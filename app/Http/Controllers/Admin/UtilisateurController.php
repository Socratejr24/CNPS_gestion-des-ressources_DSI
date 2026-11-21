<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategorieUtilisateur;
use App\Exceptions\RegleMetierException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UtilisateurFormRequest;
use App\Models\Departement;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\Interfaces\IOrganisationService;
use App\Services\Interfaces\IUtilisateurService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Interface d'administration des utilisateurs (SFD §3) — réservée à l'administrateur.
 *
 * Les agents CNPS sont créés à leur première connexion LDAP (RG-09) : l'administrateur
 * ne crée ici que des comptes prestataires (RG-08), et gère pour tous l'affectation,
 * les rôles et le statut.
 */
class UtilisateurController extends Controller
{
    public function __construct(
        private IUtilisateurService $utilisateurs,
        private IOrganisationService $organisation,
    ) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'categorie' => ['nullable', Rule::enum(CategorieUtilisateur::class)],
            'service_id' => ['nullable', 'integer'],
            'statut' => ['nullable', Rule::in(['actif', 'inactif'])],
            'affectation' => ['nullable', Rule::in(['sans'])],
        ]);

        $filtres = array_filter([
            'search' => $validated['q'] ?? null,
            'categorie' => $validated['categorie'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'sans_affectation' => ($validated['affectation'] ?? null) === 'sans',
        ]);
        if (isset($validated['statut'])) {
            $filtres['is_active'] = $validated['statut'] === 'actif';
        }

        return view('admin.utilisateurs.index', [
            'utilisateurs' => $this->utilisateurs->lister(15, $filtres)->withQueryString(),
            'compteurs' => [
                'total' => User::count(),
                'agents' => User::where('categorie', CategorieUtilisateur::AGENT)->count(),
                'prestataires' => User::where('categorie', CategorieUtilisateur::PRESTATAIRE)->count(),
                'sans_affectation' => User::whereNull('service_id')->count(),
                'inactifs' => User::where('is_active', false)->count(),
            ],
            'services' => $this->servicesGroupes(actifsSeulement: false),
            'filtres' => $validated,
        ]);
    }

    public function create(): View
    {
        return view('admin.utilisateurs.create');
    }

    public function store(UtilisateurFormRequest $request): RedirectResponse
    {
        $utilisateur = $this->utilisateurs->creerPrestataire($request->validated());

        return redirect()
            ->route('admin.utilisateurs.show', $utilisateur)
            ->with('success', 'Compte prestataire créé. Affectez-le maintenant à un service.');
    }

    public function show(User $utilisateur): View
    {
        return view('admin.utilisateurs.show', [
            'utilisateur' => $this->utilisateurs->charger($utilisateur),
            'services' => $this->servicesGroupes(actifsSeulement: true),
            'roles' => Role::orderBy('libelle')->get(),
            'responsabilites' => [
                'services' => Service::where('chef_id', $utilisateur->id)->orderBy('libelle')->pluck('libelle'),
                'departements' => Departement::where('responsable_id', $utilisateur->id)->orderBy('libelle')->pluck('libelle'),
            ],
            'estMoi' => $utilisateur->is(auth()->user()),
        ]);
    }

    public function update(UtilisateurFormRequest $request, User $utilisateur): RedirectResponse
    {
        if ($utilisateur->categorie === CategorieUtilisateur::AGENT) {
            throw new RegleMetierException(
                'Les informations d\'un agent CNPS proviennent de l\'annuaire LDAP et sont mises à jour à chaque connexion.'
            );
        }

        $this->utilisateurs->modifier($utilisateur, $request->validated());

        return back()->with('success', 'Informations mises à jour.');
    }

    public function affecter(Request $request, User $utilisateur): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ]);

        $service = isset($validated['service_id']) ? Service::findOrFail($validated['service_id']) : null;
        $this->organisation->affecterUtilisateur($utilisateur, $service);

        return back()->with('success', $service
            ? "Utilisateur affecté au service « {$service->libelle} »."
            : 'Affectation retirée.');
    }

    public function attribuerRoles(Request $request, User $utilisateur): RedirectResponse
    {
        $validated = $request->validate([
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'distinct'],
        ]);
        $roleIds = array_map('intval', $validated['role_ids'] ?? []);

        // Garde-fou : l'administrateur connecté ne peut pas se retirer ses propres droits.
        if ($utilisateur->is($request->user())) {
            $adminId = Role::where('libelle', Role::ADMINISTRATEUR)->value('id');
            if ($adminId && ! in_array($adminId, $roleIds, true)) {
                throw new RegleMetierException('Vous ne pouvez pas retirer votre propre rôle Administrateur.');
            }
        }

        $this->utilisateurs->attribuerRoles($utilisateur, $roleIds);

        return back()->with('success', 'Rôles mis à jour.');
    }

    public function changerStatut(Request $request, User $utilisateur): RedirectResponse
    {
        $actif = $request->validate(['is_active' => ['required', 'boolean']])['is_active'];
        $actif = filter_var($actif, FILTER_VALIDATE_BOOLEAN);

        if (! $actif && $utilisateur->is($request->user())) {
            throw new RegleMetierException('Vous ne pouvez pas désactiver votre propre compte.');
        }

        $this->utilisateurs->changerStatut($utilisateur, $actif);

        return back()->with('success', $actif ? 'Compte activé.' : 'Compte désactivé.');
    }

    /**
     * Services regroupés par « Structure · Département », pour les listes déroulantes.
     *
     * @return array<string, array<int, string>>
     */
    private function servicesGroupes(bool $actifsSeulement): array
    {
        return Service::with('departement.structure')
            ->when($actifsSeulement, fn ($q) => $q->where('is_active', true))
            ->orderBy('libelle')
            ->get()
            ->groupBy(fn (Service $s) => trim(
                ($s->departement?->structure?->code ?? '—').' · '.($s->departement?->libelle ?? '—')
            ))
            ->sortKeys()
            ->map(fn ($groupe) => $groupe->mapWithKeys(fn (Service $s) => [
                $s->id => $s->libelle.($s->is_active ? '' : ' (inactif)'),
            ])->all())
            ->all();
    }
}

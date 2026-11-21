<?php

namespace App\Http\Requests;

use App\Enums\TypeDestination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Formulaire de demande (création ou modification d'un brouillon).
 *
 * `action` = brouillon | soumettre. La complétude exigée pour la soumission
 * (description, destination, au moins une ressource) est contrôlée par DemandeService.
 */
class DemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // l'appartenance et le statut sont contrôlés par DemandePolicy
    }

    protected function prepareForValidation(): void
    {
        // Lignes vides du formulaire dynamique ignorées.
        $lignes = collect($this->input('lignes', []))
            ->filter(fn ($l) => is_array($l) && filled($l['ressource_id'] ?? null))
            ->values()
            ->all();

        $this->merge(['lignes' => $lignes]);
    }

    public function rules(): array
    {
        $type = $this->input('destination_type');

        return [
            'action' => ['required', Rule::in(['brouillon', 'soumettre'])],
            'objet' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'informations_complementaires' => ['nullable', 'string', 'max:5000'],
            'application_id' => ['nullable', 'integer', Rule::exists('applications', 'id')->where('is_active', true)],

            'destination_type' => ['nullable', Rule::enum(TypeDestination::class)],
            'destination_departement_id' => [Rule::requiredIf($type === TypeDestination::DEPARTEMENT->value), 'nullable', 'integer'],
            'destination_service_id' => [Rule::requiredIf($type === TypeDestination::SERVICE->value), 'nullable', 'integer'],
            'destination_utilisateur_id' => [Rule::requiredIf($type === TypeDestination::UTILISATEUR->value), 'nullable', 'integer'],

            'lignes' => ['array', 'max:20'],
            'lignes.*.ressource_id' => ['required', 'integer', 'distinct', Rule::exists('ressources', 'id')->where('is_active', true)],
            'lignes.*.quantite' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'objet' => 'objet',
            'application_id' => 'application',
            'destination_type' => 'type de destination',
            'destination_departement_id' => 'département destinataire',
            'destination_service_id' => 'service destinataire',
            'destination_utilisateur_id' => 'utilisateur destinataire',
            'lignes' => 'ressources',
            'lignes.*.ressource_id' => 'ressource',
            'lignes.*.quantite' => 'quantité',
        ];
    }

    public function messages(): array
    {
        return [
            'lignes.*.ressource_id.distinct' => 'Une même ressource ne peut apparaître qu\'une fois : ajustez plutôt la quantité.',
            'lignes.max' => 'Une demande ne peut pas comporter plus de 20 ressources.',
        ];
    }

    /** Données attendues par DemandeService. */
    public function donnees(): array
    {
        $data = $this->safe()->except('action');
        $data['lignes'] = $this->validated('lignes', []);

        return $data;
    }

    public function veutSoumettre(): bool
    {
        return $this->input('action') === 'soumettre';
    }
}

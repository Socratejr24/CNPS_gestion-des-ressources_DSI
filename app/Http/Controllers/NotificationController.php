<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Notifications internes de l'utilisateur connecté (SFD §12.3).
 */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $nonLues = $request->boolean('non_lues');
        $utilisateur = $request->user();

        return view('notifications.index', [
            'notifications' => ($nonLues ? $utilisateur->unreadNotifications() : $utilisateur->notifications())
                ->paginate(20)
                ->withQueryString(),
            'nombreNonLues' => $utilisateur->unreadNotifications()->count(),
            'nonLues' => $nonLues,
        ]);
    }

    /** Marque la notification comme lue puis ouvre la demande concernée. */
    public function lire(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $demande = Demande::find($notification->data['demande_id'] ?? null);

        return $demande && $request->user()->can('view', $demande)
            ? redirect()->route('demandes.show', $demande)
            : redirect()->route('notifications.index')->with('warning', 'Cette demande n\'est plus accessible.');
    }

    public function toutLire(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications sont marquées comme lues.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('user.profile');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'cin'       => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:300',
            'ville'     => 'nullable|string|max:100',
        ]);

        Auth::user()->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function notifications()
    {
        // Marquer toutes les notifications non lues comme lues dès l'ouverture de la page
        Notification::where('user_id', Auth::id())->where('lu', false)->update(['lu' => true]);

        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('user.notifications', compact('notifications'));
    }

    public function marquerNotificationLue(int $id): RedirectResponse
    {
        $notification = Notification::where('user_id', Auth::id())->find($id);

        if ($notification) {
            $notification->update(['lu' => true]);
        }

        return back();
    }

    public function marquerToutesLues(): RedirectResponse
    {
        Notification::where('user_id', Auth::id())->where('lu', false)->update(['lu' => true]);

        return back();
    }
}

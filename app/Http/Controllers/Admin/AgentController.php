<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AgentController extends Controller
{
    public function index()
    {
        $agents = User::agents()->withTrashed()->latest()->paginate(20);
        return view('admin.agents.index', compact('agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'password'  => ['required', Password::min(8)->letters()->numbers()],
        ]);

        User::create([
            'prenom'    => $validated['prenom'],
            'nom'       => $validated['nom'],
            'email'     => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'password'  => $validated['password'],
            'role'      => 'agent',
            'is_active' => true,
        ]);

        return back()->with('success', "Agent {$validated['prenom']} {$validated['nom']} créé avec succès.");
    }

    public function toggleActif(User $agent)
    {
        abort_unless($agent->isAgent(), 403);

        $agent->update(['is_active' => ! $agent->is_active]);

        $etat = $agent->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Compte de {$agent->nom_complet} {$etat}.");
    }

    public function resetPassword(Request $request, User $agent)
    {
        abort_unless($agent->isAgent(), 403);

        $request->validate([
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ]);

        $agent->update(['password' => $request->password]);

        return back()->with('success', "Mot de passe de {$agent->nom_complet} réinitialisé.");
    }

    public function destroy(User $agent)
    {
        abort_unless($agent->isAgent(), 403);

        $agent->delete();

        return back()->with('success', "Agent {$agent->nom_complet} supprimé.");
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// AuthApiController
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

class AuthApiController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Compte crÃ©Ã© avec succÃ¨s.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte dÃ©sactivÃ©. Contactez l\'administration.'], 403);
        }

        $user->update(['derniere_connexion' => now()]);
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'DÃ©connectÃ© avec succÃ¨s.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser(User $user): array
    {
        return [
            'id'              => $user->id,
            'nom_complet'     => $user->nom_complet,
            'nom'             => $user->nom,
            'prenom'          => $user->prenom,
            'email'           => $user->email,
            'telephone'       => $user->telephone,
            'role'            => $user->role,
            'avatar_url'      => $user->avatar_url,
            'notifications_non_lues' => $user->notificationsNonLues()->count(),
        ];
    }
}


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// DemandeApiController
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

class DemandeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $demandes = $request->user()
            ->demandes()
            ->with('paiementSucces')
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $demandes->map(fn($d) => $this->formatDemande($d)),
            'meta' => [
                'current_page' => $demandes->currentPage(),
                'last_page'    => $demandes->lastPage(),
                'total'        => $demandes->total(),
            ],
        ]);
    }

    public function show(Demande $demande): JsonResponse
    {
        abort_unless($demande->user_id === auth()->id(), 403);

        $demande->load(['documents', 'paiements', 'notifications']);

        return response()->json([
            'data' => [
                ...$this->formatDemande($demande),
                'documents'     => $demande->documents->map(fn($d) => [
                    'id'            => $d->id,
                    'type'          => $d->type_document,
                    'type_label'    => \App\Models\Document::TYPES_LABELS[$d->type_document] ?? $d->type_document,
                    'nom_original'  => $d->nom_original,
                    'taille'        => $d->taille_formatee,
                    'statut'        => $d->statut,
                    'url'           => $d->url,
                ]),
                'paiements'     => $demande->paiements->map(fn($p) => [
                    'id'         => $p->id,
                    'reference'  => $p->reference_paiement,
                    'montant'    => $p->montant_formate,
                    'statut'     => $p->statut,
                    'date'       => $p->date_paiement?->toDateTimeString(),
                ]),
            ],
        ]);
    }

    public function statut(Demande $demande): JsonResponse
    {
        abort_unless($demande->user_id === auth()->id(), 403);

        return response()->json([
            'numero_demande' => $demande->numero_demande,
            'statut'         => $demande->statut,
            'statut_label'   => $demande->statut_label,
            'statut_color'   => $demande->statut_color,
            'derniere_mise_a_jour' => $demande->updated_at->toDateTimeString(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type_passeport'       => 'required|in:ordinaire,diplomatique,service',
            'motif_renouvellement' => 'required|in:expiration,perte,vol,deterioration,changement_etat_civil,autre',
            'nom_complet'          => 'required|string|max:120',
            'date_naissance'       => 'required|date',
            'lieu_naissance'       => 'required|string|max:100',
            'nationalite'          => 'required|string|max:60',
            'cin'                  => 'required|string|max:20',
            'adresse_residence'    => 'required|string|max:300',
            'ville'                => 'required|string|max:100',
            'urgence'              => 'boolean',
        ]);

        $urgence = $request->boolean('urgence');
        $demande = Demande::create([
            ...$validated,
            'user_id'         => auth()->id(),
            'statut'          => 'en_attente_paiement',
            'urgence'         => $urgence,
            'montant_total'   => Demande::calculerMontant($validated['type_passeport'], $urgence),
            'date_soumission' => now(),
        ]);

        return response()->json([
            'message' => 'Demande crÃ©Ã©e avec succÃ¨s.',
            'data'    => $this->formatDemande($demande),
        ], 201);
    }

    private function formatDemande(Demande $d): array
    {
        return [
            'id'              => $d->id,
            'numero_demande'  => $d->numero_demande,
            'statut'          => $d->statut,
            'statut_label'    => $d->statut_label,
            'statut_color'    => $d->statut_color,
            'type_passeport'  => $d->type_passeport,
            'motif'           => $d->motif_renouvellement,
            'montant'         => (float) $d->montant_total,
            'montant_formate' => $d->montant_formate,
            'urgence'         => $d->urgence,
            'est_payee'       => $d->estPayee(),
            'date_soumission' => $d->date_soumission?->toDateString(),
            'date_validation' => $d->date_validation?->toDateString(),
            'date_rdv'        => $d->date_rdv?->toDateString(),
            'created_at'      => $d->created_at->toDateTimeString(),
        ];
    }
}


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// NotificationApiController
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

class NotificationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications_app()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $notifications->map(fn($n) => [
                'id'      => $n->id,
                'type'    => $n->type,
                'titre'   => $n->titre,
                'message' => $n->message,
                'lu'      => $n->lu,
                'date'    => $n->created_at->diffForHumans(),
            ]),
            'non_lues' => $request->user()->notificationsNonLues()->count(),
        ]);
    }

    public function count(Request $request): JsonResponse
    {
        return response()->json([
            'non_lues' => $request->user()->notificationsNonLues()->count(),
        ]);
    }

    public function marquerLu(Request $request, int $id): JsonResponse
    {
        $notif = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notif->marquerLu();
        return response()->json(['message' => 'Notification marquÃ©e comme lue.']);
    }
}


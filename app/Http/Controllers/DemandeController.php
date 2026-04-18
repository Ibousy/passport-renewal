<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Document;
use App\Services\NotificationService;
use App\Services\PayTechService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemandeController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private PayTechService      $payTechService,
    ) {}

    // ─── Liste des demandes de l'utilisateur ─────────────────────────

    public function index()
    {
        $demandes = Auth::user()
            ->demandes()
            ->with(['paiementSucces'])
            ->latest()
            ->paginate(10);

        return view('user.demandes.index', compact('demandes'));
    }

    // ─── Formulaire nouvelle demande ─────────────────────────────────

    public function create()
    {
        $user = Auth::user();
        return view('user.demandes.create', compact('user'));
    }

    // ─── Enregistrement brouillon / soumission ────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_passeport'            => 'required|in:ordinaire,diplomatique,service',
            'motif_renouvellement'      => 'required|in:expiration,perte,vol,deterioration,changement_etat_civil,autre',
            'motif_detail'              => 'nullable|string|max:500',
            'ancien_numero_passeport'   => 'nullable|string|max:30',
            'date_expiration_ancien'    => 'nullable|date',
            'nom_complet'               => 'required|string|max:120',
            'date_naissance'            => 'required|date|before:-18 years',
            'lieu_naissance'            => 'required|string|max:100',
            'nationalite'               => 'required|string|max:60',
            'cin'                       => 'required|string|max:20',
            'adresse_residence'         => 'required|string|max:300',
            'ville'                     => 'required|string|max:100',
            'profession'                => 'nullable|string|max:100',
            'urgence'                   => 'boolean',
            'action'                    => 'required|in:brouillon,soumettre',
        ]);

        DB::beginTransaction();
        try {
            $statut  = $validated['action'] === 'soumettre' ? 'soumise' : 'brouillon';
            $urgence = $request->boolean('urgence');

            $demande = Demande::create([
                ...$validated,
                'user_id'      => Auth::id(),
                'statut'       => $statut,
                'urgence'      => $urgence,
                'montant_total' => Demande::calculerMontant($validated['type_passeport'], $urgence),
                'date_soumission' => $statut === 'soumise' ? now() : null,
            ]);

            // Upload des documents si fournis
            $this->traiterDocuments($request, $demande);

            if ($statut === 'soumise') {
                $this->notificationService->notifierChangementStatut($demande, '');
                $demande->update(['statut' => 'en_attente_paiement']);
            }

            DB::commit();

            return redirect()
                ->route('demandes.show', $demande)
                ->with('success', $statut === 'soumise'
                    ? 'Demande soumise avec succès ! Procédez au paiement.'
                    : 'Brouillon sauvegardé.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    // ─── Détail d'une demande ─────────────────────────────────────────

    public function show(Demande $demande)
    {
        $this->authorize('view', $demande);

        $demande->load(['documents', 'paiements', 'notifications']);

        return view('user.demandes.show', compact('demande'));
    }

    // ─── Éditer (brouillon seulement) ────────────────────────────────

    public function edit(Demande $demande)
    {
        $this->authorize('update', $demande);
        abort_unless($demande->estModifiable(), 403, 'Cette demande ne peut plus être modifiée.');

        return view('user.demandes.edit', compact('demande'));
    }

    public function update(Request $request, Demande $demande)
    {
        $this->authorize('update', $demande);
        abort_unless($demande->estModifiable(), 403);

        $validated = $request->validate([
            'type_passeport'     => 'required|in:ordinaire,diplomatique,service',
            'nom_complet'        => 'required|string|max:120',
            'date_naissance'     => 'required|date',
            'lieu_naissance'     => 'required|string|max:100',
            'cin'                => 'required|string|max:20',
            'adresse_residence'  => 'required|string|max:300',
            'ville'              => 'required|string|max:100',
            'urgence'            => 'boolean',
            'action'             => 'required|in:brouillon,soumettre',
        ]);

        $urgence = $request->boolean('urgence');
        $statut  = $validated['action'] === 'soumettre' ? 'en_attente_paiement' : 'brouillon';

        $demande->update([
            ...$validated,
            'statut'           => $statut,
            'urgence'          => $urgence,
            'montant_total'    => Demande::calculerMontant($validated['type_passeport'], $urgence),
            'date_soumission'  => $statut !== 'brouillon' ? now() : null,
        ]);

        $this->traiterDocuments($request, $demande);

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Demande mise à jour.');
    }

    // ─── Paiement ────────────────────────────────────────────────────

    public function initierPaiement(Demande $demande)
    {
        $this->authorize('view', $demande);

        abort_unless(
            $demande->statut === 'en_attente_paiement',
            400,
            'Cette demande n\'est pas en attente de paiement.'
        );

        $result = $this->payTechService->initierPaiement($demande);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // Si simulation : afficher page de simulation
        if (!empty($result['simulation'])) {
            return redirect($result['redirect_url']);
        }

        // Sinon rediriger vers PayTech
        return redirect($result['redirect_url']);
    }

    // ─── Suppression (brouillon uniquement) ─────────────────────

    public function destroy(Demande $demande)
    {
        $this->authorize('delete', $demande);

        abort_unless(
            $demande->statut === 'brouillon',
            403,
            'Seuls les brouillons peuvent être supprimés.'
        );

        $demande->delete();

        return redirect()
            ->route('demandes.index')
            ->with('success', 'Brouillon supprimé.');
    }

    // ─── Récipissé (reçu officiel) ───────────────────────────────

    public function recipisse(Demande $demande)
    {
        $this->authorize('view', $demande);

        abort_unless(
            $demande->estPayee(),
            403,
            'Le récipissé est disponible uniquement après confirmation du paiement.'
        );

        $demande->load(['paiementSucces', 'user', 'documents']);

        return view('user.demandes.recipisse', compact('demande'));
    }

    // ─── Upload documents supplémentaires ────────────────────────────

    public function uploadDocument(Request $request, Demande $demande)
    {
        $this->authorize('view', $demande);

        $request->validate([
            'fichier'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'type_document' => 'required|in:' . implode(',', array_keys(Document::TYPES_LABELS)),
        ]);

        $this->traiterDocuments($request, $demande, singleMode: true);

        return back()->with('success', 'Document uploadé avec succès.');
    }

    // ─── Visualisation / téléchargement d'un document ────────────

    public function telechargerDocument(Document $document, Request $request)
    {
        abort_unless(
            $document->user_id === Auth::id(),
            403,
            'Vous n\'êtes pas autorisé à accéder à ce document.'
        );

        $chemin = Storage::disk('private')->path($document->chemin_fichier);

        abort_unless(file_exists($chemin), 404, 'Fichier introuvable.');

        $mime = $document->mime_type ?: mime_content_type($chemin);

        // ?dl=1 → force le téléchargement, sinon affichage inline (PDF/image dans le navigateur)
        $disposition = $request->boolean('dl') ? 'attachment' : 'inline';

        return response()->file($chemin, [
            'Content-Type'        => $mime,
            'Content-Disposition' => $disposition . '; filename="' . addslashes($document->nom_original) . '"',
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private function traiterDocuments(Request $request, Demande $demande, bool $singleMode = false): void
    {
        $champs = $singleMode ? ['fichier'] : array_keys(Document::TYPES_REQUIS);

        foreach ($champs as $champ) {
            if (!$request->hasFile($singleMode ? 'fichier' : $champ)) continue;

            $file    = $request->file($singleMode ? 'fichier' : $champ);
            $type    = $singleMode ? $request->input('type_document') : $champ;

            // Sécurité : vérifier le vrai type MIME
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])) {
                continue;
            }

            // Nom de fichier sécurisé
            $nomFichier = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $chemin     = $file->storeAs(
                "documents/{$demande->user_id}/{$demande->id}",
                $nomFichier,
                'private'
            );

            Document::create([
                'demande_id'    => $demande->id,
                'user_id'       => $demande->user_id,
                'type_document' => $type,
                'nom_original'  => $file->getClientOriginalName(),
                'nom_fichier'   => $nomFichier,
                'chemin_fichier' => $chemin,
                'mime_type'     => $mimeType,
                'taille_octets' => $file->getSize(),
                'hash_fichier'  => hash_file('sha256', $file->getRealPath()),
            ]);
        }
    }
}

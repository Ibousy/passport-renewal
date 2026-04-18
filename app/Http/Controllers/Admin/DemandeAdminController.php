<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Document;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DemandeAdminController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = Demande::with(['user', 'paiementSucces'])->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type_passeport', $request->type);
        }
        if ($request->filled('urgence')) {
            $query->where('urgence', $request->urgence === '1');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'like', "%{$search}%")
                  ->orWhere('nom_complet', 'like', "%{$search}%")
                  ->orWhere('cin', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('email', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $demandes = $query->paginate(20)->withQueryString();

        return view('admin.demandes.index', compact('demandes'));
    }

    public function show(Demande $demande)
    {
        $demande->load(['user', 'documents', 'paiements', 'notifications', 'traitePar']);
        return view('admin.demandes.show', compact('demande'));
    }

    public function changerStatut(Request $request, Demande $demande)
    {
        $request->validate([
            'statut'             => 'required|in:' . implode(',', array_keys(Demande::STATUTS_LABELS)),
            'commentaire_admin'  => 'nullable|string|max:1000',
            'motif_rejet'        => 'required_if:statut,rejetee|nullable|string|max:500',
            'date_rdv'           => 'nullable|date|after:today',
        ]);

        $ancienStatut = $demande->statut;

        $demande->update([
            'statut'            => $request->statut,
            'commentaire_admin' => $request->commentaire_admin,
            'motif_rejet'       => $request->motif_rejet,
            'date_rdv'          => $request->date_rdv,
            'traite_par'        => auth()->id(),
            'date_traitement'   => now(),
            'date_validation'   => in_array($request->statut, ['validee']) ? now() : $demande->date_validation,
        ]);

        $this->notificationService->notifierChangementStatut($demande, $ancienStatut);

        return back()->with('success', "Statut mis à jour : {$demande->statut_label}");
    }

    public function validerDocument(Document $document)
    {
        $document->update(['statut' => 'valide']);

        $document->load('demande');
        $this->notificationService->notifierDocumentValide($document);

        return back()->with('success', 'Document validé. L\'utilisateur a été notifié.');
    }

    public function rejeterDocument(Request $request, Document $document)
    {
        $request->validate(['commentaire' => 'required|string|max:300']);
        $document->update(['statut' => 'rejete', 'commentaire' => $request->commentaire]);

        $document->load('demande');
        $this->notificationService->notifierDocumentRejete($document);

        return back()->with('success', 'Document rejeté. L\'utilisateur a été notifié.');
    }

    public function telechargerDocument(Document $document)
    {
        abort_unless(Storage::disk('private')->exists($document->chemin_fichier), 404);

        return Storage::disk('private')->download(
            $document->chemin_fichier,
            $document->nom_original
        );
    }

    public function exporterPDF(Demande $demande)
    {
        $demande->load(['user', 'documents', 'paiementSucces']);
        $pdf = view('admin.demandes.pdf', compact('demande'));
        return response($pdf, 200)->header('Content-Type', 'text/html');
    }
}


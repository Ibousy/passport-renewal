@extends('layouts.app')

@section('title', 'Demande ' . $demande->numero_demande)
@section('page-title', 'Suivi de la demande')

@section('content')

<div class="row g-4">

    {{-- ── Colonne principale ─────────────────────────── --}}
    <div class="col-12 col-lg-8">

        {{-- En-tête demande --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="text-muted" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                            Référence
                        </div>
                        <h3 class="mb-1" style="font-family:'Playfair Display',serif;color:var(--primary);">
                            {{ $demande->numero_demande }}
                        </h3>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge badge-statut bg-{{ $demande->statut_color }} fs-6">
                                {{ $demande->statut_label }}
                            </span>
                            @if($demande->urgence)
                                <span class="badge bg-warning text-dark"><i class="bi bi-lightning me-1"></i>URGENT</span>
                            @endif
                            <span class="badge bg-light text-dark">{{ ucfirst($demande->type_passeport) }}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted" style="font-size:.8rem;">Montant</div>
                        <div style="font-size:1.6rem;font-weight:700;color:var(--primary);">
                            {{ $demande->montant_formate }}
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                            Soumise le {{ $demande->created_at->format('d/m/Y') }}
                        </div>
                        @if($demande->estPayee())
                        <a href="{{ route('demandes.recipisse', $demande) }}"
                           target="_blank"
                           class="btn btn-sm mt-2"
                           style="background:#f0fdf4;border:1.5px solid #86efac;color:#166534;border-radius:8px;font-weight:600;font-size:.78rem;">
                            <i class="bi bi-receipt me-1"></i>Télécharger le récipissé
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Action paiement --}}
                @if($demande->statut === 'en_attente_paiement')
                <div class="mt-4 p-3 rounded-3" style="background:#fffbeb;border:1.5px dashed #fbbf24;">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="fw-600" style="color:#92400e;"><i class="bi bi-credit-card me-2"></i>Paiement requis</div>
                            <div style="font-size:.85rem;color:#b45309;">
                                Veuillez procéder au paiement de <strong>{{ $demande->montant_formate }}</strong> pour activer votre demande.
                            </div>
                        </div>
                        <form method="POST" action="{{ route('demandes.paiement', $demande) }}">
                            @csrf
                            <button type="submit" class="btn-accent btn">
                                <i class="bi bi-credit-card me-2"></i>Payer maintenant
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                @if($demande->statut === 'documents_manquants' && $demande->commentaire_admin)
                <div class="alert alert-warning mt-3 mb-0">
                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Action requise :</strong>
                    {{ $demande->commentaire_admin }}
                </div>
                @endif

                @if($demande->statut === 'rejetee')
                <div class="alert alert-danger mt-3 mb-0">
                    <strong><i class="bi bi-x-circle me-2"></i>Demande rejetée :</strong>
                    {{ $demande->motif_rejet }}
                </div>
                @endif

                @if($demande->statut === 'passeport_pret' && $demande->date_rdv)
                <div class="alert alert-success mt-3 mb-0">
                    <strong><i class="bi bi-calendar-check me-2"></i>Rendez-vous :</strong>
                    {{ $demande->date_rdv->format('l d F Y') }}. Présentez-vous avec votre CIN et votre reçu de paiement.
                </div>
                @endif
            </div>
        </div>

        {{-- Informations personnelles --}}
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person me-2"></i>Informations personnelles</div>
            <div class="card-body p-4">
                <div class="row g-3" style="font-size:.9rem;">
                    @foreach([
                        ['Nom complet',    $demande->nom_complet],
                        ['CIN',            $demande->cin],
                        ['Date naissance', $demande->date_naissance->format('d/m/Y')],
                        ['Lieu naissance', $demande->lieu_naissance],
                        ['Nationalité',    $demande->nationalite],
                        ['Profession',     $demande->profession ?? '—'],
                        ['Adresse',        $demande->adresse_residence],
                        ['Ville',          $demande->ville],
                    ] as [$label, $val])
                    <div class="col-md-6">
                        <div class="text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">{{ $label }}</div>
                        <div class="fw-500 mt-1">{{ $val }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder2 me-2"></i>Documents joints</span>
                @if(in_array($demande->statut, ['documents_manquants', 'brouillon']))
                    <button class="btn btn-sm btn-outline-primary" style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-plus me-1"></i>Ajouter
                    </button>
                @endif
            </div>
            <div class="card-body p-4">
                @forelse($demande->documents as $doc)
                @php
                    $statutDocColor = $doc->statut === 'valide' ? 'success' : ($doc->statut === 'rejete' ? 'danger' : 'warning text-dark');
                    $statutDocLabel = ['en_attente' => 'En attente', 'valide' => 'Validé', 'rejete' => 'Rejeté'][$doc->statut];
                    $typeLabel = \App\Models\Document::TYPES_LABELS[$doc->type_document] ?? $doc->type_document;
                    $viewUrl   = route('demandes.document.download', $doc);
                    $dlUrl     = route('demandes.document.download', $doc) . '?dl=1';
                @endphp

                {{-- Ligne cliquable → ouvre le modal de détail --}}
                <div class="doc-row d-flex align-items-center justify-content-between p-3 rounded-3 mb-2"
                     style="background:#f8f9fc;border:1px solid #e8ecf0;cursor:pointer;transition:background .15s;"
                     onmouseenter="this.style.background='#eef2ff'"
                     onmouseleave="this.style.background='#f8f9fc'"
                     data-bs-toggle="modal"
                     data-bs-target="#docModal{{ $doc->id }}">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:11px;background:{{ $doc->estImage() ? '#dbeafe' : '#fee2e2' }};
                             flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                            <i class="bi bi-{{ $doc->estImage() ? 'image' : 'file-earmark-pdf' }}"
                               style="color:{{ $doc->estImage() ? '#2563eb' : '#dc2626' }}"></i>
                        </div>
                        <div>
                            <div class="fw-600" style="font-size:.88rem;">{{ $typeLabel }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $doc->nom_original }} · {{ $doc->taille_formatee }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $statutDocColor }}">{{ $statutDocLabel }}</span>
                        <i class="bi bi-chevron-right text-muted" style="font-size:.8rem;"></i>
                    </div>
                </div>

                {{-- Modal détail document --}}
                <div class="modal fade" id="docModal{{ $doc->id }}" tabindex="-1" aria-labelledby="docModalLabel{{ $doc->id }}">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">

                            {{-- En-tête modal --}}
                            <div class="modal-header" style="background:var(--primary);color:#fff;border:none;padding:18px 24px;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.15);
                                         display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                                        <i class="bi bi-{{ $doc->estImage() ? 'image' : 'file-earmark-pdf' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="modal-title mb-0 fw-700" id="docModalLabel{{ $doc->id }}">{{ $typeLabel }}</h6>
                                        <div style="font-size:.75rem;opacity:.75;">{{ $doc->nom_original }}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            {{-- Infos + statut --}}
                            <div class="px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3"
                                 style="background:#f8f9fc;border-bottom:1px solid #e8ecf0;">
                                <div class="d-flex flex-wrap gap-3" style="font-size:.83rem;">
                                    <div>
                                        <span class="text-muted">Fichier :</span>
                                        <strong class="ms-1">{{ $doc->nom_original }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Taille :</span>
                                        <strong class="ms-1">{{ $doc->taille_formatee }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Type :</span>
                                        <strong class="ms-1">{{ strtoupper(pathinfo($doc->nom_original, PATHINFO_EXTENSION)) }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Ajouté le :</span>
                                        <strong class="ms-1">{{ $doc->created_at->format('d/m/Y à H:i') }}</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-{{ $statutDocColor }} fs-6">{{ $statutDocLabel }}</span>
                                    @if($doc->statut === 'rejete' && $doc->commentaire)
                                        <span class="badge bg-danger-subtle text-danger" style="border-radius:8px;">
                                            <i class="bi bi-chat-left-text me-1"></i>{{ $doc->commentaire }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Zone d'aperçu --}}
                            <div class="modal-body p-0" style="min-height:420px;background:#1e1e2e;">
                                @if($doc->estImage())
                                    {{-- Aperçu image --}}
                                    <div class="d-flex align-items-center justify-content-center p-3" style="min-height:420px;">
                                        <img src="{{ $viewUrl }}"
                                             alt="{{ $typeLabel }}"
                                             style="max-width:100%;max-height:70vh;border-radius:8px;
                                                    box-shadow:0 4px 30px rgba(0,0,0,.4);"
                                             onerror="this.parentElement.innerHTML='<div class=\'text-center text-white p-5\'><i class=\'bi bi-exclamation-triangle fs-2 d-block mb-3\'></i>Impossible d\'afficher l\'image.</div>'">
                                    </div>
                                @elseif($doc->mime_type === 'application/pdf')
                                    {{-- Aperçu PDF intégré --}}
                                    <iframe src="{{ $viewUrl }}"
                                            style="width:100%;height:70vh;border:none;display:block;"
                                            title="{{ $typeLabel }}">
                                        <div class="text-center text-white p-5">
                                            <i class="bi bi-file-earmark-pdf fs-2 d-block mb-3"></i>
                                            Votre navigateur ne supporte pas l'aperçu PDF.
                                            <a href="{{ $viewUrl }}" target="_blank" class="btn btn-sm btn-light mt-3">
                                                Ouvrir dans un onglet
                                            </a>
                                        </div>
                                    </iframe>
                                @else
                                    {{-- Fichier non prévisualisable --}}
                                    <div class="d-flex flex-column align-items-center justify-content-center text-white p-5" style="min-height:420px;">
                                        <i class="bi bi-file-earmark fs-1 mb-3" style="opacity:.5;"></i>
                                        <div class="fw-600 mb-1">Aperçu non disponible</div>
                                        <div style="font-size:.85rem;opacity:.6;">Ce type de fichier ne peut pas être affiché directement.</div>
                                    </div>
                                @endif
                            </div>

                            {{-- Pied de modal : actions --}}
                            <div class="modal-footer" style="border:none;padding:16px 24px;background:#fff;">
                                <a href="{{ $viewUrl }}" target="_blank"
                                   class="btn btn-outline-primary" style="border-radius:10px;">
                                    <i class="bi bi-box-arrow-up-right me-2"></i>Ouvrir dans un onglet
                                </a>
                                <a href="{{ $dlUrl }}"
                                   class="btn-primary-custom btn" style="border-radius:10px;">
                                    <i class="bi bi-download me-2"></i>Télécharger
                                </a>
                                <button type="button" class="btn btn-light" style="border-radius:10px;" data-bs-dismiss="modal">
                                    Fermer
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                {{-- /modal --}}

                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-folder2-open fs-3 d-block mb-2"></i>
                        Aucun document joint pour l'instant.
                    </div>
                @endforelse
            </div>
        </div>

    </div>{{-- /col-main --}}

    {{-- ── Colonne latérale ───────────────────────────── --}}
    <div class="col-12 col-lg-4">

        {{-- Timeline statut --}}
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-signpost me-2"></i>Progression</div>
            <div class="card-body p-4">
                @php
                $etapes = [
                    'soumise'              => ['📝', 'Demande soumise'],
                    'en_attente_paiement'  => ['💳', 'En attente paiement'],
                    'payee'                => ['✅', 'Paiement confirmé'],
                    'en_cours_traitement'  => ['🔄', 'En traitement'],
                    'validee'              => ['🎉', 'Demande validée'],
                    'passeport_pret'       => ['🛂', 'Passeport prêt'],
                    'delivre'              => ['📬', 'Délivré'],
                ];
                $ordreSatuts = array_keys($etapes);
                $indexActuel = array_search($demande->statut, $ordreSatuts) ?: 0;
                @endphp

                <div class="timeline">
                    @foreach($etapes as $key => [$icon, $label])
                    @php
                        $indexEtape = array_search($key, $ordreSatuts);
                        $etat = $indexEtape < $indexActuel ? 'done'
                              : ($indexEtape === $indexActuel ? 'active' : '');
                    @endphp
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $etat }}"></div>
                        <div style="font-size:.87rem;" class="{{ $etat ? 'fw-600' : 'text-muted' }}">
                            {{ $icon }} {{ $label }}
                        </div>
                    </div>
                    @endforeach

                    @if($demande->statut === 'rejetee')
                    <div class="timeline-item">
                        <div class="timeline-dot error"></div>
                        <div class="fw-600 text-danger" style="font-size:.87rem;">❌ Rejetée</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Paiement --}}
        @if($demande->paiements->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-receipt me-2"></i>Paiement</div>
            <div class="card-body p-4">
                @foreach($demande->paiements as $paiement)
                <div class="d-flex justify-content-between mb-2" style="font-size:.87rem;">
                    <span class="text-muted">Référence</span>
                    <code style="font-size:.78rem;">{{ $paiement->reference_paiement }}</code>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.87rem;">
                    <span class="text-muted">Montant</span>
                    <span class="fw-600">{{ $paiement->montant_formate }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.87rem;">
                    <span class="text-muted">Statut</span>
                    <span class="badge bg-{{ $paiement->estReussi() ? 'success' : 'warning text-dark' }}">
                        {{ ucfirst($paiement->statut) }}
                    </span>
                </div>
                @if($paiement->date_paiement)
                <div class="d-flex justify-content-between" style="font-size:.87rem;">
                    <span class="text-muted">Date</span>
                    <span>{{ $paiement->date_paiement->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /col-side --}}

</div>

{{-- Modal upload document --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Ajouter un document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('demandes.document.upload', $demande) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label">Type de document</label>
                        <select name="type_document" class="form-select" required>
                            @foreach(\App\Models\Document::TYPES_LABELS as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fichier</label>
                        <input type="file" name="fichier" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">PDF, JPG, PNG — max 5 Mo</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-primary-custom btn">
                        <i class="bi bi-upload me-2"></i>Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

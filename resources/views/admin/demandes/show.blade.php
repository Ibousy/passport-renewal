@extends('layouts.app')
@section('title', 'Demande ' . $demande->numero_demande)
@section('page-title', 'Traitement de la demande')

@section('content')

{{-- Fil d'ariane --}}
<nav class="mb-4" style="font-size:.85rem;">
    <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none">Dashboard</a>
    <span class="mx-2 text-muted">/</span>
    <a href="{{ route('admin.demandes.index') }}" class="text-muted text-decoration-none">Demandes</a>
    <span class="mx-2 text-muted">/</span>
    <span class="fw-500">{{ $demande->numero_demande }}</span>
</nav>

<div class="row g-4">

    {{-- ── Colonne principale ─────────────────────────────── --}}
    <div class="col-12 col-lg-8">

        {{-- En-tête demande --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <code style="font-size:.82rem;background:#f1f5f9;padding:4px 10px;border-radius:8px;color:var(--primary);">
                            {{ $demande->numero_demande }}
                        </code>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="badge badge-statut bg-{{ $demande->statut_color }} fs-6">
                                {{ $demande->statut_label }}
                            </span>
                            @if($demande->urgence)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-lightning me-1"></i>URGENT
                                </span>
                            @endif
                            <span class="badge bg-light text-dark border">
                                {{ ucfirst($demande->type_passeport) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted mb-1" style="font-size:.78rem;">Soumise le</div>
                        <div class="fw-600">{{ $demande->created_at->format('d/m/Y à H:i') }}</div>
                        <div class="mt-2">
                            <a href="{{ route('admin.demandes.pdf', $demande) }}"
                               target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                                <i class="bi bi-file-pdf me-1"></i>Exporter PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informations demandeur --}}
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person me-2"></i>Demandeur</div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div style="width:52px;height:52px;border-radius:14px;background:var(--primary);
                         color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;">
                        {{ strtoupper(substr($demande->user->prenom, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-700" style="font-size:1rem;">{{ $demande->user->nom_complet }}</div>
                        <div class="text-muted" style="font-size:.85rem;">{{ $demande->user->email }}</div>
                        <div class="text-muted" style="font-size:.85rem;">{{ $demande->user->telephone }}</div>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('admin.users.show', $demande->user) }}"
                           class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                            Voir profil →
                        </a>
                    </div>
                </div>
                <div class="row g-3" style="font-size:.88rem;">
                    @foreach([
                        ['Nom complet',    $demande->nom_complet],
                        ['CIN',            $demande->cin],
                        ['Date naissance', $demande->date_naissance->format('d/m/Y')],
                        ['Lieu naissance', $demande->lieu_naissance],
                        ['Nationalité',    $demande->nationalite],
                        ['Motif',          ucfirst(str_replace('_',' ',$demande->motif_renouvellement))],
                        ['Adresse',        $demande->adresse_residence],
                        ['Ville',          $demande->ville],
                        ['Profession',     $demande->profession ?? '—'],
                        ['Montant',        $demande->montant_formate],
                    ] as [$label, $val])
                    <div class="col-md-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">{{ $label }}</div>
                        <div class="fw-500 mt-1">{{ $val }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-folder2-open me-2"></i>Documents joints</div>
            <div class="card-body p-4">
                @forelse($demande->documents as $doc)
                <div class="rounded-3 p-3 mb-3" style="background:#f8f9fc;border:1px solid #e8ecf0;">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-{{ $doc->estImage() ? 'image' : 'file-earmark-pdf' }} fs-5"
                               style="color:{{ $doc->estImage() ? '#2563eb' : '#dc2626' }}"></i>
                            <div>
                                <div class="fw-500" style="font-size:.88rem;">
                                    {{ \App\Models\Document::TYPES_LABELS[$doc->type_document] }}
                                </div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ $doc->nom_original }} · {{ $doc->taille_formatee }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-{{ $doc->statut === 'valide' ? 'success' : ($doc->statut === 'rejete' ? 'danger' : 'warning text-dark') }}">
                            {{ ['en_attente' => 'En attente', 'valide' => 'Validé', 'rejete' => 'Rejeté'][$doc->statut] }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.documents.download', $doc) }}"
                           class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                            <i class="bi bi-download me-1"></i>Télécharger
                        </a>
                        @if($doc->statut !== 'valide')
                        <form method="POST" action="{{ route('admin.documents.valider', $doc) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success" style="border-radius:8px;">
                                <i class="bi bi-check me-1"></i>Valider
                            </button>
                        </form>
                        @endif
                        @if($doc->statut !== 'rejete')
                        <button class="btn btn-sm btn-outline-danger" style="border-radius:8px;"
                                data-bs-toggle="modal" data-bs-target="#rejetModal{{ $doc->id }}">
                            <i class="bi bi-x me-1"></i>Rejeter
                        </button>
                        {{-- Modal rejet document --}}
                        <div class="modal fade" id="rejetModal{{ $doc->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius:16px;">
                                    <div class="modal-header border-0">
                                        <h6 class="modal-title">Motif de rejet — {{ \App\Models\Document::TYPES_LABELS[$doc->type_document] }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.documents.rejeter', $doc) }}">
                                        @csrf
                                        <div class="modal-body px-4">
                                            <textarea name="commentaire" class="form-control" rows="3"
                                                      placeholder="Expliquez pourquoi ce document est rejeté..." required></textarea>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger" style="border-radius:8px;">Confirmer le rejet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($doc->commentaire)
                        <div class="mt-2 text-danger" style="font-size:.8rem;">
                            <i class="bi bi-chat-left-text me-1"></i>{{ $doc->commentaire }}
                        </div>
                    @endif
                </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-folder2-open fs-3 d-block mb-2 opacity-25"></i>
                        Aucun document joint.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Historique notifications --}}
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Historique des notifications</div>
            <div class="card-body p-4">
                @forelse($demande->notifications()->latest()->get() as $notif)
                <div class="d-flex gap-3 mb-3">
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--primary);margin-top:5px;flex-shrink:0;"></div>
                    <div>
                        <div class="fw-500" style="font-size:.87rem;">{{ $notif->titre }}</div>
                        <div class="text-muted" style="font-size:.8rem;">{{ $notif->message }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $notif->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @empty
                    <p class="text-muted mb-0" style="font-size:.87rem;">Aucune notification envoyée.</p>
                @endforelse
            </div>
        </div>

    </div>{{-- /col-main --}}

    {{-- ── Colonne actions admin ────────────────────────────── --}}
    <div class="col-12 col-lg-4">

        {{-- Formulaire changement statut --}}
        <div class="card mb-4 border-primary" style="border-width:2px!important;">
            <div class="card-header" style="background:var(--primary);color:#fff;">
                <i class="bi bi-gear me-2"></i>Actions administrateur
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.demandes.statut', $demande) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nouveau statut</label>
                        <select name="statut" class="form-select" id="statutSelect" required>
                            @foreach(\App\Models\Demande::STATUTS_LABELS as $val => $info)
                            <option value="{{ $val }}" {{ $demande->statut === $val ? 'selected' : '' }}>
                                {{ $info['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Commentaire interne</label>
                        <textarea name="commentaire_admin" class="form-control" rows="3"
                                  placeholder="Commentaire visible par le demandeur...">{{ $demande->commentaire_admin }}</textarea>
                    </div>

                    <div class="mb-3" id="motifRejetBlock" style="display:none;">
                        <label class="form-label text-danger">Motif de rejet <span class="text-danger">*</span></label>
                        <textarea name="motif_rejet" class="form-control border-danger" rows="2"
                                  placeholder="Expliquer clairement le motif...">{{ $demande->motif_rejet }}</textarea>
                    </div>

                    <div class="mb-3" id="dateRdvBlock" style="display:none;">
                        <label class="form-label">Date de rendez-vous</label>
                        <input type="date" name="date_rdv" class="form-control"
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               value="{{ $demande->date_rdv?->format('Y-m-d') }}">
                    </div>

                    <button type="submit" class="btn-primary-custom btn w-100">
                        <i class="bi bi-check-circle me-2"></i>Enregistrer la décision
                    </button>
                </form>
            </div>
        </div>

        {{-- Paiement --}}
        @if($demande->paiements->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-credit-card me-2"></i>Paiement</div>
            <div class="card-body p-4">
                @foreach($demande->paiements as $p)
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Référence</span>
                    <code style="font-size:.78rem;">{{ $p->reference_paiement }}</code>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Montant</span>
                    <strong>{{ $p->montant_formate }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Statut</span>
                    <span class="badge bg-{{ $p->estReussi() ? 'success' : 'warning text-dark' }}">
                        {{ ucfirst($p->statut) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.85rem;">
                    <span class="text-muted">Date</span>
                    <span>{{ $p->date_paiement?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Traitement admin --}}
        @if($demande->traitePar)
        <div class="card">
            <div class="card-body p-4" style="font-size:.87rem;">
                <div class="text-muted mb-2">Traité par</div>
                <div class="fw-600">{{ $demande->traitePar->nom_complet }}</div>
                <div class="text-muted">{{ $demande->date_traitement?->format('d/m/Y') }}</div>
            </div>
        </div>
        @endif

    </div>{{-- /col-side --}}

</div>
@endsection

@push('scripts')
<script>
const select = document.getElementById('statutSelect');
function toggleBlocks() {
    document.getElementById('motifRejetBlock').style.display = select.value === 'rejetee' ? '' : 'none';
    document.getElementById('dateRdvBlock').style.display    = select.value === 'passeport_pret' ? '' : 'none';
}
select.addEventListener('change', toggleBlocks);
toggleBlocks();
</script>
@endpush

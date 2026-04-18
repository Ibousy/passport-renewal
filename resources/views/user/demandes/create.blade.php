@extends('layouts.app')

@section('title', 'Nouvelle demande')
@section('page-title', 'Nouvelle demande de renouvellement')

@section('content')

<div class="row justify-content-center">
<div class="col-12 col-xl-9">

{{-- Breadcrumb --}}
<nav class="mb-4" style="font-size:.85rem;">
    <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none">Accueil</a>
    <span class="mx-2 text-muted">/</span>
    <a href="{{ route('demandes.index') }}" class="text-muted text-decoration-none">Mes demandes</a>
    <span class="mx-2 text-muted">/</span>
    <span class="text-dark fw-500">Nouvelle demande</span>
</nav>

{{-- Erreurs --}}
@if($errors->any())
<div class="alert alert-danger" style="border-radius:12px;">
    <strong><i class="bi bi-exclamation-circle me-2"></i>Veuillez corriger les erreurs suivantes :</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('demandes.store') }}" enctype="multipart/form-data" id="demandeForm">
@csrf

{{-- ── Étape 1 : Informations du passeport ─────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-passport me-2" style="color:var(--accent)"></i>
        Étape 1 — Informations du passeport
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Type de passeport <span class="text-danger">*</span></label>
                <select name="type_passeport" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    <option value="ordinaire"    {{ old('type_passeport') == 'ordinaire'    ? 'selected' : '' }}>Ordinaire (25 000 XOF)</option>
                    <option value="diplomatique" {{ old('type_passeport') == 'diplomatique' ? 'selected' : '' }}>Diplomatique (50 000 XOF)</option>
                    <option value="service"      {{ old('type_passeport') == 'service'      ? 'selected' : '' }}>Service (30 000 XOF)</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Motif de renouvellement <span class="text-danger">*</span></label>
                <select name="motif_renouvellement" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    <option value="expiration">Expiration du passeport</option>
                    <option value="perte">Perte</option>
                    <option value="vol">Vol</option>
                    <option value="deterioration">Détérioration</option>
                    <option value="changement_etat_civil">Changement d'état civil</option>
                    <option value="autre">Autre</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Numéro de l'ancien passeport</label>
                <input type="text" name="ancien_numero_passeport" class="form-control"
                       placeholder="Ex: A12345678" value="{{ old('ancien_numero_passeport') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Date d'expiration de l'ancien passeport</label>
                <input type="date" name="date_expiration_ancien" class="form-control"
                       value="{{ old('date_expiration_ancien') }}">
            </div>

            <div class="col-12">
                <label class="form-label">Précisions (facultatif)</label>
                <textarea name="motif_detail" class="form-control" rows="2"
                          placeholder="Détails supplémentaires sur le motif...">{{ old('motif_detail') }}</textarea>
            </div>

            <div class="col-12">
                <div class="p-3 rounded-3 d-flex align-items-start gap-3"
                     style="background:#fffbeb;border:1.5px solid #fbbf24;">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="urgence" value="1"
                               id="urgence" {{ old('urgence') ? 'checked' : '' }} style="width:2.5em;">
                    </div>
                    <div>
                        <label for="urgence" class="fw-600 cursor-pointer" style="color:#92400e;">
                            <i class="bi bi-lightning-charge me-1"></i>Traitement urgent
                        </label>
                        <p class="mb-0 mt-1" style="font-size:.82rem;color:#b45309;">
                            Traitement en 48h ouvrées. Supplément de 20 000 XOF appliqué sur le tarif normal.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Étape 2 : Informations personnelles ──────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-person-vcard me-2" style="color:var(--accent)"></i>
        Étape 2 — Informations personnelles
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom et prénom complets <span class="text-danger">*</span></label>
                <input type="text" name="nom_complet" class="form-control" required
                       placeholder="Prénom(s) NOM" value="{{ old('nom_complet', $user->nom_complet) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Numéro CIN <span class="text-danger">*</span></label>
                <input type="text" name="cin" class="form-control" required
                       placeholder="Ex: 1234567890123" value="{{ old('cin', $user->cin) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Date de naissance <span class="text-danger">*</span></label>
                <input type="date" name="date_naissance" class="form-control" required
                       value="{{ old('date_naissance', $user->date_naissance?->format('Y-m-d')) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Lieu de naissance <span class="text-danger">*</span></label>
                <input type="text" name="lieu_naissance" class="form-control" required
                       placeholder="Ville ou commune" value="{{ old('lieu_naissance') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Nationalité <span class="text-danger">*</span></label>
                <input type="text" name="nationalite" class="form-control" required
                       value="{{ old('nationalite', 'Sénégalaise') }}">
            </div>

            <div class="col-md-8">
                <label class="form-label">Adresse de résidence <span class="text-danger">*</span></label>
                <input type="text" name="adresse_residence" class="form-control" required
                       placeholder="Rue, quartier..." value="{{ old('adresse_residence', $user->adresse) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Ville <span class="text-danger">*</span></label>
                <input type="text" name="ville" class="form-control" required
                       value="{{ old('ville', $user->ville) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Profession</label>
                <input type="text" name="profession" class="form-control"
                       placeholder="Votre profession" value="{{ old('profession') }}">
            </div>
        </div>
    </div>
</div>

{{-- ── Étape 3 : Documents ──────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-folder2-open me-2" style="color:var(--accent)"></i>
        Étape 3 — Pièces justificatives
    </div>
    <div class="card-body p-4">
        <div class="alert alert-info d-flex gap-2 mb-4" style="border-radius:10px;font-size:.87rem;">
            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
            <div>Formats acceptés : <strong>PDF, JPG, PNG</strong>. Taille max par fichier : <strong>5 Mo</strong>.
            Les documents doivent être lisibles et en cours de validité.</div>
        </div>

        <div class="row g-3">
            @foreach(['ancien_passeport' => 'Ancien passeport (scan)', 'carte_identite' => 'Carte nationale d\'identité', 'photo_identite' => 'Photo d\'identité récente (fond blanc)', 'acte_naissance' => 'Acte de naissance'] as $champ => $label)
            <div class="col-md-6">
                <label class="form-label">{{ $label }}</label>
                <div class="upload-zone" id="zone_{{ $champ }}">
                    <input type="file" name="{{ $champ }}" id="{{ $champ }}"
                           accept=".pdf,.jpg,.jpeg,.png" class="upload-input">
                    <label for="{{ $champ }}" class="upload-label">
                        <i class="bi bi-cloud-upload fs-4 mb-2 d-block"></i>
                        <span class="upload-text">Cliquer ou glisser-déposer</span>
                        <small class="text-muted d-block mt-1">PDF, JPG, PNG — max 5 Mo</small>
                    </label>
                    <div class="upload-preview d-none"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Récapitulatif & Actions ──────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body p-4">
        <div id="recapitulatif" class="p-3 rounded-3 mb-4" style="background:#f0f9ff;border:1.5px solid #bae6fd;">
            <div class="fw-600 mb-2"><i class="bi bi-receipt me-2"></i>Récapitulatif</div>
            <div class="d-flex justify-content-between" style="font-size:.88rem;">
                <span>Type de passeport :</span>
                <span id="recap_type" class="fw-500">—</span>
            </div>
            <div class="d-flex justify-content-between mt-1" style="font-size:.88rem;">
                <span>Traitement urgence :</span>
                <span id="recap_urgence" class="fw-500">Non</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-700">
                <span>Montant à payer :</span>
                <span id="recap_montant" style="color:var(--primary);font-size:1.1rem;">0 XOF</span>
            </div>
        </div>

        <div class="d-flex gap-3 flex-wrap">
            <button type="submit" name="action" value="brouillon"
                    class="btn btn-outline-secondary flex-grow-1" style="border-radius:10px;padding:12px;">
                <i class="bi bi-floppy me-2"></i>Sauvegarder en brouillon
            </button>
            <button type="submit" name="action" value="soumettre"
                    class="btn-primary-custom btn flex-grow-1">
                <i class="bi bi-send me-2"></i>Soumettre la demande
            </button>
        </div>
        <p class="text-muted mt-3 mb-0" style="font-size:.8rem;">
            <i class="bi bi-shield-check me-1"></i>
            En soumettant, vous serez redirigé vers la page de paiement sécurisé.
        </p>
    </div>
</div>

</form>
</div>
</div>

@endsection

@push('styles')
<style>
.upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    background: #fafbfc;
    position: relative;
    min-height: 110px;
    transition: border-color .2s, background .2s;
    overflow: hidden;
}
.upload-zone:hover { border-color: var(--primary); background: #f0f7ff; }
.upload-zone.has-file { border-color: var(--success); background: #f0fdf4; border-style: solid; }
.upload-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; z-index: 2; }
.upload-label { display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 20px; cursor: pointer; color: #94a3b8; font-size: .85rem; min-height: 110px; }
.upload-preview { padding: 12px 16px; font-size: .82rem; color: var(--success); }
</style>
@endpush

@push('scripts')
<script>
const TARIFS = {
    ordinaire:    { normal: 25000, urgent: 45000 },
    diplomatique: { normal: 50000, urgent: 80000 },
    service:      { normal: 30000, urgent: 55000 },
};

function formaterMontant(n) {
    return n.toLocaleString('fr-FR') + ' XOF';
}

function mettreAJourRecap() {
    const type    = document.querySelector('[name="type_passeport"]').value;
    const urgence = document.querySelector('[name="urgence"]').checked;
    const tarif   = TARIFS[type];

    document.getElementById('recap_type').textContent =
        { ordinaire: 'Ordinaire', diplomatique: 'Diplomatique', service: 'Service' }[type] || '—';
    document.getElementById('recap_urgence').textContent = urgence ? '✅ Oui (+20 000 XOF)' : 'Non';

    if (tarif) {
        const montant = urgence ? tarif.urgent : tarif.normal;
        document.getElementById('recap_montant').textContent = formaterMontant(montant);
    } else {
        document.getElementById('recap_montant').textContent = '0 XOF';
    }
}

document.querySelector('[name="type_passeport"]').addEventListener('change', mettreAJourRecap);
document.querySelector('[name="urgence"]').addEventListener('change', mettreAJourRecap);

// ── Upload preview ─────────────────────────────────────────
document.querySelectorAll('.upload-input').forEach(input => {
    input.addEventListener('change', function() {
        const zone    = this.closest('.upload-zone');
        const preview = zone.querySelector('.upload-preview');
        const label   = zone.querySelector('.upload-label');

        if (this.files.length > 0) {
            const file = this.files[0];
            zone.classList.add('has-file');
            label.classList.add('d-none');
            preview.classList.remove('d-none');
            preview.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>
                <strong>${file.name}</strong><br>
                <small class="text-muted">${(file.size/1024).toFixed(0)} KB</small>`;
        }
    });
});

// Drag & Drop
document.querySelectorAll('.upload-zone').forEach(zone => {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = 'var(--primary)'; });
    zone.addEventListener('dragleave', () => { zone.style.borderColor = ''; });
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.style.borderColor = '';
        const input = zone.querySelector('input[type=file]');
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
    });
});
</script>
@endpush

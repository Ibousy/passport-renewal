@extends('layouts.app')

@section('title', 'Paiement sécurisé')
@section('page-title', 'Paiement sécurisé')

@section('content')

<div class="row justify-content-center">
<div class="col-12 col-md-8 col-lg-6">

    {{-- Header badge simulation --}}
    @if(config('paytech.simulation'))
    <div class="alert d-flex align-items-center gap-2 mb-4"
         style="background:#f0f9ff;border:1.5px solid #7dd3fc;border-radius:12px;font-size:.87rem;">
        <i class="bi bi-info-circle-fill" style="color:#0284c7;"></i>
        <span><strong>Mode démonstration</strong> — Aucun vrai débit ne sera effectué.</span>
    </div>
    @endif

    {{-- Carte paiement --}}
    <div class="card" style="border-radius:20px;overflow:hidden;">
        {{-- En-tête --}}
        <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));padding:28px 32px;color:#fff;">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:48px;height:48px;background:var(--accent);border-radius:12px;
                     display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.3rem;color:var(--primary-dark);">
                    P
                </div>
                <div>
                    <div style="font-size:.75rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;">Paiement sécurisé</div>
                    <div style="font-family:'Playfair Display',serif;font-size:1.2rem;">PasseportSN</div>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <i class="bi bi-lock-fill" style="opacity:.7;"></i>
                    <span style="font-size:.75rem;opacity:.7;">SSL</span>
                </div>
            </div>
            <div class="mt-2" style="font-size:.85rem;opacity:.8;">Référence : <strong>{{ $paiement->reference_paiement }}</strong></div>
            <div style="font-size:.85rem;opacity:.8;">Demande : <strong>{{ $demande->numero_demande }}</strong></div>
        </div>

        <div class="card-body p-4">
            {{-- Montant --}}
            <div class="text-center mb-4">
                <div class="text-muted" style="font-size:.85rem;">Montant à payer</div>
                <div style="font-size:2.8rem;font-weight:800;color:var(--primary);line-height:1.1;">
                    {{ number_format($demande->montant_total, 0, ',', ' ') }}
                </div>
                <div style="font-size:1rem;color:#64748b;font-weight:500;">XOF</div>
            </div>

            {{-- Détail --}}
            <div class="rounded-3 p-3 mb-4" style="background:#f8f9fc;font-size:.87rem;">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Passeport {{ ucfirst($demande->type_passeport) }}</span>
                    <span>{{ number_format($demande->urgence ? \App\Models\Demande::TARIFS[$demande->type_passeport]['normal'] : \App\Models\Demande::TARIFS[$demande->type_passeport]['normal'], 0, ',', ' ') }} XOF</span>
                </div>
                @if($demande->urgence)
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Supplément urgence</span>
                    <span>+ 20 000 XOF</span>
                </div>
                @endif
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-700">
                    <span>Total</span>
                    <span style="color:var(--primary);">{{ $demande->montant_formate }}</span>
                </div>
            </div>

            @if(config('paytech.simulation'))
            {{-- Formulaire simulation --}}
            <div class="mb-4">
                <label class="form-label">Numéro de carte (simulation)</label>
                <input type="text" class="form-control" placeholder="4242 4242 4242 4242"
                       value="4242 4242 4242 4242" readonly style="font-family:monospace;letter-spacing:.1em;">
            </div>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="form-label">Expiration</label>
                    <input type="text" class="form-control" value="12/26" readonly>
                </div>
                <div class="col-6">
                    <label class="form-label">CVV</label>
                    <input type="text" class="form-control" value="123" readonly>
                </div>
            </div>

            <form method="POST" action="{{ route('paiement.simulation.confirmer') }}" id="payForm">
                @csrf
                <input type="hidden" name="paiement_id" value="{{ $paiement->id }}">
                <button type="submit" class="btn-accent btn w-100 py-3" id="payBtn" style="font-size:1rem;">
                    <i class="bi bi-lock-fill me-2"></i>Confirmer le paiement de {{ $demande->montant_formate }}
                </button>
            </form>

            @else
            {{-- Méthodes PayTech réelles --}}
            <div class="mb-3">
                <div class="fw-600 mb-3" style="font-size:.9rem;">Choisir une méthode</div>
                <div class="row g-2">
                    @foreach([
                        ['bi-credit-card', 'Carte bancaire', '#e0f2fe'],
                        ['bi-phone',       'Orange Money',   '#fff7ed'],
                        ['bi-phone-fill',  'Free Money',     '#fdf4ff'],
                    ] as [$icon, $label, $bg])
                    <div class="col-4">
                        <div class="p-3 rounded-3 text-center cursor-pointer method-btn"
                             style="background:{{ $bg }};border:2px solid transparent;cursor:pointer;transition:.2s;">
                            <i class="bi {{ $icon }} fs-4 d-block mb-1"></i>
                            <div style="font-size:.78rem;font-weight:500;">{{ $label }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <button class="btn-accent btn w-100 py-3" style="font-size:1rem;" disabled>
                <i class="bi bi-lock-fill me-2"></i>Payer {{ $demande->montant_formate }}
            </button>
            @endif

            {{-- Logos sécurité --}}
            <div class="text-center mt-4 d-flex align-items-center justify-content-center gap-3"
                 style="font-size:.75rem;color:#94a3b8;">
                <i class="bi bi-shield-check fs-5" style="color:#22c55e;"></i>
                <span>Paiement 100% sécurisé</span>
                <span>•</span>
                <i class="bi bi-lock fs-5" style="color:#0284c7;"></i>
                <span>Cryptage SSL/TLS</span>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('demandes.show', $demande) }}" class="text-muted text-decoration-none" style="font-size:.85rem;">
            <i class="bi bi-arrow-left me-1"></i>Revenir à ma demande
        </a>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
const btn = document.getElementById('payBtn');
if (btn) {
    document.getElementById('payForm').addEventListener('submit', function() {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Traitement en cours...';
        btn.disabled = true;
    });
}
</script>
@endpush

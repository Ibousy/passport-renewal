@extends('layouts.app')
@section('title', 'Paiement réussi')
@section('page-title', 'Confirmation de paiement')

@section('content')
<div class="row justify-content-center">
<div class="col-12 col-md-7 col-lg-5">
    <div class="card text-center" style="border-radius:20px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#059669,#10b981);padding:40px 32px;color:#fff;">
            <div style="width:80px;height:80px;background:rgba(255,255,255,.2);border-radius:50%;
                 display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 16px;">
                ✅
            </div>
            <h2 style="font-family:'Playfair Display',serif;">Paiement confirmé !</h2>
            <p class="mb-0" style="opacity:.85;">Votre demande est maintenant en cours de traitement.</p>
        </div>
        <div class="card-body p-4">
            <div class="rounded-3 p-3 mb-4 text-start" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                <div class="d-flex justify-content-between mb-2" style="font-size:.87rem;">
                    <span class="text-muted">Référence paiement</span>
                    <code style="font-size:.8rem;">{{ $paiement->reference_paiement }}</code>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.87rem;">
                    <span class="text-muted">Demande</span>
                    <strong>{{ $paiement->demande->numero_demande }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.87rem;">
                    <span class="text-muted">Montant payé</span>
                    <strong style="color:#059669;">{{ $paiement->montant_formate }}</strong>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.87rem;">
                    <span class="text-muted">Date</span>
                    <span>{{ $paiement->date_paiement?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
            <div class="alert" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;font-size:.85rem;text-align:left;">
                <i class="bi bi-info-circle me-2" style="color:#2563eb;"></i>
                Vous recevrez un email de confirmation et des notifications à chaque étape du traitement.
                Le délai moyen de traitement est de <strong>10 à 15 jours ouvrés</strong>.
            </div>
            <div class="d-grid gap-2">
                <a href="{{ route('demandes.show', $paiement->demande_id) }}" class="btn-primary-custom btn">
                    <i class="bi bi-eye me-2"></i>Suivre ma demande
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius:10px;">
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

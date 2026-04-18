@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Administrateur')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── Stats Cards ─────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #1a3a6b, #2563eb);">
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-number">{{ number_format($stats['total_demandes']) }}</div>
            <div class="stat-label">Total demandes</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-number">{{ $stats['demandes_en_attente'] }}</div>
            <div class="stat-label">En attente de traitement</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #059669, #10b981);">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-number">{{ $stats['demandes_validees'] }}</div>
            <div class="stat-label">Demandes validées</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6);">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-number" style="font-size:1.4rem;">
                {{ number_format($stats['revenus_ce_mois'], 0, ',', ' ') }}
            </div>
            <div class="stat-label">XOF ce mois</div>
        </div>
    </div>
</div>

{{-- ── Alertes urgences ─────────────────────────────────── --}}
@if($stats['demandes_urgentes'] > 0)
<div class="alert d-flex align-items-center gap-3 mb-4"
     style="background:#fffbeb;border:1.5px solid #fbbf24;color:#92400e;border-radius:12px;">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong>{{ $stats['demandes_urgentes'] }} demande(s) urgente(s)</strong> nécessitent une attention immédiate.
        <a href="{{ route('admin.demandes.index', ['urgence' => '1']) }}" class="fw-bold ms-2">Voir →</a>
    </div>
</div>
@endif

<div class="row g-4">
    {{-- ── Graphique évolution ──────────────────────────── --}}
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart me-2"></i>Évolution des demandes — {{ date('Y') }}</span>
                <span class="badge bg-light text-dark" style="font-size:.75rem;">Par mois</span>
            </div>
            <div class="card-body p-4">
                <canvas id="evolutionChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Répartition statuts ──────────────────────────── --}}
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-2"></i>Répartition des statuts</div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <canvas id="statutsChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── Dernières demandes ────────────────────────────────── --}}
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Dernières demandes</span>
        <a href="{{ route('admin.demandes.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
            Voir tout <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.87rem;">
                <thead style="background:#f8f9fc;">
                    <tr>
                        <th class="px-4 py-3">Référence</th>
                        <th>Demandeur</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dernieresDemandes as $demande)
                    <tr>
                        <td class="px-4">
                            <code style="font-size:.82rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;">
                                {{ $demande->numero_demande }}
                            </code>
                            @if($demande->urgence)
                                <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">URGENT</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-500">{{ $demande->nom_complet }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $demande->user->email }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ ucfirst($demande->type_passeport) }}</span></td>
                        <td class="fw-600">{{ $demande->montant_formate }}</td>
                        <td class="text-muted">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-statut bg-{{ $demande->statut_color }}">
                                {{ $demande->statut_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.demandes.show', $demande) }}"
                               class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Aucune demande</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Graphique évolution ─────────────────────────────────────
const mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
const dataEvolution = @json($evolutionDemandes);
const evolutionValues = mois.map((_, i) => dataEvolution[i + 1] || 0);

new Chart(document.getElementById('evolutionChart'), {
    type: 'bar',
    data: {
        labels: mois,
        datasets: [{
            label: 'Demandes',
            data: evolutionValues,
            backgroundColor: 'rgba(26,58,107,.85)',
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
            x: { grid: { display: false } }
        }
    }
});

// ── Graphique statuts ───────────────────────────────────────
const dataStatuts = @json($repartitionStatuts);
const statutLabels = {
    brouillon: 'Brouillon', soumise: 'Soumise',
    en_attente_paiement: 'Att. paiement', payee: 'Payée',
    en_cours_traitement: 'En cours', validee: 'Validée',
    rejetee: 'Rejetée', passeport_pret: 'Prêt', delivre: 'Délivré'
};
const colors = ['#94a3b8','#3b82f6','#f59e0b','#6366f1','#0ea5e9','#22c55e','#ef4444','#10b981','#1a3a6b'];

new Chart(document.getElementById('statutsChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(dataStatuts).map(k => statutLabels[k] || k),
        datasets: [{
            data: Object.values(dataStatuts),
            backgroundColor: colors,
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
        },
        cutout: '65%',
    }
});
</script>
@endpush

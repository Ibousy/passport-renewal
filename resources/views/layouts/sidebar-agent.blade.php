{{-- resources/views/layouts/sidebar-agent.blade.php --}}

<div class="nav-section-title">Espace Agent</div>
<a href="{{ route('agent.dashboard') }}" class="nav-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Tableau de bord
</a>
<a href="{{ route('admin.demandes.index') }}" class="nav-link {{ request()->routeIs('admin.demandes.*') ? 'active' : '' }}">
    <i class="bi bi-collection"></i> Demandes
    @php $enAttente = \App\Models\Demande::whereIn('statut', ['payee','soumise'])->count(); @endphp
    @if($enAttente > 0)
        <span class="badge-notif">{{ $enAttente }}</span>
    @endif
</a>

<div class="nav-section-title mt-2">Filtres rapides</div>
<a href="{{ route('admin.demandes.index', ['statut' => 'payee']) }}" class="nav-link">
    <i class="bi bi-clock-history" style="color:#0dcaf0"></i> À traiter
</a>
<a href="{{ route('admin.demandes.index', ['urgence' => '1']) }}" class="nav-link">
    <i class="bi bi-exclamation-triangle" style="color:#ffc107"></i> Urgentes
</a>
<a href="{{ route('admin.demandes.index', ['statut' => 'en_cours_traitement']) }}" class="nav-link">
    <i class="bi bi-arrow-repeat" style="color:#6f42c1"></i> En cours
</a>

<div class="nav-section-title mt-2">Mon compte</div>
<a href="{{ route('profile.edit') }}" class="nav-link">
    <i class="bi bi-person-circle"></i> Mon profil
</a>

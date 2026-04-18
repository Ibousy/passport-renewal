{{-- resources/views/layouts/sidebar-admin.blade.php --}}

<div class="nav-section-title">Administration</div>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="{{ route('admin.demandes.index') }}" class="nav-link {{ request()->routeIs('admin.demandes.*') ? 'active' : '' }}">
    <i class="bi bi-collection"></i> Demandes
    @php $enAttente = \App\Models\Demande::whereIn('statut', ['payee','soumise'])->count(); @endphp
    @if($enAttente > 0)
        <span class="badge-notif">{{ $enAttente }}</span>
    @endif
</a>
<a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Utilisateurs
</a>
<a href="{{ route('admin.agents.index') }}" class="nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
    <i class="bi bi-person-badge"></i> Agents
</a>

<div class="nav-section-title mt-2">Filtres rapides</div>
<a href="{{ route('admin.demandes.index', ['statut' => 'payee']) }}" class="nav-link">
    <i class="bi bi-clock-history" style="color:#0dcaf0"></i> À traiter
</a>
<a href="{{ route('admin.demandes.index', ['urgence' => '1']) }}" class="nav-link">
    <i class="bi bi-exclamation-triangle" style="color:#ffc107"></i> Urgentes
</a>
<a href="{{ route('admin.demandes.index', ['statut' => 'validee']) }}" class="nav-link">
    <i class="bi bi-check-circle" style="color:#198754"></i> Validées
</a>

<div class="nav-section-title mt-2">Mon compte</div>
<a href="{{ route('profile.edit') }}" class="nav-link">
    <i class="bi bi-person-circle"></i> Profil admin
</a>

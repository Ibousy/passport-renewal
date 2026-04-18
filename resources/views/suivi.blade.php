<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de demande — PasseportSN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a3a6b; --primary-dark: #0f2347; --accent: #c8a84b; }
        body { font-family: 'Inter', sans-serif; background: #f8f9fc; color: #2d3748; min-height: 100vh; }

        .navbar-brand { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--primary) !important; }

        .hero-suivi {
            background: linear-gradient(150deg, #0f2347 0%, #1a3a6b 60%, #22456b 100%);
            padding: 64px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero-suivi::after {
            content: '';
            position: absolute; right: -80px; bottom: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(200,168,75,.08);
        }

        .search-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
            padding: 40px;
            margin-top: -40px;
            position: relative;
            z-index: 2;
        }

        .form-control-lg {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1.1rem;
            letter-spacing: .08em;
            font-weight: 600;
            color: var(--primary);
        }
        .form-control-lg:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,58,107,.1); }

        .btn-search {
            background: var(--primary);
            color: #fff; border: none;
            border-radius: 12px; padding: 14px 32px;
            font-weight: 700; font-size: 1rem;
            transition: all .25s;
        }
        .btn-search:hover { background: var(--primary-dark); color: #fff; transform: translateY(-1px); }

        /* ── Résultat ── */
        .result-card {
            border-radius: 16px;
            border: 1px solid #e8ecf0;
            overflow: hidden;
        }
        .result-header {
            background: var(--primary);
            color: #fff;
            padding: 24px 28px;
        }

        .step-track {
            display: flex;
            align-items: center;
            gap: 0;
            margin: 24px 0;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .step-track-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            min-width: 80px;
            position: relative;
        }
        .step-track-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
        }
        .step-track-item.done::after  { background: #198754; }
        .step-track-item.active::after { background: linear-gradient(to right, #198754, #e2e8f0); }

        .step-dot {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 700;
            background: #e2e8f0; color: #94a3b8;
            position: relative; z-index: 1;
            border: 2px solid #e2e8f0;
        }
        .step-dot.done   { background: #198754; color: #fff; border-color: #198754; }
        .step-dot.active { background: var(--primary); color: #fff; border-color: var(--primary);
                           box-shadow: 0 0 0 4px rgba(26,58,107,.15); }
        .step-dot.error  { background: #dc3545; color: #fff; border-color: #dc3545; }

        .step-label { font-size: .7rem; text-align: center; margin-top: 6px; color: #94a3b8; max-width: 70px; }
        .step-label.done   { color: #198754; font-weight: 600; }
        .step-label.active { color: var(--primary); font-weight: 700; }

        .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
        .info-item .label { font-size: .72rem; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 4px; }
        .info-item .value { font-weight: 600; font-size: .92rem; color: #1e293b; }
    </style>
</head>
<body>

{{-- ── NAVBAR ───────────────────────────────────────────────── --}}
<nav class="navbar bg-white border-bottom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <div style="width:36px;height:36px;background:var(--primary);border-radius:9px;
                 display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;">P</div>
            PasseportSN
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:9px;">Connexion</a>
            <a href="{{ route('register') }}" class="btn btn-sm" style="background:var(--primary);color:#fff;border-radius:9px;padding:6px 18px;font-weight:600;">Commencer</a>
        </div>
    </div>
</nav>

{{-- ── HERO ─────────────────────────────────────────────────── --}}
<section class="hero-suivi">
    <div class="container text-center text-white position-relative" style="z-index:1;">
        <div style="font-size:3rem;margin-bottom:12px;">🔍</div>
        <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:12px;">
            Suivi de votre demande
        </h1>
        <p style="opacity:.8;font-size:1rem;max-width:480px;margin:0 auto;">
            Entrez votre code de demande pour suivre l'état d'avancement de votre renouvellement de passeport.
        </p>
    </div>
</section>

<div class="container" style="max-width:700px;padding-bottom:80px;">

    {{-- ── Carte de recherche ──────────────────────────────── --}}
    <div class="search-card mb-5">
        @if(session('suivi_error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('suivi_error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('suivi.chercher') }}">
            @csrf
            <label class="form-label fw-600 mb-2" style="font-size:.95rem;color:var(--primary);">
                <i class="bi bi-upc-scan me-2"></i>Code de demande
            </label>
            <div class="d-flex gap-3">
                <input type="text"
                       name="code"
                       class="form-control form-control-lg @error('code') is-invalid @enderror"
                       placeholder="REF-2026-00001"
                       value="{{ old('code', isset($demande) ? $demande->numero_demande : '') }}"
                       autocomplete="off"
                       style="text-transform:uppercase;">
                <button type="submit" class="btn-search btn flex-shrink-0">
                    <i class="bi bi-search me-2"></i>Vérifier
                </button>
            </div>
            @error('code')
                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
            @enderror
            <div class="mt-2 text-muted" style="font-size:.82rem;">
                <i class="bi bi-info-circle me-1"></i>
                Le code figure sur votre email de confirmation, au format <strong>REF-AAAA-XXXXX</strong>.
            </div>
        </form>
    </div>

    {{-- ── Résultat ─────────────────────────────────────────── --}}
    @isset($demande)
    @php
        $etapes = [
            'soumise'             => 'Soumise',
            'en_attente_paiement' => 'Paiement',
            'payee'               => 'Payée',
            'en_cours_traitement' => 'En cours',
            'validee'             => 'Validée',
            'passeport_pret'      => 'Prêt',
            'delivre'             => 'Délivré',
        ];
        $icones = [
            'soumise'             => '📝',
            'en_attente_paiement' => '💳',
            'payee'               => '✅',
            'en_cours_traitement' => '🔄',
            'validee'             => '🎉',
            'passeport_pret'      => '🛂',
            'delivre'             => '📬',
        ];
        $ordreEtapes  = array_keys($etapes);
        $indexActuel  = array_search($demande->statut, $ordreEtapes);
        $estRejete    = $demande->statut === 'rejetee';
    @endphp

    <div class="result-card">
        {{-- En-tête --}}
        <div class="result-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div style="font-size:.75rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;">Référence de la demande</div>
                    <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;letter-spacing:.04em;">
                        {{ $demande->numero_demande }}
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge fs-6 bg-{{ $demande->statut_color }}"
                          style="border-radius:20px;padding:8px 16px;">
                        {{ $demande->statut_label }}
                    </span>
                    @if($demande->urgence)
                    <div class="mt-1">
                        <span class="badge bg-warning text-dark"><i class="bi bi-lightning me-1"></i>URGENT</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-4">

            {{-- Barre de progression --}}
            @if(!$estRejete)
            <div class="step-track">
                @foreach($etapes as $key => $label)
                @php
                    $idx  = array_search($key, $ordreEtapes);
                    $etat = $idx < $indexActuel ? 'done' : ($idx === $indexActuel ? 'active' : '');
                @endphp
                <div class="step-track-item {{ $etat }}">
                    <div class="step-dot {{ $etat }}">
                        @if($etat === 'done')
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $icones[$key] ?? '' }}
                        @endif
                    </div>
                    <div class="step-label {{ $etat }}">{{ $label }}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
                <i class="bi bi-x-circle-fill fs-5"></i>
                <div>
                    <div class="fw-600">Demande rejetée</div>
                    @if($demande->motif_rejet)
                    <div style="font-size:.85rem;">{{ $demande->motif_rejet }}</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Alerte passeport prêt --}}
            @if($demande->statut === 'passeport_pret')
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
                <i class="bi bi-calendar-check-fill fs-5"></i>
                <div>
                    <div class="fw-600">Passeport prêt à retirer !</div>
                    @if($demande->date_rdv)
                    <div style="font-size:.85rem;">
                        Rendez-vous prévu le <strong>{{ $demande->date_rdv->format('d/m/Y') }}</strong>.
                        Présentez-vous avec votre pièce d'identité et votre reçu de paiement.
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Alerte documents manquants --}}
            @if($demande->statut === 'documents_manquants' && $demande->commentaire_admin)
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>
                    <div class="fw-600">Action requise</div>
                    <div style="font-size:.85rem;">{{ $demande->commentaire_admin }}</div>
                </div>
            </div>
            @endif

            {{-- Informations de la demande (info publiques uniquement) --}}
            <div class="info-grid mb-4">
                <div class="info-item">
                    <div class="label">Type de passeport</div>
                    <div class="value">{{ ucfirst($demande->type_passeport) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Motif</div>
                    <div class="value">{{ ucfirst(str_replace('_', ' ', $demande->motif_renouvellement)) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Date de soumission</div>
                    <div class="value">{{ $demande->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Montant</div>
                    <div class="value">{{ $demande->montant_formate }}</div>
                </div>
                @if($demande->date_traitement)
                <div class="info-item">
                    <div class="label">Date de traitement</div>
                    <div class="value">{{ $demande->date_traitement->format('d/m/Y') }}</div>
                </div>
                @endif
                @if($demande->date_rdv)
                <div class="info-item">
                    <div class="label">Rendez-vous</div>
                    <div class="value" style="color:#198754;">{{ $demande->date_rdv->format('d/m/Y') }}</div>
                </div>
                @endif
            </div>

            {{-- Nom masqué --}}
            <div class="p-3 rounded-3 d-flex align-items-center gap-3 mb-3"
                 style="background:#f8f9fc;border:1px solid #e8ecf0;font-size:.88rem;">
                <i class="bi bi-person-circle fs-4 text-muted"></i>
                <div>
                    <div class="text-muted" style="font-size:.75rem;">Demandeur</div>
                    <div class="fw-600">
                        {{-- Masquer le nom sauf les 3 premiers caractères --}}
                        {{ substr($demande->nom_complet, 0, 3) }}{{ str_repeat('*', max(0, strlen($demande->nom_complet) - 3)) }}
                    </div>
                </div>
                <div class="ms-auto text-muted" style="font-size:.78rem;">
                    <i class="bi bi-shield-lock me-1"></i>Données protégées
                </div>
            </div>

            {{-- CTA connexion --}}
            <div class="text-center pt-2">
                <p class="text-muted mb-3" style="font-size:.85rem;">
                    Vous êtes le demandeur ? Connectez-vous pour accéder à tous les détails.
                </p>
                <a href="{{ route('login') }}" class="btn" style="background:var(--primary);color:#fff;border-radius:10px;padding:10px 28px;font-weight:600;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </a>
            </div>
        </div>
    </div>
    @endisset

    {{-- Aide --}}
    @if(!isset($demande))
    <div class="text-center mt-4 text-muted" style="font-size:.85rem;">
        <i class="bi bi-question-circle me-1"></i>
        Vous ne trouvez pas votre code ?
        <a href="{{ route('login') }}" class="text-decoration-none" style="color:var(--primary);">
            Connectez-vous à votre espace
        </a>
        pour accéder à toutes vos demandes.
    </div>
    @endif

</div>

<footer style="background:var(--primary-dark);color:rgba(255,255,255,.6);text-align:center;padding:20px 0;font-size:.82rem;margin-top:auto;">
    © {{ date('Y') }} PasseportSN — Tous droits réservés
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

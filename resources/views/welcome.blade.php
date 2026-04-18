<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PasseportSN — Renouvellement de passeport en ligne</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a3a6b;
            --primary-dark: #0f2347;
            --accent: #c8a84b;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #2d3748; }

        /* NAV */
        .navbar-brand { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--primary) !important; }
        .nav-pill { background: var(--primary); color: #fff; border-radius: 10px; padding: 9px 22px; font-weight: 600; font-size: .9rem; }
        .nav-pill:hover { background: var(--primary-dark); color: #fff; }

        /* HERO */
        .hero {
            min-height: 92vh;
            background: linear-gradient(155deg, #f0f5ff 0%, #e8f0fe 40%, #fff 100%);
            display: flex; align-items: center;
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; right: -120px; top: -80px;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(26,58,107,.07) 0%, transparent 70%);
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(26,58,107,.08); border: 1px solid rgba(26,58,107,.15);
            border-radius: 20px; padding: 6px 14px;
            font-size: .8rem; font-weight: 600; color: var(--primary);
            margin-bottom: 24px;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 5vw, 3.5rem);
            line-height: 1.15;
            color: var(--primary-dark);
            margin-bottom: 20px;
        }
        .hero h1 em { color: var(--accent); font-style: italic; }
        .hero p { font-size: 1.05rem; color: #4a5568; line-height: 1.7; max-width: 520px; }

        .btn-hero-primary {
            background: var(--primary);
            color: #fff; border: none;
            border-radius: 12px; padding: 14px 32px;
            font-weight: 700; font-size: 1rem;
            transition: all .25s;
            box-shadow: 0 4px 20px rgba(26,58,107,.3);
        }
        .btn-hero-primary:hover {
            background: var(--primary-dark); color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(26,58,107,.4);
        }
        .btn-hero-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 12px; padding: 14px 32px;
            font-weight: 600; font-size: 1rem;
            transition: all .25s;
        }
        .btn-hero-outline:hover { background: var(--primary); color: #fff; }

        /* CARD VISUEL DROITE */
        .hero-visual {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 80px rgba(26,58,107,.12);
            padding: 36px;
            border: 1px solid #e8ecf4;
        }

        /* ÉTAPES */
        .step-card {
            border-radius: 16px;
            padding: 28px;
            background: #fff;
            border: 1px solid #e8ecf0;
            height: 100%;
            transition: transform .25s, box-shadow .25s;
        }
        .step-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,.08); }
        .step-number {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: var(--primary);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; font-weight: 800;
            margin-bottom: 16px;
        }

        /* FEATURES */
        .feature-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
        }

        /* FOOTER */
        footer { background: var(--primary-dark); color: rgba(255,255,255,.7); }
        footer a { color: rgba(255,255,255,.6); text-decoration: none; }
        footer a:hover { color: var(--accent); }
    </style>
</head>
<body>

{{-- ── NAVBAR ─────────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top" style="padding:12px 0;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/">
            <div style="width:38px;height:38px;background:var(--primary);border-radius:10px;
                 display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.1rem;">P</div>
            PasseportSN
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav mx-auto gap-lg-3" style="font-size:.9rem;">
                <li class="nav-item"><a class="nav-link" href="#comment-ca-marche">Comment ça marche</a></li>
                <li class="nav-item"><a class="nav-link" href="#documents">Documents requis</a></li>
                <li class="nav-item"><a class="nav-link" href="#tarifs">Tarifs</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
            </ul>
            <div class="d-flex gap-2">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                       class="nav-pill">Mon espace</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary" style="border-radius:10px;padding:8px 20px;">Connexion</a>
                    <a href="{{ route('register') }}" class="nav-pill">Commencer</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- ── HERO ────────────────────────────────────────────────── --}}
<section class="hero">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-12 col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-shield-check-fill" style="color:var(--accent);"></i>
                    Service officiel sécurisé
                </div>
                <h1>
                    Renouvellement de passeport
                    <em>100% en ligne</em>
                </h1>
                <p class="mb-4">
                    Faites votre demande de renouvellement de passeport depuis chez vous.
                    Service rapide, sécurisé et disponible 24h/24.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        <i class="bi bi-arrow-right-circle me-2"></i>Démarrer ma demande
                    </a>
                    <a href="#comment-ca-marche" class="btn-hero-outline">
                        <i class="bi bi-play-circle me-2"></i>Comment ça marche
                    </a>
                </div>
                <div class="mt-4 d-flex flex-wrap gap-4" style="font-size:.85rem;color:#64748b;">
                    <div><i class="bi bi-check2-circle me-1" style="color:#16a34a;"></i>Délai 10–15 jours</div>
                    <div><i class="bi bi-check2-circle me-1" style="color:#16a34a;"></i>Paiement sécurisé</div>
                    <div><i class="bi bi-check2-circle me-1" style="color:#16a34a;"></i>Suivi en temps réel</div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="hero-visual">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div style="width:48px;height:48px;background:var(--primary);border-radius:12px;
                             display:flex;align-items:center;justify-content:center;color:var(--accent);font-weight:800;font-size:1.4rem;">P</div>
                        <div>
                            <div class="fw-700" style="color:var(--primary);">PasseportSN</div>
                            <div style="font-size:.78rem;color:#94a3b8;">Suivi de demande</div>
                        </div>
                        <span class="badge ms-auto" style="background:#dcfce7;color:#166534;border-radius:20px;padding:6px 12px;">
                            <i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i>En ligne
                        </span>
                    </div>
                    @foreach([
                        ['REF-2024-00142', 'Passeport prêt 🛂',       'success', '100%'],
                        ['REF-2024-00189', 'En cours de traitement 🔄', 'primary', '65%'],
                        ['REF-2024-00201', 'Paiement confirmé ✅',      'info',    '40%'],
                    ] as [$ref, $label, $color, $progress])
                    <div class="rounded-3 p-3 mb-2" style="background:#f8f9fc;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <code style="font-size:.78rem;color:var(--primary);">{{ $ref }}</code>
                            <span class="badge bg-{{ $color }}" style="font-size:.72rem;">{{ $label }}</span>
                        </div>
                        <div class="progress" style="height:4px;border-radius:10px;">
                            <div class="progress-bar bg-{{ $color }}" style="width:{{ $progress }}"></div>
                        </div>
                    </div>
                    @endforeach
                    <div class="text-center mt-3" style="font-size:.8rem;color:#94a3b8;">
                        <i class="bi bi-lock-fill me-1"></i>Connexion SSL sécurisée
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── SUIVI RAPIDE ─────────────────────────────────────────── --}}
<section style="padding:40px 0;background:#fff;border-top:1px solid #e8ecf0;">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-12 col-md-5">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-search" style="color:var(--accent);font-size:1.2rem;"></i>
                    <span class="fw-700" style="color:var(--primary);font-size:1.05rem;">Suivre ma demande</span>
                </div>
                <p class="text-muted mb-0" style="font-size:.88rem;line-height:1.6;">
                    Entrez votre code de référence pour vérifier instantanément l'état de votre dossier,
                    sans avoir besoin de vous connecter.
                </p>
            </div>
            <div class="col-12 col-md-7">
                <form method="POST" action="{{ route('suivi.chercher') }}" class="d-flex gap-2">
                    @csrf
                    <input type="text"
                           name="code"
                           class="form-control"
                           placeholder="Ex: REF-2026-00001"
                           style="border-radius:10px;border:1.5px solid #e2e8f0;font-weight:600;
                                  letter-spacing:.06em;text-transform:uppercase;font-size:.95rem;"
                           autocomplete="off">
                    <button type="submit" class="btn flex-shrink-0"
                            style="background:var(--primary);color:#fff;border-radius:10px;padding:10px 22px;font-weight:600;white-space:nowrap;">
                        <i class="bi bi-search me-1"></i>Vérifier
                    </button>
                </form>
                <div class="mt-2 text-muted" style="font-size:.78rem;">
                    <i class="bi bi-info-circle me-1"></i>
                    Le code figure sur votre email de confirmation au format <strong>REF-AAAA-XXXXX</strong>.
                    <a href="{{ route('suivi') }}" class="ms-2 text-decoration-none" style="color:var(--primary);">Page dédiée →</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── COMMENT ÇA MARCHE ───────────────────────────────────── --}}
<section id="comment-ca-marche" class="py-6" style="padding:80px 0;background:#f8f9fc;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-family:'Playfair Display',serif;color:var(--primary-dark);font-size:2rem;">
                Comment ça marche ?
            </h2>
            <p class="text-muted">Quatre étapes simples pour renouveler votre passeport</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['1', 'bi-person-plus', 'Créer un compte', 'Inscrivez-vous en quelques secondes avec votre email et créez votre espace personnel sécurisé.'],
                ['2', 'bi-file-earmark-text', 'Remplir le formulaire', 'Complétez votre demande en ligne avec vos informations personnelles et uploadez vos documents.'],
                ['3', 'bi-credit-card', 'Payer en ligne', 'Réglez les frais de renouvellement de façon sécurisée via PayTech, Orange Money ou carte bancaire.'],
                ['4', 'bi-passport', 'Retirer votre passeport', 'Suivez votre demande en temps réel et récupérez votre passeport sur rendez-vous.'],
            ] as [$num, $icon, $titre, $desc])
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="step-card">
                    <div class="step-number">{{ $num }}</div>
                    <i class="bi {{ $icon }} fs-4 mb-3 d-block" style="color:var(--accent);"></i>
                    <h5 class="fw-700">{{ $titre }}</h5>
                    <p class="text-muted mb-0" style="font-size:.88rem;line-height:1.6;">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── TARIFS ──────────────────────────────────────────────── --}}
<section id="tarifs" style="padding:80px 0;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-family:'Playfair Display',serif;color:var(--primary-dark);font-size:2rem;">Tarifs</h2>
            <p class="text-muted">Choisissez le type de passeport adapté à votre situation</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['Ordinaire',    '25 000', '45 000', false],
                ['Diplomatique', '50 000', '80 000', true],
                ['Service',      '30 000', '55 000', false],
            ] as [$type, $normal, $urgent, $featured])
            <div class="col-12 col-md-4">
                <div class="card h-100 {{ $featured ? 'border-primary' : '' }}"
                     style="{{ $featured ? 'border-width:2px!important;' : '' }}border-radius:20px;">
                    @if($featured)
                    <div class="text-center py-2 rounded-top"
                         style="background:var(--primary);color:#fff;font-size:.8rem;font-weight:700;letter-spacing:.06em;">
                        LE PLUS DEMANDÉ
                    </div>
                    @endif
                    <div class="card-body p-4 text-center">
                        <h4 class="fw-700 mb-1" style="color:var(--primary);">{{ $type }}</h4>
                        <div style="font-size:2.2rem;font-weight:800;color:var(--primary-dark);">
                            {{ $normal }} <span style="font-size:1rem;font-weight:500;color:#64748b;">XOF</span>
                        </div>
                        <div class="text-muted" style="font-size:.85rem;">Traitement normal (15 jours)</div>
                        <hr>
                        <div style="font-size:1.1rem;font-weight:700;color:var(--accent);">
                            {{ $urgent }} XOF
                        </div>
                        <div class="text-muted" style="font-size:.85rem;">
                            <i class="bi bi-lightning me-1"></i>Traitement urgent (48h)
                        </div>
                        <a href="{{ route('register') }}" class="btn w-100 mt-4 {{ $featured ? 'btn-hero-primary' : 'btn-hero-outline' }}"
                           style="font-size:.9rem;">
                            Faire une demande
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── FOOTER ──────────────────────────────────────────────── --}}
<footer style="padding:48px 0 24px;">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:36px;height:36px;background:var(--accent);border-radius:8px;
                         display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:var(--primary-dark);">P</div>
                    <span style="font-family:'Playfair Display',serif;color:#fff;font-size:1.1rem;">PasseportSN</span>
                </div>
                <p style="font-size:.85rem;line-height:1.6;">
                    Service officiel de renouvellement de passeport en ligne. Sécurisé, rapide et accessible 24h/24.
                </p>
            </div>
            <div class="col-6 col-md-2 offset-md-2">
                <div class="fw-600 mb-3" style="color:#fff;font-size:.9rem;">Service</div>
                <ul class="list-unstyled" style="font-size:.85rem;">
                    <li class="mb-2"><a href="{{ route('register') }}">Nouvelle demande</a></li>
                    <li class="mb-2"><a href="{{ route('login') }}">Suivi demande</a></li>
                    <li class="mb-2"><a href="#tarifs">Tarifs</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <div class="fw-600 mb-3" style="color:#fff;font-size:.9rem;">Aide</div>
                <ul class="list-unstyled" style="font-size:.85rem;">
                    <li class="mb-2"><a href="#faq">FAQ</a></li>
                    <li class="mb-2"><a href="#">Contact</a></li>
                    <li class="mb-2"><a href="#">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        <div class="border-top pt-3 text-center" style="border-color:rgba(255,255,255,.1)!important;font-size:.8rem;">
            © {{ date('Y') }} PasseportSN — Tous droits réservés
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

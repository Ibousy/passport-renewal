<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récipissé — {{ $demande->numero_demande }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root { --primary: #1a3a6b; --primary-dark: #0f2347; --accent: #c8a84b; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: #2d3748;
        }

        /* ── Barre d'actions (non imprimée) ── */
        .action-bar {
            background: var(--primary-dark);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .action-bar .brand {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.1rem;
            text-decoration: none;
        }
        .btn-print {
            background: var(--accent);
            color: var(--primary-dark);
            border: none;
            border-radius: 10px;
            padding: 9px 22px;
            font-weight: 700;
            font-size: .9rem;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-print:hover { background: #d4b55a; }

        /* ── Document récipissé ── */
        .recipisse-wrapper {
            max-width: 780px;
            margin: 32px auto;
            padding: 0 16px 48px;
        }

        .recipisse-doc {
            background: #fff;
            border-radius: 0;
            box-shadow: 0 4px 40px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* En-tête officiel */
        .doc-header {
            background: var(--primary);
            color: #fff;
            padding: 32px 40px;
            position: relative;
        }
        .doc-header::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--accent) 0%, #e8c96a 50%, var(--accent) 100%);
        }
        .doc-header .logo {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 20px;
        }
        .doc-header .logo-icon {
            width: 56px; height: 56px;
            background: var(--accent);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.6rem;
            color: var(--primary-dark);
        }
        .doc-header .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            line-height: 1.2;
        }
        .doc-header .logo-text small {
            font-family: 'Inter', sans-serif;
            font-size: .72rem;
            opacity: .7;
            display: block;
            font-weight: 400;
            letter-spacing: .06em;
        }

        .doc-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            margin: 0;
            letter-spacing: .04em;
        }
        .doc-subtitle {
            text-align: center;
            font-size: .8rem;
            opacity: .75;
            margin-top: 4px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        /* Corps du document */
        .doc-body { padding: 36px 40px; }

        .ref-badge {
            background: #f0f5ff;
            border: 2px solid var(--primary);
            border-radius: 10px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        .ref-badge .ref-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; color: #64748b; }
        .ref-badge .ref-value { font-size: 1.4rem; font-weight: 800; color: var(--primary); letter-spacing: .06em; }

        .section-title {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #94a3b8;
            font-weight: 700;
            border-bottom: 1px solid #e8ecf0;
            padding-bottom: 8px;
            margin-bottom: 16px;
            margin-top: 28px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #f1f5f9;
            font-size: .88rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .key { color: #64748b; }
        .info-row .val { font-weight: 600; color: #1e293b; text-align: right; max-width: 60%; }

        /* Statut */
        .statut-banner {
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 24px 0;
        }
        .statut-banner.payee, .statut-banner.valide {
            background: #f0fdf4; border: 1.5px solid #86efac;
        }
        .statut-banner.en_cours {
            background: #eff6ff; border: 1.5px solid #93c5fd;
        }
        .statut-banner .statut-icon { font-size: 1.8rem; }
        .statut-banner .statut-text { font-size: .9rem; font-weight: 600; }

        /* Paiement */
        .paiement-box {
            background: #f8fffe;
            border: 1.5px solid #6ee7b7;
            border-radius: 12px;
            padding: 20px;
        }
        .paiement-montant {
            font-size: 2rem;
            font-weight: 800;
            color: #059669;
        }

        /* QR-like code + watermark */
        .doc-footer {
            border-top: 1px solid #e8ecf0;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafbfc;
            font-size: .75rem;
            color: #94a3b8;
            gap: 16px;
            flex-wrap: wrap;
        }
        .stamp {
            width: 90px; height: 90px;
            border: 3px solid rgba(26,58,107,.15);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: rgba(26,58,107,.3);
            font-size: .55rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            line-height: 1.4;
            flex-shrink: 0;
        }
        .stamp.confirmed {
            border-color: rgba(5,150,105,.3);
            color: rgba(5,150,105,.5);
        }

        /* ── IMPRESSION ── */
        @media print {
            body { background: #fff; }
            .action-bar, .no-print { display: none !important; }
            .recipisse-wrapper { margin: 0; padding: 0; max-width: 100%; }
            .recipisse-doc { box-shadow: none; }
            @page { margin: 1cm 1.5cm; size: A4; }
        }
    </style>
</head>
<body>

{{-- ── Barre d'actions (non imprimée) ── --}}
<div class="action-bar no-print">
    <a href="{{ route('demandes.show', $demande) }}" class="brand d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left me-1"></i> Retour à la demande
    </a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-print">
            <i class="bi bi-printer me-2"></i>Imprimer / Enregistrer en PDF
        </button>
    </div>
</div>

{{-- ── Document récipissé ── --}}
<div class="recipisse-wrapper">
<div class="recipisse-doc">

    {{-- En-tête --}}
    <div class="doc-header">
        <div class="logo">
            <div class="logo-icon">P</div>
            <div class="logo-text">
                PasseportSN
                <small>République du Sénégal — Service officiel de renouvellement</small>
            </div>
        </div>
        <div class="doc-title">RÉCIPISSÉ DE DEMANDE</div>
        <div class="doc-subtitle">Renouvellement de passeport — Accusé de réception</div>
    </div>

    {{-- Corps --}}
    <div class="doc-body">

        {{-- Référence --}}
        <div class="ref-badge">
            <div>
                <div class="ref-label">Numéro de demande</div>
                <div class="ref-value">{{ $demande->numero_demande }}</div>
            </div>
            <div class="text-end">
                <div class="ref-label">Date d'émission</div>
                <div style="font-size:1rem;font-weight:700;color:#475569;">{{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        {{-- Statut actuel --}}
        @php
            $statuts_positifs = ['payee', 'en_cours_traitement', 'validee', 'passeport_pret', 'delivre'];
            $classe = in_array($demande->statut, ['validee','passeport_pret','delivre']) ? 'valide'
                    : (in_array($demande->statut, ['payee','en_cours_traitement']) ? 'en_cours' : 'payee');
        @endphp
        <div class="statut-banner {{ $classe }}">
            <div class="statut-icon">
                @if(in_array($demande->statut, ['validee','passeport_pret','delivre'])) 🎉
                @elseif($demande->statut === 'en_cours_traitement') 🔄
                @else ✅
                @endif
            </div>
            <div>
                <div class="statut-text">Statut : {{ $demande->statut_label }}</div>
                @if($demande->statut === 'passeport_pret' && $demande->date_rdv)
                <div style="font-size:.82rem;margin-top:4px;">
                    Rendez-vous de retrait : <strong>{{ $demande->date_rdv->format('l d F Y') }}</strong>
                </div>
                @endif
                @if($demande->statut === 'en_cours_traitement')
                <div style="font-size:.82rem;margin-top:4px;">Délai estimé : 10 à 15 jours ouvrés.</div>
                @endif
            </div>
        </div>

        {{-- Informations personnelles --}}
        <div class="section-title"><i class="bi bi-person me-2"></i>Informations du demandeur</div>
        @foreach([
            ['Nom complet',          $demande->nom_complet],
            ['Date de naissance',    $demande->date_naissance->format('d/m/Y')],
            ['Lieu de naissance',    $demande->lieu_naissance],
            ['Nationalité',          $demande->nationalite],
            ['Numéro CIN',           $demande->cin],
            ['Adresse',              $demande->adresse_residence . ', ' . $demande->ville],
            ['Profession',           $demande->profession ?? '—'],
        ] as [$key, $val])
        <div class="info-row">
            <span class="key">{{ $key }}</span>
            <span class="val">{{ $val }}</span>
        </div>
        @endforeach

        {{-- Informations de la demande --}}
        <div class="section-title"><i class="bi bi-passport me-2"></i>Détails de la demande</div>
        @foreach([
            ['Type de passeport',       ucfirst($demande->type_passeport)],
            ['Motif de renouvellement', ucfirst(str_replace('_', ' ', $demande->motif_renouvellement))],
            ['Ancien passeport',        $demande->ancien_numero_passeport ?? '—'],
            ['Traitement',              $demande->urgence ? '⚡ Urgent (48h)' : 'Normal (15 jours)'],
            ['Date de soumission',      $demande->created_at->format('d/m/Y à H:i')],
        ] as [$key, $val])
        <div class="info-row">
            <span class="key">{{ $key }}</span>
            <span class="val">{{ $val }}</span>
        </div>
        @endforeach

        {{-- Paiement --}}
        <div class="section-title"><i class="bi bi-receipt me-2"></i>Informations de paiement</div>
        @if($demande->paiementSucces)
        @php $p = $demande->paiementSucces; @endphp
        <div class="paiement-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.07em;color:#059669;">Montant payé</div>
                    <div class="paiement-montant">{{ $p->montant_formate }}</div>
                </div>
                <span class="badge" style="background:#dcfce7;color:#166534;border-radius:20px;padding:8px 16px;font-size:.82rem;font-weight:700;">
                    <i class="bi bi-check-circle me-1"></i>CONFIRMÉ
                </span>
            </div>
            <div class="info-row">
                <span class="key">Référence paiement</span>
                <span class="val" style="font-family:monospace;font-size:.82rem;">{{ $p->reference_paiement }}</span>
            </div>
            <div class="info-row">
                <span class="key">Date du paiement</span>
                <span class="val">{{ $p->date_paiement?->format('d/m/Y à H:i') ?? '—' }}</span>
            </div>
            @if($p->transaction_id)
            <div class="info-row">
                <span class="key">Transaction ID</span>
                <span class="val" style="font-family:monospace;font-size:.78rem;">{{ $p->transaction_id }}</span>
            </div>
            @endif
        </div>
        @else
        <div class="info-row">
            <span class="key">Statut paiement</span>
            <span class="val">{{ $demande->montant_formate }} — confirmé</span>
        </div>
        @endif

        {{-- Note légale --}}
        <div class="mt-4 p-3 rounded-3" style="background:#fffbeb;border:1px solid #fde68a;font-size:.8rem;color:#92400e;line-height:1.6;">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Important :</strong> Ce récipissé atteste uniquement de la réception de votre demande.
            Il ne constitue pas un titre de voyage. Conservez ce document jusqu'au retrait de votre passeport.
            Pour toute question, connectez-vous à votre espace personnel sur <strong>PasseportSN</strong>.
        </div>

    </div>{{-- /doc-body --}}

    {{-- Pied de page --}}
    <div class="doc-footer">
        <div>
            <div style="font-weight:600;color:#475569;margin-bottom:4px;">PasseportSN — Service officiel</div>
            <div>Ce document est généré automatiquement le {{ now()->format('d/m/Y à H:i') }}.</div>
            <div>Référence : <strong>{{ $demande->numero_demande }}</strong></div>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <div class="stamp confirmed">
                <div style="font-size:1.2rem;margin-bottom:4px;">✅</div>
                Paiement<br>confirmé
            </div>
            <div class="stamp">
                <div style="font-size:1.4rem;margin-bottom:4px;">🏛️</div>
                PasseportSN<br>Officiel
            </div>
        </div>
    </div>

</div>{{-- /recipisse-doc --}}
</div>{{-- /wrapper --}}

</body>
</html>

# 🛂 PasseportSN — Plateforme de renouvellement de passeport en ligne

Plateforme web complète développée avec **Laravel 11**, **MySQL**, **Bootstrap 5** et **PayTech**.

---

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Installation rapide](#installation-rapide)
3. [Structure du projet](#structure-du-projet)
4. [Configuration](#configuration)
5. [API REST](#api-rest)
6. [Déploiement production](#déploiement-production)
7. [Comptes de test](#comptes-de-test)

---

## ✅ Prérequis

| Outil       | Version minimale |
|-------------|-----------------|
| PHP         | 8.2+            |
| Laravel     | 11.x            |
| MySQL       | 8.0+            |
| Composer    | 2.x             |
| Node.js     | 18+             |

---

## 🚀 Installation rapide

### 1. Cloner & installer

```bash
git clone https://github.com/votre-repo/passport-renewal.git
cd passport-renewal
composer install
npm install && npm run build
```

### 2. Configuration environnement

```bash
cp .env.example .env
php artisan key:generate
```

Editer `.env` :

```env
APP_NAME="PasseportSN"
APP_URL=http://localhost:8000
APP_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=passeport_sn
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe

# Mail (pour les notifications email)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_FROM_ADDRESS=noreply@passeportsn.sn
MAIL_FROM_NAME="PasseportSN"

# PayTech (laisser SIMULATION=true pour les tests)
PAYTECH_SIMULATION=true
PAYTECH_API_KEY=votre_cle_api
PAYTECH_API_SECRET=votre_secret_api
PAYTECH_BASE_URL=https://paytech.sn/api

# Activer les notifications email
APP_NOTIFICATIONS_EMAIL=false
```

### 3. Base de données

```bash
# Créer la base de données MySQL
mysql -u root -p -e "CREATE DATABASE passeport_sn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Lancer les migrations
php artisan migrate

# Peupler avec des données de test
php artisan db:seed
```

### 4. Stockage des fichiers

```bash
php artisan storage:link
```

### 5. Enregistrer le middleware Admin

Dans `app/Http/Kernel.php` (Laravel 10) ou `bootstrap/app.php` (Laravel 11) :

```php
// Laravel 11 — bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

### 6. Enregistrer la Policy

Dans `app/Providers/AuthServiceProvider.php` :

```php
protected $policies = [
    \App\Models\Demande::class => \App\Policies\DemandePolicy::class,
];
```

### 7. Lancer le serveur de développement

```bash
php artisan serve
# → http://localhost:8000
```

---

## 📁 Structure complète du projet

```
passport-renewal/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── ApiControllers.php      # Auth, Demandes, Notifications API
│   │   │   ├── Admin/
│   │   │   │   └── AdminControllers.php    # Dashboard, Demandes, Users admin
│   │   │   ├── DemandeController.php       # CRUD demandes utilisateur
│   │   │   ├── PaiementController.php      # Paiement PayTech
│   │   │   └── ProfileController.php       # Profil utilisateur
│   │   ├── Middleware/
│   │   │   └── AdminMiddleware.php         # Protection routes admin
│   │   └── Requests/
│   │       └── DemandeRequest.php          # Validation formulaire
│   │
│   ├── Models/
│   │   ├── User.php                        # Utilisateur + rôles
│   │   ├── Demande.php                     # Demande de passeport
│   │   ├── Document.php                    # Documents uploadés
│   │   ├── Paiement.php                    # Transactions PayTech
│   │   └── Notification.php               # Notifications in-app
│   │
│   ├── Policies/
│   │   └── DemandePolicy.php               # Autorisations demandes
│   │
│   └── Services/
│       ├── PayTechService.php              # Intégration API PayTech
│       └── NotificationService.php        # Envoi notifications
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_users_table.php
│   │   ├── ..._create_demandes_table.php
│   │   └── ..._create_supporting_tables.php
│   └── seeders/
│       └── DatabaseSeeder.php              # Données de test
│
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                   # Layout principal
│   │   ├── sidebar-user.blade.php
│   │   └── sidebar-admin.blade.php
│   ├── admin/
│   │   ├── dashboard.blade.php             # Dashboard avec graphiques
│   │   ├── demandes/
│   │   │   ├── index.blade.php             # Liste + filtres
│   │   │   ├── show.blade.php              # Traitement demande
│   │   │   └── pdf.blade.php               # Export PDF
│   │   └── users/
│   │       ├── index.blade.php
│   │       └── show.blade.php
│   ├── user/
│   │   ├── dashboard.blade.php
│   │   ├── demandes/
│   │   │   ├── create.blade.php            # Formulaire demande
│   │   │   ├── index.blade.php
│   │   │   ├── show.blade.php              # Suivi avec timeline
│   │   │   └── edit.blade.php
│   │   └── paiement/
│   │       ├── simulation.blade.php        # Page paiement simulé
│   │       └── succes.blade.php
│   └── welcome.blade.php                   # Page d'accueil publique
│
├── routes/
│   ├── web.php                             # Routes web
│   └── api.php                             # Routes API REST
│
└── config/
    └── paytech.php                         # Config PayTech
```

---

## 🔐 Comptes de test (après seeder)

| Rôle        | Email                      | Mot de passe  |
|-------------|----------------------------|---------------|
| Super Admin | admin@passeportsn.sn       | Admin@2024!   |
| Agent Admin | amadou@passeportsn.sn      | Agent@2024!   |
| Utilisateur | fatou.sall@email.com       | User@2024!    |
| Utilisateur | moussa.fall@email.com      | User@2024!    |

---

## 🌐 API REST — Endpoints

### Authentification
```
POST   /api/v1/auth/register     Inscription
POST   /api/v1/auth/login        Connexion → retourne token
POST   /api/v1/auth/logout       Déconnexion (auth requise)
GET    /api/v1/auth/me           Profil utilisateur (auth requise)
```

### Demandes
```
GET    /api/v1/demandes               Liste des demandes
POST   /api/v1/demandes               Créer une demande
GET    /api/v1/demandes/{id}          Détail d'une demande
GET    /api/v1/demandes/{id}/statut   Statut en temps réel
```

### Notifications
```
GET    /api/v1/notifications          Liste notifications
GET    /api/v1/notifications/count    Nombre non lues
POST   /api/v1/notifications/{id}/lu  Marquer comme lue
```

#### Exemple requête API :
```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"fatou.sall@email.com","password":"User@2024!"}'

# Réponse :
{
  "token": "1|abc123...",
  "user": { "id": 3, "nom_complet": "Fatou SALL", ... }
}

# Récupérer les demandes
curl http://localhost:8000/api/v1/demandes \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"
```

---

## 🚀 Déploiement Production

### Variables .env critiques

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.sn

# PayTech réel
PAYTECH_SIMULATION=false
PAYTECH_API_KEY=VOTRE_CLE_PRODUCTION
PAYTECH_API_SECRET=VOTRE_SECRET_PRODUCTION
```

### Commandes d'optimisation

```bash
# Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Storage
php artisan storage:link

# Droits fichiers (Linux)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Nginx (exemple de config)

```nginx
server {
    listen 80;
    server_name passeportsn.sn www.passeportsn.sn;
    root /var/www/passport-renewal/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(env|log|conf)$ { deny all; }
    location /storage { deny all; }  # Documents privés
}
```

---

## 🔒 Sécurité — Bonnes pratiques implémentées

1. **Authentification** — Laravel Breeze + Sanctum pour l'API
2. **Protection CSRF** — `@csrf` sur tous les formulaires web
3. **Validation** — Toutes les entrées validées côté serveur
4. **Upload sécurisé** — Vérification MIME réel, UUID fichiers, disque `private`
5. **Hash mots de passe** — Bcrypt via `Hash::make()`
6. **Policies** — Contrôle d'accès par ressource
7. **Soft Deletes** — Données jamais supprimées définitivement
8. **SHA-256** — Hash de chaque fichier uploadé
9. **IPN PayTech** — Vérification signature HMAC
10. **Rate Limiting** — Configuré via Laravel pour les routes API

---

## 📦 Packages recommandés à installer

```bash
# PDF generation
composer require barryvdh/laravel-dompdf

# Activity logging
composer require spatie/laravel-activitylog

# Image processing
composer require intervention/image

# Excel export
composer require maatwebsite/excel
```

---

## 📞 Support

Pour tout problème : ouvrir une issue sur GitHub ou contacter l'équipe technique.

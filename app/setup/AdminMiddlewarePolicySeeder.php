<?php
// ════════════════════════════════════════════════════════════════════
// app/Http/Middleware/AdminMiddleware.php
// ════════════════════════════════════════════════════════════════════

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
        return $next($request);
    }
}


// ════════════════════════════════════════════════════════════════════
// app/Policies/DemandePolicy.php
// ════════════════════════════════════════════════════════════════════

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;

class DemandePolicy
{
    public function view(User $user, Demande $demande): bool
    {
        return $user->id === $demande->user_id || $user->isAdmin();
    }

    public function update(User $user, Demande $demande): bool
    {
        return $user->id === $demande->user_id && $demande->estModifiable();
    }

    public function delete(User $user, Demande $demande): bool
    {
        return $user->id === $demande->user_id && $demande->statut === 'brouillon';
    }
}


// ════════════════════════════════════════════════════════════════════
// config/paytech.php
// ════════════════════════════════════════════════════════════════════

return [
    'api_key'    => env('PAYTECH_API_KEY', ''),
    'api_secret' => env('PAYTECH_API_SECRET', ''),
    'base_url'   => env('PAYTECH_BASE_URL', 'https://paytech.sn/api'),
    'simulation' => env('PAYTECH_SIMULATION', true),
];


// ════════════════════════════════════════════════════════════════════
// database/seeders/DatabaseSeeder.php
// ════════════════════════════════════════════════════════════════════

namespace Database\Seeders;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ──────────────────────────────────────────
        User::create([
            'nom'        => 'ADMIN',
            'prenom'     => 'Super',
            'email'      => 'admin@passeportsn.sn',
            'password'   => Hash::make('Admin@2024!'),
            'role'       => 'super_admin',
            'telephone'  => '+221 77 000 00 01',
            'nationalite' => 'Sénégalaise',
            'is_active'  => true,
            'email_verified_at' => now(),
        ]);

        // ── Admin secondaire ─────────────────────────────────────
        User::create([
            'nom'        => 'DIALLO',
            'prenom'     => 'Amadou',
            'email'      => 'amadou@passeportsn.sn',
            'password'   => Hash::make('Agent@2024!'),
            'role'       => 'admin',
            'telephone'  => '+221 77 000 00 02',
            'nationalite' => 'Sénégalaise',
            'is_active'  => true,
            'email_verified_at' => now(),
        ]);

        // ── Utilisateurs tests ───────────────────────────────────
        $usersData = [
            ['Fatou',   'SALL',    'fatou.sall@email.com',    '1234567890001'],
            ['Moussa',  'FALL',    'moussa.fall@email.com',   '1234567890002'],
            ['Aissatou','DIOP',    'aissatou.diop@email.com', '1234567890003'],
        ];

        foreach ($usersData as [$prenom, $nom, $email, $cin]) {
            $user = User::create([
                'nom'             => $nom,
                'prenom'          => $prenom,
                'email'           => $email,
                'password'        => Hash::make('User@2024!'),
                'role'            => 'user',
                'telephone'       => '+221 77 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'cin'             => $cin,
                'date_naissance'  => now()->subYears(rand(25, 50)),
                'nationalite'     => 'Sénégalaise',
                'adresse'         => 'Rue ' . rand(1, 100) . ', Dakar',
                'ville'           => 'Dakar',
                'is_active'       => true,
                'email_verified_at' => now(),
            ]);

            // Créer quelques demandes par utilisateur
            $statuts = ['soumise', 'payee', 'en_cours_traitement', 'validee', 'en_attente_paiement'];
            foreach (array_slice($statuts, 0, rand(1, 3)) as $statut) {
                $type = ['ordinaire', 'service'][rand(0, 1)];
                Demande::create([
                    'user_id'             => $user->id,
                    'type_passeport'      => $type,
                    'motif_renouvellement' => 'expiration',
                    'nom_complet'         => $user->nom_complet,
                    'date_naissance'      => $user->date_naissance,
                    'lieu_naissance'      => 'Dakar',
                    'nationalite'         => 'Sénégalaise',
                    'cin'                 => $user->cin,
                    'adresse_residence'   => $user->adresse,
                    'ville'               => 'Dakar',
                    'statut'              => $statut,
                    'urgence'             => rand(0, 4) === 0,
                    'date_soumission'     => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        echo "✅ Seeder terminé !\n";
        echo "   Admin : admin@passeportsn.sn / Admin\@2024!\n";
        echo "   User  : fatou.sall@email.com / User\@2024!\n";
    }
}

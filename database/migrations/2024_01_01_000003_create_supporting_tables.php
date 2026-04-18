<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── TABLE DOCUMENTS ──────────────────────────────────────────────
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type_document', [
                'ancien_passeport',
                'carte_identite',
                'photo_identite',
                'acte_naissance',
                'justificatif_domicile',
                'declaration_perte',
                'autre'
            ]);
            $table->string('nom_original');
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('taille_octets');
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->string('hash_fichier', 64)->nullable()->comment('SHA-256 pour intégrité');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['demande_id', 'type_document']);
        });

        // ─── TABLE PAIEMENTS ──────────────────────────────────────────────
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('reference_paiement', 50)->unique();
            $table->string('transaction_id', 100)->nullable()->comment('ID retourné par PayTech');
            $table->decimal('montant', 10, 2);
            $table->string('devise', 10)->default('XOF');
            $table->enum('methode', ['paytech', 'carte_bancaire', 'mobile_money', 'virement', 'especes'])->default('paytech');
            $table->enum('statut', ['en_attente', 'initie', 'succes', 'echec', 'rembourse', 'annule'])->default('en_attente');
            $table->json('reponse_gateway')->nullable()->comment('Réponse brute PayTech');
            $table->timestamp('date_paiement')->nullable();
            $table->string('ip_paiement', 45)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['demande_id', 'statut']);
            $table->index('reference_paiement');
        });

        // ─── TABLE NOTIFICATIONS ──────────────────────────────────────────
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('demande_id')->nullable()->constrained('demandes')->onDelete('set null');
            $table->enum('type', [
                'demande_soumise',
                'paiement_recu',
                'demande_en_cours',
                'documents_manquants',
                'demande_validee',
                'demande_rejetee',
                'passeport_pret',
                'rdv_confirme',
                'systeme'
            ]);
            $table->string('titre');
            $table->text('message');
            $table->boolean('lu')->default(false);
            $table->enum('canal', ['web', 'email', 'sms'])->default('web');
            $table->timestamp('lu_le')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lu']);
        });

        // ─── TABLE LOGS D'ACTIVITÉ ────────────────────────────────────────
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 100);
            $table->string('model_type', 100)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->json('donnees_avant')->nullable();
            $table->json('donnees_apres')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('documents');
    }
};

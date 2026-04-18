<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_demande', 20)->unique()->comment('REF-YEAR-XXXXX');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('traite_par')->nullable()->constrained('users')->onDelete('set null');

            // Informations du passeport
            $table->string('ancien_numero_passeport', 30)->nullable();
            $table->date('date_expiration_ancien')->nullable();
            $table->enum('type_passeport', ['ordinaire', 'diplomatique', 'service'])->default('ordinaire');
            $table->enum('motif_renouvellement', [
                'expiration', 'perte', 'vol', 'deterioration', 'changement_etat_civil', 'autre'
            ])->default('expiration');
            $table->text('motif_detail')->nullable();

            // Informations personnelles (snapshot au moment de la demande)
            $table->string('nom_complet');
            $table->date('date_naissance');
            $table->string('lieu_naissance', 100);
            $table->string('nationalite', 60)->default('Sénégalaise');
            $table->string('cin', 20);
            $table->text('adresse_residence');
            $table->string('ville', 100);
            $table->string('profession', 100)->nullable();

            // Statut & Traitement
            $table->enum('statut', [
                'brouillon',
                'soumise',
                'en_attente_paiement',
                'payee',
                'en_cours_traitement',
                'documents_manquants',
                'validee',
                'rejetee',
                'passeport_pret',
                'delivre'
            ])->default('brouillon');

            $table->text('commentaire_admin')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->decimal('montant_total', 10, 2)->default(0);
            $table->date('date_soumission')->nullable();
            $table->date('date_traitement')->nullable();
            $table->date('date_validation')->nullable();
            $table->date('date_rdv')->nullable()->comment('Rendez-vous pour retrait');
            $table->boolean('urgence')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'statut']);
            $table->index('numero_demande');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};

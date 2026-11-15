<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etat_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')
                ->constrained('biens')
                ->cascadeOnDelete();
            $table->string('de_etat', 30)->nullable();
            $table->string('vers_etat', 30);
            $table->text('commentaire')->nullable();
            $table->foreignId('auteur_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['bien_id', 'created_at']);
        });

        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')
                ->constrained('biens')
                ->restrictOnDelete();
            $table->string('type', 30)->default('REPARATION');
            $table->text('description');
            $table->string('prestataire')->nullable();
            $table->decimal('cout', 15, 2)->nullable();
            $table->string('etat_precedent', 30);
            $table->string('statut', 20)->default('EN_COURS');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->text('commentaire')->nullable();
            $table->foreignId('auteur_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['bien_id', 'statut']);
        });

        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('lieu')->nullable();
            $table->date('date');
            $table->string('statut', 20)->default('EN_COURS');
            $table->foreignId('agent_responsable_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('cree_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')
                ->constrained('inventaires')
                ->cascadeOnDelete();
            $table->foreignId('bien_id')
                ->constrained('biens')
                ->restrictOnDelete();
            $table->string('statut', 20);
            $table->text('commentaire')->nullable();
            $table->foreignId('releve_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['inventaire_id', 'bien_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
        Schema::dropIfExists('inventaires');
        Schema::dropIfExists('interventions');
        Schema::dropIfExists('etat_historiques');
    }
};

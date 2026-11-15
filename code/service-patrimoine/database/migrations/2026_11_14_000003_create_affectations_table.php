<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')
                ->constrained('biens')
                ->restrictOnDelete();
            $table->foreignId('service_origine_id')
                ->nullable()
                ->constrained('services')
                ->restrictOnDelete();
            $table->foreignId('service_destinataire_id')
                ->constrained('services')
                ->restrictOnDelete();
            $table->string('localisation_origine')->nullable();
            $table->string('localisation_destinataire')->nullable();
            $table->text('motif')->nullable();
            $table->string('statut', 20)->default('EN_ATTENTE');
            $table->date('date_effet')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamp('date_demande');
            $table->timestamp('date_validation')->nullable();
            $table->foreignId('demandeur_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('valideur_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('motif_decision')->nullable();
            $table->timestamps();

            $table->index(['bien_id', 'statut']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};

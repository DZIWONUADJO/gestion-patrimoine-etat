<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biens', function (Blueprint $table) {
            $table->string('numero_inventaire', 100)->unique();
            $table->string('designation', 255);
            $table->text('description')->nullable();
            $table->foreignId('categorie_id')
                ->constrained('categories')
                ->restrictOnDelete();
            $table->foreignId('service_id')
                ->constrained('services')
                ->restrictOnDelete();
            $table->string('etat', 30)->default('BON_ETAT');
            $table->string('localisation')->nullable();
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->string('numero_serie')->nullable();
            $table->date('date_acquisition')->nullable();
            $table->decimal('valeur_acquisition', 15, 2)->nullable();
            $table->string('fournisseur')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archive_le')->nullable();
        });

        Schema::create('bien_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')
                ->constrained('biens')
                ->cascadeOnDelete();
            $table->string('nom');
            $table->string('chemin');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->foreignId('uploade_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bien_documents');

        Schema::table('biens', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropForeign(['service_id']);
            $table->dropUnique(['numero_inventaire']);
            $table->dropColumn([
                'numero_inventaire',
                'designation',
                'description',
                'categorie_id',
                'service_id',
                'etat',
                'localisation',
                'marque',
                'modele',
                'numero_serie',
                'date_acquisition',
                'valeur_acquisition',
                'fournisseur',
                'is_active',
                'archive_le',
            ]);
        });
    }
};

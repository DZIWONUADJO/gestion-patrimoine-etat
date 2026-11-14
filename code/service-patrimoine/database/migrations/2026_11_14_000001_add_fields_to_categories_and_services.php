<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('libelle', 100)->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('code_service', 50)->nullable()->unique();
            $table->string('nom_service')->nullable();
            $table->string('type', 50)->default('SERVICE_ADMINISTRATIF');
            $table->string('responsable')->nullable();
            $table->string('contact', 50)->nullable();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('services')
                ->restrictOnDelete();
            $table->boolean('is_active')->default(true);
        });

        if (Schema::hasTable('users')
            && Schema::hasColumn('users', 'service_id')
            && ! $this->hasServiceForeignKey()) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('service_id')
                    ->references('id')
                    ->on('services')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && $this->hasServiceForeignKey()) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['service_id']);
            });
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropUnique(['code_service']);
            $table->dropColumn([
                'code_service',
                'nom_service',
                'type',
                'responsable',
                'contact',
                'parent_id',
                'is_active',
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['libelle']);
            $table->dropColumn(['libelle', 'description', 'is_active']);
        });
    }

    private function hasServiceForeignKey(): bool
    {
        foreach (Schema::getForeignKeys('users') as $foreignKey) {
            if ($foreignKey['columns'] === ['service_id']
                && $foreignKey['foreign_table'] === 'services') {
                return true;
            }
        }

        return false;
    }
};

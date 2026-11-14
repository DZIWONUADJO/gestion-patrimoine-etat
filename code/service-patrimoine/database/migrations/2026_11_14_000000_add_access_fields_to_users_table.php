<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('CHEF_SERVICE')->index();
            }

            if (! Schema::hasColumn('users', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
        });

        if (Schema::hasTable('services')
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

        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            foreach (['role', 'service_id', 'is_active'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropIndex([$column]);
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
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

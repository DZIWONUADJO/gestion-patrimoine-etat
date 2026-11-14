<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('CHEF_SERVICE')->index();
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['service_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['role', 'service_id', 'is_active']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter les champs de statut
            $table->enum('online_status', ['online', 'offline'])->default('offline')->after('email');
            $table->timestamp('last_seen_at')->nullable()->after('online_status');
            $table->string('profile_picture')->nullable()->after('last_seen_at');
        });

        // Ajouter les champs pour les messages
        Schema::table('messages', function (Blueprint $table) {
            // Ajouter un champ is_new pour les notifications visuelles
            $table->boolean('is_new')->default(true)->after('is_read');
            // Ajouter un champ soft_deleted pour la suppression "douce"
            $table->softDeletes()->after('is_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['online_status', 'last_seen_at', 'profile_picture']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('is_new');
        });
    }
};

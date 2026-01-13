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
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('duration', 8, 2); // en heures avec 2 décimales
            $table->string('location')->nullable();
            $table->enum('type', ['consultation', 'meeting', 'call', 'video', 'personal']);
            $table->enum('status', ['pending', 'confirmed', 'cancelled']);
            $table->foreignId('user_id')->constrained();
            $table->string('participant_name');
            $table->string('participant_email')->nullable();
            $table->string('participant_phone', 20)->nullable();
            $table->boolean('reminder')->default(false);
            $table->time('reminder_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};

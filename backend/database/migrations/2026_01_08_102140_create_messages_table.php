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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->enum('type', ['text', 'image', 'file', 'audio']);
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->constrained('users');
            $table->boolean('has_attachment')->default(false);
            $table->string('attachment_type')->nullable(); // image, file, audio
            $table->string('attachment_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('messages');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

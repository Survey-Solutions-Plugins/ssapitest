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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('identifying_data')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('interviews_count')->default(0);
            $table->boolean('archived')->default(false);
            $table->boolean('audio_recording')->default(false);
            $table->enum('status', ['created', 'assigned', 'completed', 'archived'])->default('created');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

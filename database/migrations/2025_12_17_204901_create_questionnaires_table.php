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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('questionnaire_id');
            $table->string('version');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('document')->nullable();
            $table->boolean('audio_recording_enabled')->default(false);
            $table->enum('criticality_level', ['normal', 'critical'])->default('normal');
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['questionnaire_id', 'version', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};

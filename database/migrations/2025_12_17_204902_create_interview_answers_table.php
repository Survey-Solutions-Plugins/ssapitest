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
        Schema::create('interview_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained()->onDelete('cascade');
            $table->string('question_id');
            $table->json('answer');
            $table->string('variable_name')->nullable();
            $table->enum('question_type', ['text', 'numeric', 'date', 'gps', 'categorical_single', 'categorical_multi', 'list', 'multimedia']);
            $table->timestamps();
            $table->index(['interview_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_answers');
    }
};

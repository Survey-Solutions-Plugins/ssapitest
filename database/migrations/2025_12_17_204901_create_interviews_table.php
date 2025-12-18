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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->string('interview_id')->unique();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('questionnaire_id')->constrained()->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['created', 'interview_completed', 'supervisor_assigned', 'completed_by_interviewer', 'approved_by_supervisor', 'approved_by_headquarters', 'rejected_by_headquarters', 'rejected_by_supervisor'])->default('created');
            $table->json('identifying_data')->nullable();
            $table->json('answers')->nullable();
            $table->boolean('has_errors')->default(false);
            $table->integer('errors_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};

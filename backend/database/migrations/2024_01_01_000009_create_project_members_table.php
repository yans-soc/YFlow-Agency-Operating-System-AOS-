<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_members', function (Blueprint $table) {
            $table->foreignUuid('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignUuid('person_id')->constrained('people')->onDelete('cascade');
            $table->enum('project_role', ['owner', 'manager', 'lead', 'contributor', 'reviewer', 'approver', 'observer', 'client'])->default('contributor');
            $table->primary(['project_id', 'person_id']);
            $table->timestamps();
            
            $table->index('project_id');
            $table->index('person_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_skills', function (Blueprint $table) {
            $table->foreignUuid('person_id')->constrained('people')->onDelete('cascade');
            $table->foreignUuid('skill_id')->constrained('skills')->onDelete('cascade');
            $table->primary(['person_id', 'skill_id']);
            $table->timestamps();
            
            $table->index('person_id');
            $table->index('skill_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_skills');
    }
};
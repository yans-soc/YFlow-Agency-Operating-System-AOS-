<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignees', function (Blueprint $table) {
            $table->foreignUuid('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignUuid('person_id')->constrained('people')->onDelete('cascade');
            $table->primary(['task_id', 'person_id']);
            $table->timestamps();
            
            $table->index('task_id');
            $table->index('person_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignees');
    }
};
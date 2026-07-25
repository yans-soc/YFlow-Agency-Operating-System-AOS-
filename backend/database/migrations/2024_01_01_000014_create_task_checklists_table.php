<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('task_id')->constrained('tasks')->onDelete('cascade');
            $table->string('title', 255);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_checklists');
    }
};
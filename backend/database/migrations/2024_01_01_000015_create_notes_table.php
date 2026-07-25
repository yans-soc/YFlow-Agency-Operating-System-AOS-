<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignUuid('person_id')->nullable()->constrained('people')->onDelete('cascade');
            $table->foreignUuid('created_by')->constrained('people')->onDelete('restrict');
            $table->string('title', 255);
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('workspace_id');
            $table->index(['workspace_id', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
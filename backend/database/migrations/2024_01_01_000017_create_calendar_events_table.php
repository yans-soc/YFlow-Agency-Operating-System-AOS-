<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignUuid('created_by')->constrained('people')->onDelete('restrict');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('location', 255)->nullable();
            $table->enum('type', ['meeting', 'deadline', 'milestone', 'reminder', 'other'])->default('meeting');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('workspace_id');
            $table->index(['workspace_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
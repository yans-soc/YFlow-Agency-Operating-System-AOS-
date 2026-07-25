<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignUuid('uploaded_by')->constrained('people')->onDelete('restrict');
            $table->string('name', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->integer('size')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('workspace_id');
            $table->index(['workspace_id', 'uploaded_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name', 150);
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
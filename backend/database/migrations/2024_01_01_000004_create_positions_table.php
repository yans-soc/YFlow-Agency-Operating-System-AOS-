<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('name', 100);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
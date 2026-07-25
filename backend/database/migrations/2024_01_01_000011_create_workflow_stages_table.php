<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('name', 100);
            $table->integer('sort_order');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['workflow_id', 'sort_order']);
            $table->index('workflow_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_stages');
    }
};
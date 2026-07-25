<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignUuid('lead_id')->nullable();
            $table->string('name', 100);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
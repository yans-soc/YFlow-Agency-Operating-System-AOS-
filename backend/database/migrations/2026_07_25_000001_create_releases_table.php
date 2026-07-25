<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20)->unique();
            $table->text('release_notes')->nullable();
            $table->date('released_at');
            $table->boolean('is_current')->default(false);
            $table->foreignUuid('created_by')->constrained('people');
            $table->timestamps();

            $table->index(['is_current', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
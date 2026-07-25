<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignUuid('team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->onDelete('set null');
            $table->enum('system_role', ['super_admin', 'workspace_admin', 'operations_manager', 'project_manager', 'contributor', 'client'])->default('contributor');
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('phone', 30)->nullable();
            $table->text('avatar')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('workspace_id');
            $table->index(['workspace_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
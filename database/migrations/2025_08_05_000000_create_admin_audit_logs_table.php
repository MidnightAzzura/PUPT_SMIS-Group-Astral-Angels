<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id'); // ID of the admin who performed the action
            $table->string('admin_name'); // Name of the admin for quick reference
            $table->string('admin_role'); // Role (SuperAdmin, Admin)
            $table->string('action_type'); // CREATE, UPDATE, DELETE, APPROVE, DENY, etc.
            $table->string('target_type'); // supply, request, user, etc.
            $table->string('target_id')->nullable(); // ID of the affected item
            $table->string('target_name')->nullable(); // Name/description of the affected item
            $table->json('old_values')->nullable(); // Previous values before change
            $table->json('new_values')->nullable(); // New values after change
            $table->text('description'); // Human readable description
            $table->string('ip_address')->nullable(); // IP address of the admin
            $table->string('user_agent')->nullable(); // Browser/device info
            $table->timestamp('performed_at'); // When the action was performed
            $table->timestamps();

            // Indexes for better performance
            $table->index(['admin_id', 'performed_at']);
            $table->index(['action_type', 'performed_at']);
            $table->index(['target_type', 'target_id']);
            $table->index('performed_at');
            
            // Foreign key constraint
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};

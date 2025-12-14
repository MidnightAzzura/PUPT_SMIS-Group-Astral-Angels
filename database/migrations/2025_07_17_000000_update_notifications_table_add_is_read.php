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
        Schema::table('notifications', function (Blueprint $table) {
            // Add is_read column if it doesn't exist
            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('message');
            }
            
            // Add read_at column if it doesn't exist
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            
            // Add indexes if they don't exist
            if (!Schema::hasIndex('notifications', 'notifications_user_id_is_read_index')) {
                $table->index(['user_id', 'is_read']);
            }
            
            if (!Schema::hasIndex('notifications', 'notifications_office_is_read_index')) {
                $table->index(['office', 'is_read']);
            }
            
            if (!Schema::hasIndex('notifications', 'notifications_action_is_read_index')) {
                $table->index(['action', 'is_read']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'read_at']);
            $table->dropIndex(['user_id', 'is_read']);
            $table->dropIndex(['office', 'is_read']);
            $table->dropIndex(['action', 'is_read']);
        });
    }
};

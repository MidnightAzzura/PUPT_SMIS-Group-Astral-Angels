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
        Schema::table('requests', function (Blueprint $table) {
            // Add columns for tracking expiration if they don't exist
            if (!Schema::hasColumn('requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('requests', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('requests', 'expiration_reason')) {
                $table->text('expiration_reason')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('requests', 'updated_by')) {
                $table->string('updated_by', 255)->nullable()->after('expiration_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'expires_at', 'expiration_reason', 'updated_by']);
        });
    }
};

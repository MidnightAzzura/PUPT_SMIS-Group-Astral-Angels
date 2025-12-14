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
        // Drop the existing table
        Schema::dropIfExists('notification_counters');
        
        // Recreate with proper structure
        Schema::create('notification_counters', function (Blueprint $table) {
            $table->id();
            $table->string('office'); // Office name
            $table->string('page'); // Page/section name (pending, approved, released, etc.)
            $table->integer('count')->default(0);
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();

            $table->unique(['office', 'page']);
            $table->index(['office', 'count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_counters');
    }
};

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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // Type of notification (request_approved, request_denied, etc.)
            $table->unsignedBigInteger('user_id'); // ID of the user who should receive the notification
            $table->string('office'); // Office related to the notification
            $table->text('message'); // Notification message
            $table->boolean('is_read')->default(false); // Whether notification has been read
            $table->timestamp('read_at')->nullable(); // When notification was read
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['office', 'is_read']);
            $table->index(['action', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

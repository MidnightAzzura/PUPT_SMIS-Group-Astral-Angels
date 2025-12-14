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
        Schema::create('archived_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_type'); // 'requests' or 'audit'
            $table->unsignedBigInteger('original_id'); // Original record ID
            $table->json('record_data'); // Serialized original record data
            $table->string('reference')->nullable(); // StockNumber for requests, admin name for audit
            $table->text('description'); // Brief description of the record
            $table->unsignedBigInteger('archived_by'); // User who archived this record
            $table->timestamp('archived_at');
            $table->timestamps();

            // Indexes for better performance
            $table->index(['record_type', 'original_id']);
            $table->index('archived_at');
            $table->index('archived_by');
            
            // Foreign key constraint
            $table->foreign('archived_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_records');
    }
};

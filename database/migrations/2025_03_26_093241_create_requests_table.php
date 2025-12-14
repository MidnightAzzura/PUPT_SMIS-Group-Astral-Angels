<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('requests', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('username', 255);
    //         $table->string('action', 20);
    //         $table->string('supply_name', 255);
    //         $table->text('supply_description');
    //         $table->integer('supply_quantity');
    //         $table->string('status', 50);
    //         $table->timestamp('created_at')->useCurrent();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliesTable extends Migration
{
    // public function up()
    // {
    //     Schema::create('supplies', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('ItemName');
    //         $table->text('Descriptions');
    //         $table->integer('Quantity');
    //         $table->timestamps();
    //     });
    // }

    public function down()
    {
        Schema::dropIfExists('supplies');
    }
}

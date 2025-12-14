<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToSuppliesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('supplies')) return;

        Schema::table('supplies', function (Blueprint $table) {
            // Add column only if it doesn't already exist
            if (! Schema::hasColumn('supplies', 'Category')) {
                $table->string('Category', 100)->nullable()->default('')->after('Unit');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('supplies')) return;

        Schema::table('supplies', function (Blueprint $table) {
            if (Schema::hasColumn('supplies', 'Category')) {
                $table->dropColumn('Category');
            }
        });
    }
}

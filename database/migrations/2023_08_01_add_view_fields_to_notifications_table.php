<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewFieldsToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'view_enabled')) {
                $table->boolean('view_enabled')->default(false)->after('is_read');
            }
            if (!Schema::hasColumn('notifications', 'details_url')) {
                $table->string('details_url')->nullable()->after('view_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'view_enabled')) {
                $table->dropColumn('view_enabled');
            }
            if (Schema::hasColumn('notifications', 'details_url')) {
                $table->dropColumn('details_url');
            }
        });
    }
}

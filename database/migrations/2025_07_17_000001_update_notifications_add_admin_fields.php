<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'admin_id')) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('notifications', 'target_user_id')) {
                $table->unsignedBigInteger('target_user_id')->nullable()->after('admin_id');
            }
            if (!Schema::hasColumn('notifications', 'request_id')) {
                $table->unsignedBigInteger('request_id')->nullable()->after('target_user_id');
            }
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['admin_id', 'target_user_id', 'request_id']);
        });
    }
};

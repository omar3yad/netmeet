<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRecordingsTable extends Migration
{
    public function up()
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->string('username')->after('id');
            $table->string('file_name')->after('username');
            $table->string('file_path')->after('file_name');
            $table->timestamp('recorded_at')->nullable()->after('file_path');
        });
    }

    public function down()
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->dropColumn(['username', 'file_name', 'file_path', 'recorded_at']);
        });
    }
}


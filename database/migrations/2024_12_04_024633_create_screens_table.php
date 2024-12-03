<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreensTable extends Migration
{
    public function up()
    {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // スクリーン1, スクリーン2, スクリーン3
            $table->timestamps();
        });

        // スケジュールテーブルにscreen_idを追加
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('screen_id')->after('movie_id')->constrained('screens');
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('screen_id');
        });
        Schema::dropIfExists('screens');
    }
}

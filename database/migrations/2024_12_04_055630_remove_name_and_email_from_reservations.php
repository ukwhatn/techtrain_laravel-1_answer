<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNameAndEmailFromReservations extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['name', 'email']);
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('name');
            $table->string('email');
        });
    }
}

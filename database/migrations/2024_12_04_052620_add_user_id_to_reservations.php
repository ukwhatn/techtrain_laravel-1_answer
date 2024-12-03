<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUserIdToReservations extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained();
        });

        // データ移行
        if (Schema::hasColumn('reservations', 'user_id')) {
            $this->migrateData();
        }
    }

    private function migrateData(): void
    {
        $users = collect();

        DB::table('reservations')
            ->whereNull('user_id')
            ->orderBy('email')
            ->chunk(100, function ($reservations) use ($users) {
                foreach ($reservations as $reservation) {
                    $user = $users->get($reservation->email);

                    if (!$user) {
                        $user = DB::table('users')->insertGetId([
                            'name' => $reservation->name,
                            'email' => $reservation->email,
                            'password' => Hash::make(Str::random()),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $users->put($reservation->email, $user);
                    }

                    DB::table('reservations')
                        ->where('id', $reservation->id)
                        ->update(['user_id' => $user]);
                }
            });
    }
}

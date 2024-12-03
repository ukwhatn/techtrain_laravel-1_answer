<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Schedule::create([
            'movie_id' => 1,
            'screen_id' => 1,
            'start_time' => '2021-08-01 10:00:00',
            'end_time' => '2021-08-01 12:00:00',
        ]);

        \App\Models\Schedule::create([
            'movie_id' => 2,
            'screen_id' => 2,
            'start_time' => '2021-08-01 13:00:00',
            'end_time' => '2021-08-01 15:00:00',
        ]);

        \App\Models\Schedule::create([
            'movie_id' => 3,
            'screen_id' => 3,
            'start_time' => '2021-08-01 16:00:00',
            'end_time' => '2021-08-01 18:00:00',
        ]);
    }
}

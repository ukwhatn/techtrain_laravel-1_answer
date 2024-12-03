<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SheetTableSeeder::class,
        ]);

        $this->call(GenreSeeder::class);
        $this->call(ScreenSeeder::class);
        $this->call(MovieSeeder::class);
        $this->call(ScheduleSeeder::class);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Screen::create(['name' => 'スクリーン1']);
        \App\Models\Screen::create(['name' => 'スクリーン2']);
        \App\Models\Screen::create(['name' => 'スクリーン3']);
    }
}

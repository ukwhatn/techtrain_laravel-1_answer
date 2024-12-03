<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Genre::create(['name' => 'アクション']);
        \App\Models\Genre::create(['name' => 'コメディ']);
        \App\Models\Genre::create(['name' => 'サスペンス']);
    }
}

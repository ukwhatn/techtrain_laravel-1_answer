<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Movie::create([
            'title' => '映画1',
            'image_url' => 'https://placehold.jp/3d4070/ffffff/150x150.png?text=%E6%98%A0%E7%94%BB1',
            'published_year' => 2021,
            'description' => '映画1の説明',
            'is_showing' => 1,
            'genre_id' => 1,
        ]);

        \App\Models\Movie::create([
            'title' => '映画2',
            'image_url' => 'https://placehold.jp/3d4070/ffffff/150x150.png?text=%E6%98%A0%E7%94%BB2',
            'published_year' => 2021,
            'description' => '映画2の説明',
            'is_showing' => 1,
            'genre_id' => 2,
        ]);

        \App\Models\Movie::create([
            'title' => '映画3',
            'image_url' => 'https://placehold.jp/3d4070/ffffff/150x150.png?text=%E6%98%A0%E7%94%BB3',
            'published_year' => 2021,
            'description' => '映画3の説明',
            'is_showing' => 1,
            'genre_id' => 3,
        ]);
    }
}

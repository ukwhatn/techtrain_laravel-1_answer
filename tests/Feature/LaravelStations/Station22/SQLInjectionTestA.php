<?php

namespace Tests\Feature\LaravelStations\Station22;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SQLInjectionTestA extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function testSQLインジェションによる全件取得の確認(): void
    {
        // ログイン
        $this->actingAs($this->user);

        $injectionKeyword = "' OR 1 = 1 or '";

        $response = $this->get("/movies?keyword=" . urlencode($injectionKeyword));
        $response->assertStatus(200);

        // 全ての映画タイトルが表示されていることを確認
        $movies = Movie::all();
        foreach ($movies as $movie) {
            $response->assertSee($movie->title);
        }

        // SQLインジェクションが発生したことを示すファイルを作成
        $filePath = base_path('test-outputs/temp/station22-a.txt');
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        file_put_contents($filePath, date('Y-m-d H:i:s') . ': SQL Injection test passed');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // ユーザー作成
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@techbowl.com',
            'password' => bcrypt('password123')
        ]);

        // テストデータ作成
        $genre = Genre::create(['name' => 'テストジャンル']);

        for ($i = 1; $i <= 5; $i++) {
            Movie::create([
                'title' => "テスト映画{$i}",
                'image_url' => "https://example.com/image{$i}.jpg",
                'published_year' => 2020 + $i,
                'description' => "テスト映画{$i}の説明",
                'is_showing' => $i % 2,
                'genre_id' => $genre->id
            ]);
        }
    }
}

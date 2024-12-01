<?php

namespace Tests\Feature\LaravelStations\Station20;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\Sheet;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenTest extends TestCase
{
    use RefreshDatabase;

    private int $genreId;
    private CarbonImmutable $showTime;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(); // シートのマスターデータを作成
        $this->genreId = Genre::insertGetId(['name' => 'テストジャンル']);
        $this->showTime = CarbonImmutable::create(2024, 12, 1, 10, 0, 0);
    }

    public function test各スクリーンで異なる作品が同時に上映されているか(): void
    {
        // 3つの異なる映画を作成
        $movie1 = $this->createMovie('映画1');
        $movie2 = $this->createMovie('映画2');
        $movie3 = $this->createMovie('映画3');

        // 同じ時間帯に3つのスケジュールを作成（スクリーン1,2,3で別々の映画）
        $schedule1 = $this->createSchedule($movie1->id, 1);
        $schedule2 = $this->createSchedule($movie2->id, 2);
        $schedule3 = $this->createSchedule($movie3->id, 3);

        // 各スケジュールが異なる映画IDを持っていることを確認
        $this->assertNotEquals($schedule1->movie_id, $schedule2->movie_id);
        $this->assertNotEquals($schedule2->movie_id, $schedule3->movie_id);
        $this->assertNotEquals($schedule3->movie_id, $schedule1->movie_id);

        // それぞれの時間が同じであることを確認
        $this->assertEquals($schedule1->start_time, $schedule2->start_time);
        $this->assertEquals($schedule2->start_time, $schedule3->start_time);
    }

    private function createMovie(string $title): Movie
    {
        return Movie::create([
            'title' => $title,
            'image_url' => 'https://example.com/image.jpg',
            'published_year' => 2024,
            'description' => 'テスト用映画説明',
            'is_showing' => true,
            'genre_id' => $this->genreId,
        ]);
    }

    private function createSchedule(int $movieId, int $screenNumber): Schedule
    {
        return Schedule::create([
            'movie_id' => $movieId,
            'start_time' => $this->showTime,
            'end_time' => $this->showTime->addHours(2),
            'screen_number' => $screenNumber,
        ]);
    }

    public function testユーザー画面にスクリーン番号が表示されていないか(): void
    {
        // 映画とスケジュールを作成
        $movie = $this->createMovie('テスト映画');
        $this->createSchedule($movie->id, 2); // スクリーン2で上映

        // 映画詳細ページを表示
        $response = $this->get('/movies/' . $movie->id);
        $response->assertStatus(200);

        // "スクリーン"という文字列が表示されていないことを確認
        $response->assertDontSeeText('スクリーン');
        $response->assertDontSeeText('SCREEN');
        $response->assertDontSeeText('screen');

        // 数字のみの場合でもスクリーン番号として認識されないよう確認
        $response->assertDontSeeText('スクリーン1');
        $response->assertDontSeeText('スクリーン2');
        $response->assertDontSeeText('スクリーン3');
    }

    public function test異なるスクリーンで同じ座席番号を予約できるか(): void
    {
        // 同じ時間帯に2つの映画のスケジュールを作成（異なるスクリーンで）
        $movie1 = $this->createMovie('映画A');
        $movie2 = $this->createMovie('映画B');

        $schedule1 = $this->createSchedule($movie1->id, 1);
        $schedule2 = $this->createSchedule($movie2->id, 2);

        // 同じ座席番号(例：A-1)で予約を試みる
        $sheet = Sheet::where('row', 'A')->where('column', '1')->first();

        // スクリーン1での予約
        $reservation1 = Reservation::create([
            'schedule_id' => $schedule1->id,
            'sheet_id' => $sheet->id,
            'email' => 'test1@example.com',
            'name' => 'テストユーザー1',
            'date' => $this->showTime->format('Y-m-d'),
        ]);

        // スクリーン2で同じ座席番号の予約を試みる
        try {
            $reservation2 = Reservation::create([
                'schedule_id' => $schedule2->id,
                'sheet_id' => $sheet->id,
                'email' => 'test2@example.com',
                'name' => 'テストユーザー2',
                'date' => $this->showTime->format('Y-m-d'),
            ]);

            // 2つ目の予約が成功したことを確認
            $this->assertDatabaseHas('reservations', [
                'id' => $reservation2->id,
                'schedule_id' => $schedule2->id,
                'sheet_id' => $sheet->id,
            ]);

            // 両方の予約が存在することを確認
            $this->assertEquals(2, Reservation::count());
        } catch (\Exception $e) {
            $this->fail('異なるスクリーンでの同一座席予約に失敗: ' . $e->getMessage());
        }
    }
}

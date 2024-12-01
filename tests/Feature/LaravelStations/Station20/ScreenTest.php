<?php

namespace Tests\Feature\LaravelStations\Station20;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\Screen;
use App\Models\Sheet;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScreenTest extends TestCase
{
    use RefreshDatabase;

    private int $genreId;
    private int $movieId;
    private int $scheduleId;
    private int $screenId;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();

        // 基本データのセットアップ
        $this->genreId = Genre::insertGetId(['name' => 'ジャンル']);
        $this->movieId = Movie::insertGetId([
            'title' => 'テスト映画',
            'image_url' => 'https://example.com/image.jpg',
            'published_year' => 2024,
            'description' => 'テスト用の映画説明',
            'is_showing' => true,
            'genre_id' => $this->genreId,
        ]);

        $startTime = new CarbonImmutable('2024-12-01 10:00:00');
        $this->scheduleId = Schedule::insertGetId([
            'movie_id' => $this->movieId,
            'screen_id' => Screen::first()->id,
            'start_time' => $startTime,
            'end_time' => $startTime->addHours(2),
        ]);
    }

    #[Test]
    #[Group('station20')]
    public function testスクリーンのシード処理が正常に動作する(): void
    {
        $screens = Screen::all();
        $this->assertCount(3, $screens);

        foreach ($screens as $index => $screen) {
            $this->assertEquals('スクリーン' . ($index + 1), $screen->name);
        }
    }

    #[Test]
    #[Group('station20')]
    public function test予約作成時にスクリーンの指定が必須(): void
    {
        $response = $this->post('/reservations/store', [
            'schedule_id' => $this->scheduleId,
            'sheet_id' => Sheet::first()->id,
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'date' => '2024-12-01',
            // screen_id を意図的に省略
        ]);

        $response->assertStatus(302);
        $response->assertInvalid(['screen_id']);
        $this->assertDatabaseCount('reservations', 0);
    }

    #[Test]
    #[Group('station20')]
    public function test同一スケジュールの同一スクリーンで同一座席の重複予約を防ぐ(): void
    {
        // 最初の予約
        $firstReservation = [
            'schedule_id' => $this->scheduleId,
            'screen_id' => Screen::first()->id,
            'sheet_id' => Sheet::first()->id,
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'date' => '2024-12-01',
        ];

        $response = $this->post('/reservations/store', $firstReservation);
        $response->assertStatus(302);
        $this->assertDatabaseCount('reservations', 1);

        // 重複予約の試行
        $response = $this->post('/reservations/store', $firstReservation);
        $response->assertStatus(302);
        $response->assertInvalid(['sheet_id']);
        $this->assertDatabaseCount('reservations', 1);
    }

    #[Test]
    #[Group('station20')]
    public function test異なるスクリーンでは同じ座席を予約可能(): void
    {
        // 1つ目のスクリーンでの予約
        $this->post('/reservations/store', [
            'schedule_id' => $this->scheduleId,
            'screen_id' => Screen::first()->id,
            'sheet_id' => Sheet::first()->id,
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'date' => '2024-12-01',
        ]);

        // 2つ目のスクリーンでの予約
        $response = $this->post('/reservations/store', [
            'schedule_id' => $this->scheduleId,
            'screen_id' => Screen::find(2)->id,
            'sheet_id' => Sheet::first()->id,
            'name' => 'テスト次郎',
            'email' => 'test2@example.com',
            'date' => '2024-12-01',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('reservations', 2);
    }

    #[Test]
    #[Group('station20')]
    public function testスケジュール作成時にスクリーンの指定が必須(): void
    {
        $startTime = new CarbonImmutable('2024-12-01 10:00:00');
        $response = $this->post("/admin/movies/{$this->movieId}/schedules/store", [
            'movie_id' => $this->movieId,
            'start_time_date' => $startTime->format('Y-m-d'),
            'start_time_time' => $startTime->format('H:i'),
            'end_time_date' => $startTime->addHours(2)->format('Y-m-d'),
            'end_time_time' => $startTime->addHours(2)->format('H:i'),
            // screen_id を意図的に省略
        ]);

        $response->assertStatus(302);
        $response->assertInvalid(['screen_id']);
    }

    #[Test]
    #[Group('station20')]
    public function test予約一覧画面にスクリーン情報が表示される(): void
    {
        $reservation = Reservation::create([
            'schedule_id' => $this->scheduleId,
            'screen_id' => Screen::first()->id,
            'sheet_id' => Sheet::first()->id,
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'date' => '2024-12-01',
        ]);

        $response = $this->get('/admin/reservations');
        $response->assertStatus(200);
        $response->assertSee('スクリーン1');
        $response->assertSee($reservation->name);
    }

    #[Test]
    #[Group('station20')]
    public function testモデル間のリレーションが正しく機能する(): void
    {
        // 予約を作成
        $reservation = Reservation::create([
            'schedule_id' => $this->scheduleId,
            'screen_id' => Screen::first()->id,
            'sheet_id' => Sheet::first()->id,
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'date' => '2024-12-01',
        ]);

        // スクリーンから予約を取得できることを確認
        $screen = Screen::find($reservation->screen_id);
        $this->assertTrue($screen->schedules()->exists());
        $this->assertTrue($screen->sheets()->exists());

        // スケジュールからスクリーンを取得できることを確認
        $schedule = Schedule::find($this->scheduleId);
        $this->assertTrue($schedule->screens()->exists());
    }
}

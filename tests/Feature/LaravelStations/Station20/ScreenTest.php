<?php

namespace Tests\Feature\LaravelStations\Station20;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\Screen;
use App\Models\Sheet;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenTest extends TestCase
{
    use RefreshDatabase;

    private Movie $movie;
    private Screen $screen1;
    private Screen $screen2;
    private Sheet $sheet1;
    private Sheet $sheet2;

    public function test重複していない日時の異なるスクリーンでスケジュール作成(): void
    {
        $response = $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '10:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '12:00'
        ]);
        $response->assertSessionHasNoErrors();

        $response = $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen2->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '13:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '15:00'
        ]);
        $response->assertSessionHasNoErrors();
    }

    public function test重複していない日時の同一スクリーンでスケジュール作成(): void
    {
        $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '10:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '12:00'
        ]);

        $response = $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '13:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '15:00'
        ]);
        $response->assertSessionHasNoErrors();
    }

    public function test重複している日時の異なるスクリーンでスケジュール作成(): void
    {
        $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '10:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '12:00'
        ]);

        $response = $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen2->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '11:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '13:00'
        ]);
        $response->assertSessionHasNoErrors();
    }

    public function test重複している日時の同一スクリーンでスケジュール作成(): void
    {
        $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '10:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '12:00'
        ]);

        $response = $this->post('/admin/movies/' . $this->movie->id . '/schedules/store', [
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time_date' => '2024-12-10',
            'start_time_time' => '11:00',
            'end_time_date' => '2024-12-10',
            'end_time_time' => '13:00'
        ]);
        $response->assertSessionHasErrors('screen_id');
    }

    public function test重複していない日時の座席予約(): void
    {
        $schedule1 = Schedule::create([
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time' => new CarbonImmutable('2024-12-10 10:00:00'),
            'end_time' => new CarbonImmutable('2024-12-10 12:00:00')
        ]);

        $schedule2 = Schedule::create([
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen2->id,
            'start_time' => new CarbonImmutable('2024-12-10 13:00:00'),
            'end_time' => new CarbonImmutable('2024-12-10 15:00:00')
        ]);

        // 同じ座席番号で予約
        $response = $this->post('/reservations/store', [
            'schedule_id' => $schedule1->id,
            'sheet_id' => $this->sheet1->id,
            'name' => 'Test User 1',
            'email' => 'techbowl1@techbowl.com',
            'date' => '2024-12-10'
        ]);
        $response->assertSessionHasNoErrors();

        $response = $this->post('/reservations/store', [
            'schedule_id' => $schedule2->id,
            'sheet_id' => $this->sheet1->id,
            'name' => 'Test User 2',
            'email' => 'techbowl2@techbowl.com',
            'date' => '2024-12-10'
        ]);
        $response->assertSessionHasNoErrors();

        // 異なる座席番号で予約
        $response = $this->post('/reservations/store', [
            'schedule_id' => $schedule1->id,
            'sheet_id' => $this->sheet2->id,
            'name' => 'Test User 3',
            'email' => 'techbowl3@techbowl.com',
            'date' => '2024-12-10'
        ]);
        $response->assertSessionHasNoErrors();
    }

    public function test重複している日時の座席予約(): void
    {
        $schedule1 = Schedule::create([
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time' => new CarbonImmutable('2024-12-10 10:00:00'),
            'end_time' => new CarbonImmutable('2024-12-10 12:00:00')
        ]);

        $schedule2 = Schedule::create([
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen2->id,
            'start_time' => new CarbonImmutable('2024-12-10 11:00:00'),
            'end_time' => new CarbonImmutable('2024-12-10 13:00:00')
        ]);

        // 同じ座席番号で予約
        $response = $this->post('/reservations/store', [
            'schedule_id' => $schedule1->id,
            'sheet_id' => $this->sheet1->id,
            'name' => 'Test User 1',
            'email' => 'techbowl1@techbowl.com',
            'date' => '2024-12-10'
        ]);
        $response->assertSessionHasNoErrors();

        $response = $this->post('/reservations/store', [
            'schedule_id' => $schedule2->id,
            'sheet_id' => $this->sheet1->id,
            'name' => 'Test User 2',
            'email' => 'techbowl2@techbowl.com',
            'date' => '2024-12-10'
        ]);
        $response->assertSessionHasNoErrors();

        // 異なる座席番号で予約
        $response = $this->post('/reservations/store', [
            'schedule_id' => $schedule2->id,
            'sheet_id' => $this->sheet2->id,
            'name' => 'Test User 3',
            'email' => 'techbowl3@techbowl.com',
            'date' => '2024-12-10'
        ]);
        $response->assertSessionHasNoErrors();
    }

    public function testユーザ画面にスクリーン名が表示されていない(): void
    {
        $schedule = Schedule::create([
            'movie_id' => $this->movie->id,
            'screen_id' => $this->screen1->id,
            'start_time' => new CarbonImmutable('2024-12-10 10:00:00'),
            'end_time' => new CarbonImmutable('2024-12-10 12:00:00')
        ]);

        $response = $this->get('/movies/' . $this->movie->id);
        $response->assertDontSee('スクリーン1');

        $response = $this->get('/movies/' . $this->movie->id . '/schedules/' . $schedule->id . '/sheets?date=2024-12-10 10:00');
        $response->assertDontSee('スクリーン1');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $genre = Genre::create(['name' => 'テストジャンル']);

        $this->movie = Movie::create([
            'title' => 'テスト映画',
            'image_url' => 'https://test.com/image.jpg',
            'published_year' => 2024,
            'description' => 'テスト概要',
            'is_showing' => true,
            'genre_id' => $genre->id
        ]);

        $this->screen1 = Screen::create(['name' => 'スクリーン1']);
        $this->screen2 = Screen::create(['name' => 'スクリーン2']);

        $this->sheet1 = Sheet::first();
        $this->sheet2 = Sheet::skip(1)->first();
    }
}

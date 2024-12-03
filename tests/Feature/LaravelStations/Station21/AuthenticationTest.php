<?php

namespace Tests\Feature\LaravelStations\Station21;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\Screen;
use App\Models\Sheet;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private Movie $movie;
    private Schedule $schedule;
    private User $user;

    public function test未認証時にアクセス制限されるページ一覧(): void
    {
        $routes = [
            '/movies',
            '/movies/' . $this->movie->id,
            '/sheets',
            '/movies/' . $this->movie->id . '/schedules/' . $this->schedule->id . '/sheets',
            '/movies/' . $this->movie->id . '/schedules/' . $this->schedule->id . '/reservations/create'
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }

        $response = $this->post('/reservations/store', [
            'schedule_id' => $this->schedule->id,
            'sheet_id' => Sheet::first()->id,
            'date' => '2024-12-10'
        ]);
        $response->assertRedirect('/login');
    }

    public function testユーザー登録画面の表示(): void
    {
        $response = $this->get('/users/create');
        $response->assertStatus(200);
        $response->assertSee('name');
        $response->assertSee('email');
        $response->assertSee('password');
        $response->assertSee('password_confirmation');
    }

    public function test空欄があるとユーザー登録できない(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => ''
        ]);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 1); // setUp()で作成した1件のみ
    }

    public function testパスワード確認が一致しないとユーザー登録できない(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@techbowl.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword'
        ]);
        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test正常なユーザー登録(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@techbowl.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 2);
    }

    public function test登録したアカウントでログイン可能(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@techbowl.com',
            'password' => 'password123'
        ]);
        $this->assertAuthenticated();
    }

    public function test予約ページに名前とメールアドレスの入力欄がない(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/movies/' . $this->movie->id . '/schedules/' . $this->schedule->id . '/reservations/create?date=2024-12-10 10:00&sheetId=' . Sheet::first()->id);
        $response->assertDontSee('name="name"');
        $response->assertDontSee('name="email"');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // SheetTableSeederでシートを作成
        $this->seed(\SheetTableSeeder::class);

        $genre = Genre::create(['name' => 'テストジャンル']);
        $screen = Screen::create(['name' => 'スクリーン1']);


        $this->movie = Movie::create([
            'title' => 'テスト映画',
            'image_url' => 'https://test.com/image.jpg',
            'published_year' => 2024,
            'description' => 'テスト概要',
            'is_showing' => true,
            'genre_id' => $genre->id
        ]);

        $this->schedule = Schedule::create([
            'movie_id' => $this->movie->id,
            'screen_id' => $screen->id,
            'start_time' => new CarbonImmutable('2024-12-10 10:00:00'),
            'end_time' => new CarbonImmutable('2024-12-10 12:00:00')
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@techbowl.com',
            'password' => bcrypt('password123'),
        ]);
    }
}

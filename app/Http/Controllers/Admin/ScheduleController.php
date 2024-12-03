<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\Screen;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function store(CreateScheduleRequest $request): RedirectResponse
    {
        $input = $request->validated();
        $data = $this->formatDateForSave($input);

        $startTime = CarbonImmutable::parse($data['start_time']);
        if (CarbonImmutable::parse($data['start_time'])->gte($data['end_time'])) {
            return redirect(route('admin.schedules.create', $data['movie_id']))->withInput()->withErrors([
                'start_time_time' => '終了時刻より前にしてください',
                'end_time_time' => '開始時刻より後にしてください'
            ]);
        }

        if ($startTime->diffInMinutes($data['end_time']) <= 5) {
            return redirect(route('admin.schedules.create', $data['movie_id']))->withInput()->withErrors([
                'start_time_time' => '終了時刻より6分以上前の時刻にしてください',
                'end_time_time' => '開始時刻から6分以上後の時刻にしてください'
            ]);
        }

        if ($this->hasScheduleConflict($input, $data)) {
            return redirect()
                ->route('admin.schedules.create', $input['movie_id'])
                ->withInput()
                ->withErrors(['screen_id' => '指定した時間帯は既に使用されています']);
        }

        Schedule::create($data);
        $request->session()->flash('success', 'スケジュールを保存しました');
        return redirect(route('admin.movies.show', $data['movie_id']));
    }

    private function formatDateForSave(array $data): array
    {
        $data['start_time'] = $data['start_time_date'] . ' ' . $data['start_time_time'];
        $data['end_time'] = $data['end_time_date'] . ' ' . $data['end_time_time'];
        return $data;
    }

    private function hasScheduleConflict(array $input, array $data, ?int $excludeId = null): bool
    {
        $query = Schedule::where('screen_id', $input['screen_id'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']]);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function create(int $movieId): View
    {
        $movie = Movie::findOrFail($movieId);
        $screens = Screen::all()->pluck('name', 'id');  // 追加
        return view('admin.schedules.create', compact('movie', 'screens'));
    }

    public function edit(int $scheduleId): View
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $screens = Screen::all()->pluck('name', 'id');
        $movie = Movie::findOrFail($schedule->movie_id);
        return view('admin.schedules.edit')->with(compact('schedule', 'screens', 'movie'));
    }

    public function update(int $id, UpdateScheduleRequest $request): RedirectResponse
    {
        $input = $request->validated();
        $data = $this->formatDateForSave($input);
        \Illuminate\Support\Facades\Log::error('Validated data:', $input);
        \Illuminate\Support\Facades\Log::error('Formatted data:', $data);

        $startTime = CarbonImmutable::parse($data['start_time']);
        if (CarbonImmutable::parse($data['start_time'])->gte($data['end_time'])) {
            return redirect(route('admin.schedules.edit', $data['movie_id']))->withInput()->withErrors([
                'start_time_time' => '終了時刻より前にしてください',
                'end_time_time' => '開始時刻より後にしてください'
            ]);
        }

        if ($startTime->diffInMinutes($data['end_time']) <= 5) {
            return redirect(route('admin.schedules.edit', $data['movie_id']))->withInput()->withErrors([
                'start_time_time' => '終了時刻より6分以上前の時刻にしてください',
                'end_time_time' => '開始時刻から6分以上後の時刻にしてください'
            ]);
        }

        if ($this->hasScheduleConflict($input, $data, $id)) {
            return redirect()
                ->route('admin.schedules.edit', $id)
                ->withInput()
                ->withErrors(['schedule_conflict' => '指定した時間帯は既に使用されています']);
        }

        $schedule = Schedule::findOrFail($id);
        $schedule->update($data);
        $request->session()->flash('success', 'スケジュールを更新しました');
        return redirect(route('admin.movies.show', $data['movie_id']));
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        $schedule = Schedule::findOrFail($id);
        $movieId = $schedule->movie_id;
        $schedule->delete();
        $request->session()->flash('success', 'スケジュールを削除しました');
        return redirect(route('admin.movies.show', $movieId));
    }
}

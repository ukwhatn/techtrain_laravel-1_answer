@extends('layouts.app')

@section('content')
   <h3 class="mt-3">ご予約ページ</h3>
   <form method="post" action="{{ route('reservations.store') }}">
       @csrf
       <div class="row mb-3">
           <div class="col-6">
               <label><strong>選択中の映画</strong></label>
               <p>{{ $movie->title }}</p>
           </div>
       </div>
       <div class="row mb-3">
           <div class="col-6">
               <label><strong>選択中の日時</strong></label>
               <input type="text" name="date" value="{{ $schedule->start_time->format('Y-m-d') }}" hidden>
               <input type="text" name="schedule_id" value="{{ $schedule->id }}" hidden>
               <p>
                   {{ $schedule->start_time->isoFormat('YYYY年M月D日(ddd)') }}
                   開始時間: {{ $schedule->start_time->format('H:i') }}
                   終了時間: {{ $schedule->end_time->format('H:i') }}
               </p>
           </div>
       </div>
       <div class="row mb-3">
           <div class="col-6">
               <label><strong>選択中の座席</strong></label>
               <input type="text" name="sheet_id" value="{{ $sheet->id }}" hidden>
               <p>
                   {{ strtoupper($sheet->row) }}{{ $sheet->column }}
               </p>
           </div>
       </div>
       <div class="row mb-3">
           <div class="col-6">
               <label><strong>予約者情報</strong></label>
               <p>氏名: {{ Auth::user()->name }}</p>
               <p>メールアドレス: {{ Auth::user()->email }}</p>
           </div>
       </div>
       <div class="row mb-3">
           <div class="col-6">
               <button class="btn btn-primary" type="submit">予約を確定する</button>
           </div>
       </div>
   </form>
@endsection

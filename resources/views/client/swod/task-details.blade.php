@extends('layouts.admin')

@section('content')
    {{-- <h4 class="mb-4">
        @if ($from && $to)
            <br><small class="text-muted">({{ $from }} dan {{ $to }} gacha natijalari)</small>
        @endif
    </h4> --}}
    <form method="GET" action="{{ route('client-task.show', $user->id) }}" class="row justify-content-end mb-4">
        <div class="col-md-2 mb-2">
            <input type="date" name="from_date" value="{{ request('from_date', $from?->format('Y-m-d')) }}" class="form-control">
        </div>
        <div class="col-md-2 mb-2">
            <input type="date" name="to_date" value="{{ request('to_date', $to?->format('Y-m-d')) }}" class="form-control">
        </div>

        <div class="col-md-auto mb-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    @if ($assignments->isEmpty())
        <div class="alert alert-warning">Bu topshiriq bo‘yicha ma'lumot topilmadi.</div>
    @else

    @php
    $fromDate = request('from_date')
        ? \Carbon\Carbon::parse(request('from_date'))
        : \Carbon\Carbon::now()->subMonth()->day(26);

    $toDate = request('to_date')
        ? \Carbon\Carbon::parse(request('to_date'))
        : \Carbon\Carbon::now()->day(25);

    $isDefaultPeriod = $fromDate->day == 26 && $toDate->day == 25 && $fromDate->copy()->addMonth()->month == $toDate->month;

    if ($isDefaultPeriod) {
        $monthName = ucfirst($fromDate->copy()->addMonth()->locale('en')->monthName);
        $message = "$monthName uchun ($user->lastName $user->firstName)ning <b>$task->taskName</b> bo'yicha natijalari";
    } else {
        $message = "{$fromDate->format('d-m-Y')} dan {$toDate->format('d-m-Y')} gacha";
    }
@endphp

<!-- HTMLda chiqarishda raw (xavfsizligini tekshirib qo'ying!) -->
<h4>
    {!! preg_replace('/<b>(.*?)<\/b>/', '<b style="color:red;">$1</b>', $message) !!}
</h4>

{{-- <h4 class="mb-4">{{ $message }}</h4> --}}
    <div class="table-container" style="max-height: 650px; overflow-y: auto;">
        <table class="table table-bordered" id="myTable2">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subtask nomi</th>
                    <th>Ball (Rating)</th>
                    <th>Izoh</th>
                    <th>Sana</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assignments as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->subtask->title }}</td>
                        <td>{{ $item->rating }}</td>
                        <td>{{ $item->comment }}</td>
                        <td>{{ $item->addDate }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>    
    @endif

    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Ortga</a>
@endsection

@extends('layouts.admin')

@section('content')
    <style>
        .deleted-user-header {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .deleted-badge {
            font-size: 12px;
        }

        .deleted-row {
            background-color: #fefefe;
            opacity: 0.9;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            Foydalanuvchi: {{ $user->firstName }} {{ $user->lastName }}
            @if ($isDeleted ?? $user->trashed())
                <span class="badge bg-warning deleted-badge ms-2">
                    <i class="fas fa-trash"></i> O'chirilgan
                </span>
            @endif
        </h4>
    </div>

    @if ($isDeleted ?? $user->trashed())
        <div class="deleted-user-header">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle text-warning me-3" style="font-size: 24px;"></i>
                <div>
                    <strong class="text-warning">Diqqat:</strong>
                    Bu xodim {{ $user->deleted_at->format('d.m.Y') }} sanada o'chirilgan.
                    <br>
                    <small class="text-muted">
                        Bu sahifada faqat tarixiy ma'lumotlar ko'rsatilmoqda.
                        Yangi topshiriqlar qo'shish yoki tahrirlash mumkin emas.
                    </small>
                </div>
            </div>
        </div>
    @endif

    <form method="GET" action="{{ route('client-task.show', $user->id) }}" class="row justify-content-end mb-4">
        <!-- Oylik tanlash -->
        <div class="col-md-2 mb-2">
            <select name="month" class="form-select">
                <option value="">-- Oy bo'yicha filter --</option>
                @foreach ($months as $month)
                    <option value="{{ $month['value'] }}" 
                        {{ $selectedMonth == $month['value'] ? 'selected' : '' }}>
                        {{ $month['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Qo'lda sana kiritish -->
        <div class="col-md-2 mb-2">
            <input type="date" name="from_date"
                value="{{ request('from_date', is_string($from) ? $from : $from?->format('Y-m-d')) }}" 
                class="form-control">
        </div>
        <div class="col-md-2 mb-2">
            <input type="date" name="to_date"
                value="{{ request('to_date', is_string($to) ? $to : $to?->format('Y-m-d')) }}" 
                class="form-control">
        </div>

        <div class="col-md-auto mb-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
        <div class="col-md-auto mb-2">
            <a href="{{ route('client-task.show', $user->id) }}" class="btn btn-secondary">Tozalash</a>
        </div>
    </form>

    @if ($assignments->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i>
            Bu foydalanuvchiga tanlangan muddat uchun topshiriqlar biriktirilmagan.
        </div>
    @else
        @php
            $fromDate = request('from_date') || $selectedMonth
                ? \Carbon\Carbon::parse($from)
                : \Carbon\Carbon::now()->subMonth()->day(26);

            $toDate = request('to_date') || $selectedMonth
                ? \Carbon\Carbon::parse($to)
                : \Carbon\Carbon::now()->day(25);

            $isDefaultPeriod =
                $fromDate->day == 26 && $toDate->day == 25 && $fromDate->copy()->addMonth()->month == $toDate->month;

            if ($isDefaultPeriod) {
                $monthName = ucfirst($fromDate->copy()->addMonth()->locale('uz_Latn')->translatedFormat('F'));
                $message = "$monthName oyi uchun xodim natijalari";
            } else {
                $message = "{$fromDate->format('d-m-Y')} dan {$toDate->format('d-m-Y')} gacha";
            }
        @endphp

        <h3 class="mb-4">{{ $message }}</h3>

        <div class="table-container" style="max-height: 650px; overflow-y: auto;">
            <table class="table table-bordered" id="myTable2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subtask nomi</th>
                        <th>Vazifalar</th>
                        <th>Ball (Rating)</th>
                        <th>Izoh</th>
                        <th>Sana</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assignments as $index => $item)
                        <tr class="{{ $isDeleted ?? $user->trashed() ? 'deleted-row' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->subtask->title }} ({{ $item->subtask->min }} - {{ $item->subtask->max }})</td>
                            <td>{{ $item->subtask->task->taskName }}</td>
                            <td>
                                <span
                                    class="badge bg-{{ $item->rating >= $item->subtask->max * 0.8 ? 'success' : ($item->rating >= $item->subtask->max * 0.5 ? 'warning' : 'danger') }}">
                                    {{ $item->rating }}
                                </span>
                            </td>
                            <td>{{ $item->comment ?: '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->addDate)->format('d.m.Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Statistika -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Statistika
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary">{{ $assignments->count() }}</h4>
                                    <small class="text-muted">Jami topshiriqlar</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success">{{ $assignments->sum('rating') }}</h4>
                                    <small class="text-muted">Jami ball</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info">
                                        {{ $assignments->count() > 0 ? round($assignments->avg('rating'), 2) : 0 }}</h4>
                                    <small class="text-muted">O'rtacha ball</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning">{{ $assignments->max('rating') ?? 0 }}</h4>
                                    <small class="text-muted">Eng yuqori ball</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($isDeleted ?? $user->trashed())
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning bg-opacity-25">
                            <h5 class="mb-0 text-warning">
                                <i class="fas fa-archive"></i> Arxivlangan ma'lumotlar
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">
                                <strong>O'chirilgan sana:</strong>
                                {{ $user->deleted_at->format('d.m.Y H:i') }}
                            </p>
                            <small class="text-muted">
                                Bu xodimning barcha tarixiy ma'lumotlari saqlanib turadi,
                                lekin yangi topshiriqlar qo'shish mumkin emas.
                            </small>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('task.swod') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Ortga
        </a>
    </div>
@endsection
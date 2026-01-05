@extends('layouts.client')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 style="color: #9ac6ff" class="mb-0">Vazifalarni ko'rish</h3>
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('User'))
            <a href="{{ route('assignments.index',['year' => request('year'), 'month' => request('month')]) }}" class="btn btn-outline-light">Orqaga</a>
        @endif
    </div>
        <p><strong>Бажарилган топшириқ номи:</strong> {{ $assignment->name }}</p>
        <p><strong>Ким томонидан берилди:</strong> {{ $assignment->who_from }}</p>
        @if ($assignment->file)
            <p><strong>Fayl:</strong> <a href="{{ asset('storage/assignments/' . basename($assignment->file)) }}" target="_blank">View File</a>
            </p>
            </p>
        @endif
        <p><strong>Sana:</strong> {{ $assignment->date ? \Carbon\Carbon::parse($assignment->date)->format('d M Y') : 'N/A' }}</p>
        <p><strong>Кимга топширилди:</strong> {{ $assignment->who_hand }}</p>
        <p><strong>Лойиҳадаги ижрочилар ва ҳиссалар:</strong> {{ $assignment->people }}</p>
@endsection

@extends('layouts.client')

@section('content')
<div class="mt-4"></div>

@if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('User'))
    <a href="{{ route('assignments.create', ['year' => request('year'), 'month' => request('month')]) }}" class="btn btn-outline-light mt-1 mb-3"><i class="fas fa-upload"></i> Bajarilgan vazifalarni yuklash</a>
	<a href="{{ route('assignments.index') }}" class="btn btn-outline-success mt-1 mb-3"><i class="fas fa-list"></i> Barcha natijalarni ko'rish</a>
@endif

<div style="max-height: 650px; overflow-y: auto;">
    <table id="" style="background-color: #282a3a;">            
        <thead>
            <tr>
                <th>№</th>
                <th>Бажарилган топшириқ номи</th>
                <th>Ким томонидан берилди</th>
                <th>Материални топширган санаси (якуний вариант)</th>
                <th>Кимга топширилди</th>
                <th>Лойиҳадаги ижрочилар ва ҳиссалар</th>
                <th>Comment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assignments as $index => $assignment)
                <tr>
                    <td>{{ ++$index }}</td>
                    <td>{{ $assignment->name }}</td>
                    <td>{{ $assignment->who_from }}</td>
                    <td>{{ $assignment->date ? \Carbon\Carbon::parse($assignment->date)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $assignment->who_hand }}</td>
                    <td>{{ $assignment->people }}</td>
                    <td>{{ $assignment->comment }}</td>
                    <td>
                        @can('view', $assignment)
                            <a href="{{ route('assignments.show', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}" class="btn btn-info">Ko'rish</a>
                        @endcan
                    
                        @can('update', $assignment)
                        <a href="{{ route('assignments.edit', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}" class="btn btn-warning mt-1">
                            O'zgartirish
                        </a>
                        @endcan
                    
                        @can('delete', $assignment)
                            <form action="{{ route('assignments.destroy', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger mt-1">O'chirish</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-light">
                        Tanlangan oraliqda hech qanday topshiriq topilmadi
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- Sahifalash --}}
<div class="mt-3">
    {{ $assignments->appends(request()->query())->links('pagination::bootstrap-4') }}

</div>
@if(request('year') || request('month'))
    <div class="mt-3">
        <small class="">
            Filtr: 
            @if(request('year'))
                Yil - {{ request('year') }}
            @endif
            @if(request('month'))
                @if(request('year')), @endif
                Oy - {{ $months[request('month')] ?? request('month') }}
            @endif
        </small>
    </div>
@endif

@endsection
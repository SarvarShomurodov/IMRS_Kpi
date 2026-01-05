@extends('layouts.client')

@section('content')

    <!-- Action Buttons -->
    @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('User'))
        <div class="action-buttons-wrapper">
            <a href="{{ route('assignments.create', ['year' => request('year'), 'month' => request('month')]) }}"
                class="action-btn action-btn-primary">
                <i class="fas fa-upload"></i>
                <span>Bajarilgan vazifalarni yuklash</span>
            </a>
            <a href="{{ route('assignments.index') }}" class="action-btn action-btn-success">
                <i class="fas fa-list"></i>
                <span>Barcha natijalarni ko'rish</span>
            </a>
        </div>
    @endif

    <!-- Assignments Table -->
    <div class="assignments-table-wrapper">
        <div class="table-responsive">
            <table class="table table-hover assignments-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Topshiriq nomi</th>
                        <th>Kim berdi</th>
                        <th>Topshirgan sanasi</th>
                        <th>Kimga topshirildi</th>
                        <th>Ijrochilаr</th>
                        <th>Izoh</th>
                        <th>Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $index => $assignment)
                        <tr>
                            <td><strong>{{ ++$index }}</strong></td>
                            <td>
                                <div class="assignment-name">
                                    <i class="fas fa-file-alt"></i>
                                    {{ Str::limit($assignment->name, 80) }}
                                </div>
                            </td>
                            <td>
                                <span class="user-badge">
                                    <i class="fas fa-user"></i>
                                    {{ Str::limit($assignment->who_from, 30) }}
                                </span>
                            </td>
                            <td>
                                <span class="date-badge">
                                    <i class="fas fa-calendar"></i>
                                    {{ $assignment->date ? \Carbon\Carbon::parse($assignment->date)->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="user-badge">
                                    <i class="fas fa-user-check"></i>
                                    {{ $assignment->who_hand }}
                                </span>
                            </td>
                            <td>{{ $assignment->people }}</td>
                            <td>
                                @if ($assignment->comment)
                                    <div class="comment-cell">
                                        <i class="fas fa-comment"></i>
                                        {{ Str::limit($assignment->comment) }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons-group">
                                    @can('view', $assignment)
                                        <a href="{{ route('assignments.show', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}"
                                            class="action-btn-sm action-btn-info" title="Ko'rish">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('update', $assignment)
                                        <a href="{{ route('assignments.edit', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}"
                                            class="action-btn-sm action-btn-warning" title="O'zgartirish">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $assignment)
                                        <form
                                            action="{{ route('assignments.destroy', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Rostdan ham o\'chirmoqchimisiz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn-sm action-btn-danger" title="O'chirish">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Tanlangan oraliqda hech qanday topshiriq topilmadi</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    {{-- <div class="pagination-wrapper">
    {{ $assignments->appends(request()->query())->links('pagination::bootstrap-4') }}
</div> --}}
    <div class="d-flex justify-content-end align-items-center mt-4 mb-3">
        <div class="pagination-info me-4">
            <span class="pagination-info-text">
                {{ $assignments->firstItem() }} dan {{ $assignments->lastItem() }} gacha,
                jami {{ $assignments->total() }} ta natija
            </span>
        </div>
        <div class="pagination-wrapper">
            {{ $assignments->appends(request()->query())->links('custom.custom') }}
        </div>
    </div>

    <!-- Filter Info -->
    @if (request('year') || request('month'))
        <div class="filter-info-card">
            <i class="fas fa-filter"></i>
            <strong>Filtr:</strong>
            @if (request('year'))
                <span class="filter-badge">Yil: {{ request('year') }}</span>
            @endif
            @if (request('month'))
                <span class="filter-badge">Oy: {{ $months[request('month')] ?? request('month') }}</span>
            @endif
        </div>
    @endif

@endsection

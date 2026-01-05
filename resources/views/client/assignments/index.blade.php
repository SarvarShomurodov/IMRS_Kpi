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

            <a href="{{ route('assignments.index', ['year' => request('year'), 'month' => request('month'), 'show_all' => 1]) }}"
                class="action-btn action-btn-success">
                <i class="fas fa-list"></i>
                <span>Barcha natijalarni ko'rish</span>
            </a>

            {{-- ✅ Qidiruv tugmasi --}}
            <button type="button" class="action-btn action-btn-info" id="searchToggle">
                <i class="fas fa-search"></i>
                <span>Qidirish</span>
            </button>
        </div>
    @endif

    {{-- ✅ Qidiruv inputi (default yashirin) --}}
    <div class="search-container" id="searchContainer" style="display: none;">
        <form action="{{ route('assignments.index') }}" method="GET" class="search-form">
            {{-- Filtr parametrlarini saqlash --}}
            <input type="hidden" name="year" value="{{ request('year') }}">
            <input type="hidden" name="month" value="{{ request('month') }}">
            @if (request('show_all'))
                <input type="hidden" name="show_all" value="1">
            @endif

            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="search-input" placeholder="Topshiriq nomini kiriting..."
                    value="{{ request('search') }}" autofocus>

                @if (request('search'))
                    <a href="{{ route('assignments.index', array_filter(['year' => request('year'), 'month' => request('month'), 'show_all' => request('show_all')])) }}"
                        class="search-clear">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <button type="submit" class="search-submit">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

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
                            <td><strong>{{ $assignments->firstItem() + $index }}</strong></td>
                            <td>
                                <div class="assignment-name">
                                    <i class="fas fa-file-alt"></i>
                                    {{-- ✅ Qidiruv so'zini highlight qilish --}}
                                    @if (request('search'))
                                        {!! str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', Str::limit($assignment->name, 80)) !!}
                                    @else
                                        {{ Str::limit($assignment->name, 80) }}
                                    @endif
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
                                    @if (request('search'))
                                        <p>"{{ request('search') }}" bo'yicha hech narsa topilmadi</p>
                                        <a href="{{ route('assignments.index', array_filter(['year' => request('year'), 'month' => request('month'), 'show_all' => request('show_all')])) }}"
                                            class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-arrow-left"></i> Barcha topshiriqlarga qaytish
                                        </a>
                                    @else
                                        <p>Tanlangan oraliqda hech qanday topshiriq topilmadi</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-end align-items-center mt-4 mb-3">
        <div class="pagination-info me-4">
            <span class="pagination-info-text">
                {{ $assignments->firstItem() }} dan {{ $assignments->lastItem() }} gacha,
                jami {{ $assignments->total() }} ta natija
            </span>
        </div>
        <div class="pagination-wrapper">
            {{-- ✅ search parametrini ham saqlash --}}
            {{ $assignments->appends(request()->only(['year', 'month', 'show_all', 'search']))->links('custom.custom') }}
        </div>
    </div>

    <!-- Filter Info -->
    <!-- Filter Info -->
    @if (request('year') || request('month') || request('show_all') || request('search'))
        <div class="filter-info-card">
            <i class="fas fa-filter"></i>
            <strong>Filtr:</strong>

            @if (request('search'))
                <span class="filter-badge" style="background: #17a2b8; color: white;">
                    <i class="fas fa-search"></i> "{{ request('search') }}"
                </span>
                <small class="text-muted ms-2">(Barcha ma'lumotlar ichidan qidirish)</small>
            @endif

            @if (request('show_all'))
                <span class="filter-badge" style="background: #28a745; color: white;">
                    <i class="fas fa-check-circle"></i> Barcha natijalar
                </span>
            @endif

            {{-- ✅ Agar qidiruv MAVJUD bo'lsa, yil/oy filtrini ko'rsatmaslik --}}
            @if (!request('search'))
                @if (request('year'))
                    <span class="filter-badge">Yil: {{ request('year') }}</span>
                @endif
                @if (request('month'))
                    @php
                        $monthNames = [
                            '01' => 'Yanvar',
                            '02' => 'Fevral',
                            '03' => 'Mart',
                            '04' => 'Aprel',
                            '05' => 'May',
                            '06' => 'Iyun',
                            '07' => 'Iyul',
                            '08' => 'Avgust',
                            '09' => 'Sentabr',
                            '10' => 'Oktabr',
                            '11' => 'Noyabr',
                            '12' => 'Dekabr',
                        ];
                    @endphp
                    <span class="filter-badge">Oy: {{ $monthNames[request('month')] ?? request('month') }}</span>
                @endif
            @endif
        </div>
    @endif

@endsection

<style>
    /* Qidiruv Container */
    .search-container {
        margin: 20px 0;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .search-form {
        width: 100%;
    }

    .search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        background: white;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 5px 5px 5px 20px;
        transition: all 0.3s ease;
    }

    .search-input-wrapper:focus-within {
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        transform: translateY(-2px);
    }

    .search-icon {
        color: #6c757d;
        font-size: 18px;
        margin-right: 10px;
    }

    .search-input {
        flex: 1;
        border: none;
        outline: none;
        padding: 12px 10px;
        font-size: 15px;
        background: transparent;
        color: #000;
        /* ✅ Text color qo'shildi */
    }

    .search-input::placeholder {
        color: #adb5bd;
    }

    .search-clear {
        padding: 8px 12px;
        color: #dc3545;
        text-decoration: none;
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-clear:hover {
        background: #ffe5e5;
        transform: rotate(90deg);
    }

    .search-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 12px 25px;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
    }

    .search-submit:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    /* ✅ Highlight qidiruv natijasi - TUZATILDI */
    mark {
        background: #555020 !important;
        /* Yorqin sariq */
        padding: 3px 6px;
        border-radius: 4px;
        font-weight: 700;
        color: #000 !important;
        /* Qora matn */
        box-shadow: 0 2px 4px rgba(255, 235, 59, 0.3);
    }

    /* ✅ Assignment name background o'chirish */
    .assignment-name {
        background: transparent !important;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .assignment-name i {
        color: #667eea;
    }

    /* ✅ Barcha ustunlar uchun matnni bir nechta qatorga bo'lish */
    .assignments-table tbody td {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-word;
        line-height: 1.5;
    }

    /* ✅ User badge uchun */
    .user-badge {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-word;
        max-width: 150px;
        line-height: 1.4;
        display: inline-flex !important;
        flex-wrap: wrap;
    }

    /* ✅ Date badge uchun */
    .date-badge {
        white-space: normal !important;
        word-wrap: break-word;
        line-height: 1.4;
    }

    /* ✅ Assignment name uchun */
    .assignment-name {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-word;
        line-height: 1.5;
    }

    /* ✅ Comment cell uchun */
    .comment-cell {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-word;
        line-height: 1.5;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchToggle = document.getElementById('searchToggle');
        const searchContainer = document.getElementById('searchContainer');
        const searchInput = document.querySelector('.search-input');

        // Agar qidiruv mavjud bo'lsa, inputni ochiq qoldirish
        @if (request('search'))
            searchContainer.style.display = 'block';
        @endif

        // Qidiruv tugmasini bosish
        searchToggle.addEventListener('click', function() {
            if (searchContainer.style.display === 'none') {
                searchContainer.style.display = 'block';
                setTimeout(() => searchInput.focus(), 300);
            } else {
                searchContainer.style.display = 'none';
            }
        });

        // ESC tugmasi bosilganda yopish
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchContainer.style.display === 'block') {
                searchContainer.style.display = 'none';
            }
        });
    });
</script>
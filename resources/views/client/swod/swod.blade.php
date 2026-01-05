@extends('layouts.admin')

@section('content')
    <style>
        .sticky-col-left {
            min-width: 20px;
            white-space: nowrap;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background: white;
            z-index: 3;
            min-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .deleted-user {
            background-color: #fff3cd !important;
            opacity: 0.7;
        }

        .deleted-badge {
            font-size: 10px;
            padding: 2px 4px;
        }

        /* ✅ Yashirin task ustunlari */
        .hidden-task-col {
            display: none;
        }

        /* ✅ Loyiha nomi qisqartirish */
        .project-name {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            cursor: help;
            position: relative;
        }

        /* ✅ Tooltip loyiha uchun - YANGI */
        .project-name[title]:hover::before {
            content: attr(title);
            position: absolute;
            left: 0;
            top: 100%;
            background: #2c3e50;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            white-space: normal;
            z-index: 1000;
            min-width: 250px;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            font-size: 13px;
            line-height: 1.4;
            margin-top: 5px;
        }

        /* ✅ Header tooltip */
        .short-header {
            cursor: help;
            position: relative;
            display: inline-block;
        }

        .short-header[title]:hover::before {
            content: attr(title);
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: calc(100% + 10px);
            background: #000000;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            font-size: 14px;
        }

        /* ✅ Toggle button - TABLE USTIDA */
        .toggle-container {
            margin-bottom: 10px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .toggle-tasks-btn {
            cursor: pointer;
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .toggle-tasks-btn:hover {
            background: #0056b3;
        }

        .toggle-tasks-btn i {
            margin-left: 5px;
        }

        /* ✅ Header alignment - markazda */
        #myTable2 thead th {
            position: sticky;
            top: 0;
            /* background: #343a40; */
            /* table-dark rangi */
            z-index: 10;
            vertical-align: middle;
            /* padding: 12px 8px; */
        }

        #myTable2 thead th.sticky-col {
            position: sticky;
            left: 0;
            top: 0;
            z-index: 11;
            /* Boshqa sticky elementlardan yuqori */
            /* background: #343a40; */
            text-align: left;
        }

        /* ✅ Task ustunlari markazda */
        #myTable2 tbody td {
            text-align: center;
            padding: 8px;
        }

        #myTable2 tbody td.sticky-col,
        #myTable2 tbody td:nth-child(3),
        #myTable2 tbody td:nth-child(4) {
            text-align: left;
        }
    </style>

    <h4 class="mb-4">SWOD Tahliliy ma'lumotlar jadvali</h4>

    <!-- Form -->
    <form method="GET" class="row justify-content-end mb-4" id="universalForm">
        <div class="col-md-2 mb-2">
            <select name="month" class="form-control" id="monthSelect">
                <option value="">-- Oy bo'yicha filter --</option>
                @foreach ($months as $month)
                    <option value="{{ $month['value'] }}" {{ $selectedMonth == $month['value'] ? 'selected' : '' }}>
                        {{ $month['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mb-2">
            <input type="date" name="from_date" value="{{ request('from_date', $from ?? '') }}" class="form-control"
                id="from_date" onchange="syncDates()">
        </div>
        <div class="col-md-2 mb-2">
            <input type="date" name="to_date" value="{{ request('to_date', $to ?? '') }}" class="form-control"
                id="to_date" onchange="syncDates()">
        </div>

        <input type="hidden" name="start_date" id="start_date_hidden">
        <input type="hidden" name="end_date" id="end_date_hidden">

        <div class="col-md-2 mb-2">
            <select name="position" class="form-control">
                <option value="">Barcha pozitsiyalar</option>
                @foreach ($positions as $position)
                    <option value="{{ $position }}" {{ request('position') == $position ? 'selected' : '' }}>
                        {{ $position }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-auto mb-2">
            <button type="submit" class="btn btn-primary" onclick="submitForm('{{ route('task.swod') }}')">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="col-md-auto mb-2">
            <button type="submit" class="btn btn-success" onclick="submitForm('{{ route('task-assignments.export') }}')">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </form>

    <script>
        document.getElementById('monthSelect')?.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('from_date').value = '';
                document.getElementById('to_date').value = '';
            }
        });

        document.getElementById('from_date')?.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('monthSelect').value = '';
            }
            syncDates();
        });

        document.getElementById('to_date')?.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('monthSelect').value = '';
            }
            syncDates();
        });

        function syncDates() {
            const fromDate = document.querySelector('input[name="from_date"]').value;
            const toDate = document.querySelector('input[name="to_date"]').value;

            document.getElementById('start_date_hidden').value = fromDate;
            document.getElementById('end_date_hidden').value = toDate;
        }

        function submitForm(actionUrl) {
            syncDates();
            document.getElementById('universalForm').action = actionUrl;
        }

        document.addEventListener('DOMContentLoaded', syncDates);

        // ✅ Task ustunlarini ko'rsatish/yashirish
        let tasksVisible = false;

        function toggleTaskColumns() {
            tasksVisible = !tasksVisible;

            const hiddenCols = document.querySelectorAll('.hidden-task-col');
            const toggleText = document.getElementById('toggleText');
            const toggleIcon = document.getElementById('toggleIcon');

            hiddenCols.forEach(col => {
                col.style.display = tasksVisible ? 'table-cell' : 'none';
            });

            if (tasksVisible) {
                toggleText.textContent = 'Yopish';
                toggleIcon.className = 'fas fa-minus';
            } else {
                toggleText.textContent = 'Barcha tasklar';
                toggleIcon.className = 'fas fa-plus';
            }
        }
    </script>

    @php
        use Carbon\Carbon;

        $fromDate = request('from_date') || $selectedMonth ? Carbon::parse($from) : Carbon::parse($from);
        $toDate = request('to_date') || $selectedMonth ? Carbon::parse($to) : Carbon::parse($to);

        $isDefaultPeriod =
            $fromDate->day == 26 && $toDate->day == 25 && $fromDate->copy()->addMonth()->month == $toDate->month;

        if ($isDefaultPeriod) {
            $monthName = ucfirst($toDate->locale('uz_Latn')->translatedFormat('F'));
            $message = "$monthName oyi uchun KPI";
        } else {
            $message = "{$fromDate->format('d-m-Y')} dan {$toDate->format('d-m-Y')} gacha";
        }
    @endphp

    <h3 class="mb-4">{{ $message }}</h3>

    @php
        $totalBonusAll = $assignments->sum('bonus');
        $totalWithBonusAll = $assignments->sum('total_with_bonus');
        $totalRating = $assignments->sum('total_rating');

        if (request('position')) {
            $staffUsers = $staffUsers->where('position', request('position'));
        }
    @endphp

    {{-- ✅ Toggle button TABLE USTIDA --}}
    <div class="toggle-container">
        <button type="button" class="toggle-tasks-btn" onclick="toggleTaskColumns()">
            <span id="toggleText">Barcha tasklar</span>
            <i class="fas fa-plus" id="toggleIcon"></i>
        </button>
    </div>

    <div class="table-container" style="max-height: 650px; overflow-y: auto;">
    <table class="table table-bordered" id="myTable2" style="width: 100% !important;">
            <thead class="table-dark">
                <tr>
                    <th>№</th>
                    <th class="sticky-col">FISH</th>
                    <th>Lavozim</th>
                    <th>Loyiha nomi</th>

                    @foreach ($tasks as $index => $task)
                        @if ($index === 0)
                            <th>
                                <span class="short-header" title="{{ $task->taskName }}">
                                    {{ Str::limit($task->taskName, 10) }}
                                </span>
                            </th>
                        @else
                            <th class="hidden-task-col">
                                <span class="short-header" title="{{ $task->taskName }}">
                                    {{ Str::limit($task->taskName, 10) }}
                                </span>
                            </th>
                        @endif
                    @endforeach

                    {{-- ✅ Qisqartirilgan headerlar --}}
                    <th>
                        <span class="short-header" title="Institut bo'yicha o'rtacha ball">
                            Institut bo'yi...
                        </span>
                    </th>
                    <th>
                        <span class="short-header" title="Bonussiz umumiy baho ({{ $totalRating }})">
                            Bonussiz...
                        </span>
                    </th>
                    <th>
                        <span class="short-header" title="Bonus ({{ $totalBonusAll }})">
                            Bonus
                        </span>
                    </th>
                    <th>
                        <span class="short-header" title="Jami ({{ $totalWithBonusAll }})">
                            Jami
                        </span>
                    </th>
                    <th>KPI</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach ($staffUsers as $user)
                    @php
                        $roles = $user->getRoleNames();
                        if (
                            $roles->contains('Admin') ||
                            $roles->contains('Super Admin') ||
                            $roles->contains('Texnik')
                        ) {
                            continue;
                        }

                        $data = $assignments[$user->id] ?? [
                            'total_rating' => 0,
                            'bonus' => 0,
                            'total_with_bonus' => 0,
                            'kpi' => 0,
                            'globalAvg' => 0,
                            'tasks' => collect(),
                        ];

                        $taskRatings = [];
                        foreach ($data['tasks'] as $taskData) {
                            $taskRatings[$taskData['task_id']] =
                                ($taskRatings[$taskData['task_id']] ?? 0) + $taskData['rating'];
                        }

                        $isDeleted = $user->trashed();
                    @endphp
                    <tr class="{{ $isDeleted ? 'deleted-user' : '' }}">
                        <td class="sticky-col-left">{{ $i++ }}</td>
                        <td class="sticky-col">
                            <a href="{{ route('client-task.show', ['user' => $user->id, 'month' => $selectedMonth, 'from_date' => request('from_date'), 'to_date' => request('to_date')]) }}"
                                style="text-decoration: none">
                                {{ $user->firstName }} {{ $user->lastName }}

                                @if ($isDeleted)
                                    @php
                                        $deletedDate = \Carbon\Carbon::parse($user->deleted_at);
                                        $periodStart = \Carbon\Carbon::parse($from);
                                        $periodEnd = \Carbon\Carbon::parse($to);
                                        $deletedInPeriod = $deletedDate->between($periodStart, $periodEnd);
                                    @endphp

                                    <span class="badge bg-warning deleted-badge ms-1">
                                        <i class="fas fa-trash"></i>
                                        @if ($deletedInPeriod)
                                            Davrda o'chirilgan
                                        @else
                                            O'chirilgan
                                        @endif
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $user->deleted_at->format('d.m.Y') }} da o'chirilgan
                                    </small>
                                @endif
                            </a>
                        </td>
                        <td>{{ $user->position }}</td>
                        <td>
                            @php
    $currentProjectName = $user->project->name ?? 'Loyiha yo\'q';
    $showOldProject = false;
    $displayProjectName = $currentProjectName; // Default: joriy loyiha
    
    if ($user->project_changed_at && $user->previous_project_id) {
        $changeDate = \Carbon\Carbon::parse($user->project_changed_at);
        $periodStart = \Carbon\Carbon::parse($from);
        $periodEnd = \Carbon\Carbon::parse($to);
        
        // ✅ Agar loyiha davr boshlanishidan OLDIN o'zgartirilgan bo'lsa
        if ($changeDate->lt($periodStart)) {
            // O'tgan davr - yangi loyihani ko'rsat
            $displayProjectName = $currentProjectName;
            $showOldProject = false;
        }
        // ✅ Agar loyiha davr ichida o'zgartirilgan bo'lsa
        elseif ($changeDate->between($periodStart, $periodEnd)) {
            // Davr ichida - ikkala loyihani ham ko'rsat
            $displayProjectName = $user->previousProject->name ?? 'Loyiha yo\'q';
            $showOldProject = true;
        }
        // ✅ Agar loyiha davr tugaganidan KEYIN o'zgartirilgan bo'lsa
        else {
            // Kelajak davr - eski loyihani ko'rsat
            $displayProjectName = $user->previousProject->name ?? 'Loyiha yo\'q';
            $showOldProject = false;
        }
    }
@endphp

{{-- ✅ Asosiy loyiha nomi --}}
<span class="project-name" title="{{ $displayProjectName }}">
    {{ Str::limit($displayProjectName, 30) }}
</span>


                        </td>

                        @foreach ($tasks as $index => $task)
                            @if ($index === 0)
                                <td>
                                    @if ($isDeleted)
                                        {{ $taskRatings[$task->id] ?? 0 }}
                                    @else
                                        <a href="{{ route('client-task.task-details', [
                                            'user' => $user->id,
                                            'task' => $task->id,
                                            'month' => $selectedMonth,
                                            'from_date' => request('from_date'),
                                            'to_date' => request('to_date'),
                                        ]) }}"
                                            style="text-decoration: none">
                                            {{ $taskRatings[$task->id] ?? 0 }}
                                        </a>
                                    @endif
                                </td>
                            @else
                                <td class="hidden-task-col">
                                    @if ($isDeleted)
                                        {{ $taskRatings[$task->id] ?? 0 }}
                                    @else
                                        <a href="{{ route('client-task.task-details', [
                                            'user' => $user->id,
                                            'task' => $task->id,
                                            'month' => $selectedMonth,
                                            'from_date' => request('from_date'),
                                            'to_date' => request('to_date'),
                                        ]) }}"
                                            style="text-decoration: none">
                                            {{ $taskRatings[$task->id] ?? 0 }}
                                        </a>
                                    @endif
                                </td>
                            @endif
                        @endforeach

                        <td>{{ number_format($data['globalAvg'], 2) }}</td>
                        <td>{{ $data['total_rating'] }}</td>
                        <td>{{ $data['bonus'] }}</td>
                        <td>{{ $data['total_with_bonus'] }}</td>
                        <td>{{ round($data['kpi']) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <small class="text-muted">
            <i class="fas fa-info-circle"></i>
            <span class="badge bg-warning">O'chirilgan</span> - Bu xodimlar o'chirilgan, lekin ularning tarixi saqlanib
            turadi.
        </small>
    </div>
@endsection

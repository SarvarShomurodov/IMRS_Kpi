@extends('layouts.client')

@section('content')

@php
    // 'Жарима' taskni ajratib olish
    $taskStatsWithoutJarima = collect($taskStats)->filter(fn($_, $key) => $key !== 'Жарима');
    $jarimaAssignments = $taskStats['Жарима'] ?? null;
    $allUniqueId = 'barcha_tasklar_' . uniqid();
@endphp

<div class="kpi-container" onclick="toggleKpiDropdownWithTable('{{ $allUniqueId }}')">
    <div class="kpi-select-text" style="color: #0dcaf0">
        <i class="fas fa-award"></i> <strong>BARCHA YIG'ILGAN KPI BALLARI BONUS BILAN ({{ number_format($totalWithBonus, 2, '.', '') }})</strong>
    </div>
    <div class="kpi-select-icon" id="arrowIcon_{{ $allUniqueId }}">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M7 10l5 5 5-5H7z"/>
        </svg>
    </div>
</div>

<div class="kpi-container">
    <div class="kpi-select-text" style="color: #198754">
        <i class="fas fa-star"></i> <strong>BONUS ({{ number_format($bonus, 2, '.', '') }})</strong>
    </div>
</div>

<div class="kpi-dropdown mb-3" id="kpiDropdown_{{ $allUniqueId }}" style="display: none;">
    {{-- Table View --}}
    <div class="table-container" style="max-height: 350px; overflow-y: auto;" id="dataTable_{{ $allUniqueId }}" style="display: none;">
        <style>
            .table-container::-webkit-scrollbar {
                width: 3px;
            }
            .table-container::-webkit-scrollbar-track {
                background: transparent;
            }
            .table-container::-webkit-scrollbar-thumb {
                background-color: #7c7c7c;
                border-radius: 10px;
            }
        </style>
        <table id="myTable_{{ $allUniqueId }}" >
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Subtask</th>
                    <th>Date</th>
                    <th>Ball</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                @php $allIndex = 1; @endphp
                @foreach($taskStatsWithoutJarima as $taskName => $data)
                    @foreach($data['assignments'] as $assignment)
                        <tr>
                            <td>{{ $allIndex++ }}</td>
                            <td>{{ $taskName }}</td>
                            <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                            <td>{{ $assignment->addDate }}</td>
                            <td>{{ number_format($assignment->rating, 2, '.', '') }}</td>
                            <td>{{ $assignment->comment ?? 'Comment yo‘q' }}</td>
                        </tr>
                    @endforeach
                @endforeach

                {{-- Жарима table oxirida bo'ladi --}}
                @if($jarimaAssignments)
                    @foreach($jarimaAssignments['assignments'] as $assignment)
                        <tr>
                            <td>{{ $allIndex++ }}</td>
                            <td>Жарима</td>
                            <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                            <td>{{ $assignment->addDate }}</td>
                            <td>{{ number_format($assignment->rating, 2, '.', '') }}</td>
                            <td>{{ $assignment->comment ?? 'Comment yo‘q' }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>


@php
    $taskStatsCollection = collect($taskStats);
    $jarimaData = $taskStatsCollection->pull('Жарима');
@endphp

{{-- Boshqa tasklar --}}
@foreach($taskStatsCollection as $taskName => $data)
    @php
        $uniqueId = Str::slug($taskName) . '_' . uniqid();
    @endphp

    <div class="kpi-container" onclick="toggleDropdown('{{ $uniqueId }}')">
        <div class="kpi-select-text">
            <i class="fas fa-chart-bar"></i> {{ $taskName }} ({{ number_format($data['sum'], 2, '.', '') }})
        </div>
        <div class="kpi-select-icon" id="arrowIcon_{{ $uniqueId }}">
            <svg width="16" height="16" viewBox="0 0 24 24">
                <path d="M7 10l5 5 5-5H7z"/>
            </svg>
        </div>
    </div>

    <div class="kpi-dropdown mb-3" id="kpiDropdown_{{ $uniqueId }}" style="display: none;">
        <div class="switch-wrapper">
            <label class="switch">
                <input type="checkbox" id="tableToggle_{{ $uniqueId }}" onchange="toggleTable('{{ $uniqueId }}')">
                <span class="slider"></span>
            </label>
            <span class="switch-label">Data sifatida ko‘rish</span>
        </div>

        <div class="card-container" id="cardView_{{ $uniqueId }}">
            @foreach($data['assignments'] as $assignment)
                <div class="card mt-3">
                    <div class="section">
                        <div class="block">
                            <span class="icon">📁</span>
                            <div>
                                <div class="label">Task</div>
                                <div class="value">{{ $taskName }}</div>
                            </div>
                        </div>
                        <div class="block">
                            <span class="icon">🖥️</span>
                            <div>
                                <div class="label">Subtask</div>
                                <div class="value">{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</div>
                            </div>
                        </div>
                        <div class="block">
                            <span class="icon">⭐</span>
                            <div>
                                <div class="label">Ball</div>
                                <div class="rating">{{ $assignment->rating }}</div>
                            </div>
                        </div>
                        <div class="block">
                            <span class="icon">📅</span>
                            <div>
                                <div class="label">Sana</div>
                                <div class="date">{{ $assignment->addDate }}</div>
                            </div>
                        </div>
                        <div class="block" style="flex: 1 1 100%">
                            <span class="icon">📝</span>
                            <div class="izoh">{{ $assignment->comment ?? 'Comment yo‘q' }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="table-container" id="dataTable_{{ $uniqueId }}" style="display: none;">
            <table id="myTable_{{ $uniqueId }}">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subtask</th>
                        <th>Date</th>
                        <th>Ball</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['assignments'] as $index => $assignment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                            <td>{{ $assignment->addDate }}</td>
                            <td>{{ number_format($assignment->rating, 2, '.', '') }}</td>
                            <td>{{ $assignment->comment ?? 'Comment yo‘q' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

{{-- Endi Жарима taskini oxirgi bo‘lib chiqaramiz --}}
@if($jarimaData)
    @php
        $taskName = 'Жарима';
        $data = $jarimaData;
        $uniqueId = Str::slug($taskName) . '_' . uniqid();
    @endphp

    <div class="kpi-container" onclick="toggleDropdown('{{ $uniqueId }}')">
        <div class="kpi-select-text">
            <i class="fas fa-chart-bar"></i> {{ $taskName }} ({{ number_format($data['sum'], 2, '.', '') }})
        </div>
        <div class="kpi-select-icon" id="arrowIcon_{{ $uniqueId }}">
            <svg width="16" height="16" viewBox="0 0 24 24">
                <path d="M7 10l5 5 5-5H7z"/>
            </svg>
        </div>
    </div>

    <div class="kpi-dropdown mb-3" id="kpiDropdown_{{ $uniqueId }}" style="display: none;">
        <div class="switch-wrapper">
            <label class="switch">
                <input type="checkbox" id="tableToggle_{{ $uniqueId }}" onchange="toggleTable('{{ $uniqueId }}')">
                <span class="slider"></span>
            </label>
            <span class="switch-label">Data sifatida ko‘rish</span>
        </div>

        <div class="card-container" id="cardView_{{ $uniqueId }}">
            @foreach($data['assignments'] as $assignment)
                <div class="card mt-3">
                    <div class="section">
                        <div class="block">
                            <span class="icon">📁</span>
                            <div>
                                <div class="label">Task</div>
                                <div class="value">{{ $taskName }}</div>
                            </div>
                        </div>
                        <div class="block">
                            <span class="icon">🖥️</span>
                            <div>
                                <div class="label">Subtask</div>
                                <div class="value">{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</div>
                            </div>
                        </div>
                        <div class="block">
                            <span class="icon">⭐</span>
                            <div>
                                <div class="label">Ball</div>
                                <div class="rating">{{ $assignment->rating }}</div>
                            </div>
                        </div>
                        <div class="block">
                            <span class="icon">📅</span>
                            <div>
                                <div class="label">Sana</div>
                                <div class="date">{{ $assignment->addDate }}</div>
                            </div>
                        </div>
                        <div class="block" style="flex: 1 1 100%">
                            <span class="icon">📝</span>
                            <div class="izoh">{{ $assignment->comment ?? 'Comment yo‘q' }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="table-container" id="dataTable_{{ $uniqueId }}" style="display: none;">
            <table id="myTable_{{ $uniqueId }}">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subtask</th>
                        <th>Date</th>
                        <th>Ball</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['assignments'] as $index => $assignment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                            <td>{{ $assignment->addDate }}</td>
                            <td>{{ number_format($assignment->rating, 2, '.', '') }}</td>
                            <td>{{ $assignment->comment ?? 'Comment yo‘q' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif


{{-- JavaScript --}}
<script>
    function toggleKpiDropdownWithTable(id) {
        const dropdown = document.getElementById('kpiDropdown_' + id);
        const table = document.getElementById('dataTable_' + id);
        const arrow = document.getElementById('arrowIcon_' + id).querySelector('svg');

        const isVisible = dropdown.style.display === 'block';

        if (isVisible) {
            // Yopamiz
            dropdown.style.display = 'none';
            table.style.display = 'none';
            arrow.classList.remove('rotate');
        } else {
            // Ochamiz
            dropdown.style.display = 'block';
            table.style.display = 'block';
            arrow.classList.add('rotate');
        }
    }
    function toggleDropdown(id) {
        const dropdown = document.getElementById('kpiDropdown_' + id);
        const arrow = document.getElementById('arrowIcon_' + id).querySelector('svg');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        arrow.classList.toggle('rotate');
    }

    function toggleTable(id) {
        const isChecked = document.getElementById('tableToggle_' + id).checked;
        document.getElementById('dataTable_' + id).style.display = isChecked ? 'block' : 'none';
        document.getElementById('cardView_' + id).style.display = isChecked ? 'none' : 'block';
    }
</script>
@endsection

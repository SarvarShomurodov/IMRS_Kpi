@extends('layouts.client')

@section('content')

    @php
        // 'Жарима' taskni ajratib olish
        $taskStatsWithoutJarima = collect($taskStats)->filter(fn($_, $key) => $key !== 'Жарима');
        $jarimaAssignments = $taskStats['Жарима'] ?? null;
        $allUniqueId = 'barcha_tasklar_' . uniqid();
    @endphp

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total with Bonus Card -->
        <div class="col-md-6">
            <div class="summary-card summary-card-primary" onclick="toggleKpiDropdownWithTable('{{ $allUniqueId }}')">
                <div class="summary-icon">
                    <i class="fas fa-award"></i>
                </div>
                <div class="summary-content">
                    <h6 class="summary-title">UMUMIY KPI BALLARI</h6>
                    <div class="summary-value">{{ number_format($totalWithBonus, 2, '.', '') }}</div>
                    <span class="summary-subtitle">Bonus bilan</span>
                </div>
                <div class="summary-arrow" id="arrowIcon_{{ $allUniqueId }}">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Bonus Card -->
        <div class="col-md-6">
            <div class="summary-card summary-card-success">
                <div class="summary-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="summary-content">
                    <h6 class="summary-title">BONUS</h6>
                    <div class="summary-value">{{ number_format($bonus, 2, '.', '') }}</div>
                    <span class="summary-subtitle">Qo'shimcha ball</span>
                </div>
            </div>
        </div>
    </div>

    <!-- All Tasks Dropdown -->
    <div class="kpi-dropdown-container mb-4" id="kpiDropdown_{{ $allUniqueId }}" style="display: none;">
        <div class="table-responsive">
            <table class="table table-hover" id="myTable_{{ $allUniqueId }}">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Task</th>
                        <th>Subtask</th>
                        <th>Sana</th>
                        <th>Ball</th>
                        <th>Izoh</th>
                    </tr>
                </thead>
                <tbody>
                    @php $allIndex = 1; @endphp
                    @foreach ($taskStatsWithoutJarima as $taskName => $data)
                        @foreach ($data['assignments'] as $assignment)
                            <tr>
                                <td>{{ $allIndex++ }}</td>
                                <td><span class="badge bg-primary">{{ $taskName }}</span></td>
                                <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                                <td>{{ $assignment->addDate }}</td>
                                <td><strong>{{ number_format($assignment->rating, 2, '.', '') }}</strong></td>
                                <td>{{ $assignment->comment ?? 'Izoh yoq' }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                    {{-- Жарима table oxirida --}}
                    @if ($jarimaAssignments)
                        @foreach ($jarimaAssignments['assignments'] as $assignment)
                            <tr class="table-danger">
                                <td>{{ $allIndex++ }}</td>
                                <td><span class="badge bg-danger">Жарима</span></td>
                                <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                                <td>{{ $assignment->addDate }}</td>
                                <td><strong>{{ number_format($assignment->rating, 2, '.', '') }}</strong></td>
                                <td>{{ $assignment->comment ?? 'Izoh yoq' }}</td>
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

    <!-- Task Accordions -->
    <div class="tasks-container">
        @foreach ($taskStatsCollection as $taskName => $data)
            @php
                $uniqueId = Str::slug($taskName) . '_' . uniqid();
            @endphp

            <div class="task-accordion mb-3">
                <div class="task-header" onclick="toggleDropdown('{{ $uniqueId }}')">
                    <div class="task-info">
                        <i class="fas fa-folder-open"></i>
                        <span class="task-name">{{ $taskName }}</span>
                        <span class="task-badge">{{ number_format($data['sum'], 2, '.', '') }}</span>
                    </div>
                    <div class="task-arrow" id="arrowIcon_{{ $uniqueId }}">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                <div class="task-content" id="kpiDropdown_{{ $uniqueId }}" style="display: none;">
                    <!-- Toggle Switch -->
                    <div class="view-toggle-wrapper">
                        <label class="view-toggle">
                            <input type="checkbox" id="tableToggle_{{ $uniqueId }}"
                                onchange="toggleTable('{{ $uniqueId }}')">
                            <span class="toggle-slider-custom"></span>
                        </label>
                        <span class="toggle-label-custom">
                            <i class="fas fa-table"></i> Jadval ko'rinishi
                        </span>
                    </div>

                    <!-- Card View -->
                    <div class="assignments-grid" id="cardView_{{ $uniqueId }}">
                        @foreach ($data['assignments'] as $assignment)
                            <div class="assignment-card">
                                <div class="assignment-header">
                                    <span class="assignment-badge">{{ $taskName }}</span>
                                    <span class="assignment-rating">
                                        <i class="fas fa-star"></i>BALL ({{ $assignment->rating }})
                                    </span>
                                </div>
                                <div class="assignment-body">
                                    <div class="assignment-item">
                                        <i class="fas fa-tasks"></i>
                                        <div>
                                            <span class="item-label">Subtask</span>
                                            <span class="item-value">{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</span>
                                        </div>
                                    </div>
                                    <div class="assignment-item">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <span class="item-label">Sana</span>
                                            <span class="item-value">{{ $assignment->addDate }}</span>
                                        </div>
                                    </div>
                                    @if ($assignment->comment)
                                        <div class="assignment-comment">
                                            <i class="fas fa-comment"></i>
                                            <p>{{ $assignment->comment }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Table View -->
                    <div class="table-responsive" id="dataTable_{{ $uniqueId }}" style="display: none;">
                        <table class="table table-hover" id="myTable_{{ $uniqueId }}">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subtask</th>
                                    <th>Sana</th>
                                    <th>Ball</th>
                                    <th>Izoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['assignments'] as $index => $assignment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                                        <td>{{ $assignment->addDate }}</td>
                                        <td><strong>{{ number_format($assignment->rating, 2, '.', '') }}</strong></td>
                                        <td>{{ $assignment->comment ?? 'Izoh yoq' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Жарима task oxirida --}}
        @if ($jarimaData)
            @php
                $taskName = 'Жарима';
                $data = $jarimaData;
                $uniqueId = Str::slug($taskName) . '_' . uniqid();
            @endphp

            <div class="task-accordion task-accordion-danger mb-3">
                <div class="task-header" onclick="toggleDropdown('{{ $uniqueId }}')">
                    <div class="task-info">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="task-name">{{ $taskName }}</span>
                        <span class="task-badge task-badge-danger">{{ number_format($data['sum'], 2, '.', '') }}</span>
                    </div>
                    <div class="task-arrow" id="arrowIcon_{{ $uniqueId }}">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                <div class="task-content" id="kpiDropdown_{{ $uniqueId }}" style="display: none;">
                    <div class="view-toggle-wrapper">
                        <label class="view-toggle">
                            <input type="checkbox" id="tableToggle_{{ $uniqueId }}"
                                onchange="toggleTable('{{ $uniqueId }}')">
                            <span class="toggle-slider-custom"></span>
                        </label>
                        <span class="toggle-label-custom">
                            <i class="fas fa-table"></i> Jadval ko'rinishi
                        </span>
                    </div>

                    <div class="assignments-grid" id="cardView_{{ $uniqueId }}">
                        @foreach ($data['assignments'] as $assignment)
                            <div class="assignment-card assignment-card-danger">
                                <div class="assignment-header">
                                    <span class="assignment-badge badge-danger">{{ $taskName }}</span>
                                    <span class="assignment-rating rating-danger">
                                        <i class="fas fa-exclamation-circle"></i> {{ $assignment->rating }}
                                    </span>
                                </div>
                                <div class="assignment-body">
                                    <div class="assignment-item">
                                        <i class="fas fa-tasks"></i>
                                        <div>
                                            <span class="item-label">Subtask</span>
                                            <span
                                                class="item-value">{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</span>
                                        </div>
                                    </div>
                                    <div class="assignment-item">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <span class="item-label">Sana</span>
                                            <span class="item-value">{{ $assignment->addDate }}</span>
                                        </div>
                                    </div>
                                    @if ($assignment->comment)
                                        <div class="assignment-comment">
                                            <i class="fas fa-comment"></i>
                                            <p>{{ $assignment->comment }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="table-responsive" id="dataTable_{{ $uniqueId }}" style="display: none;">
                        <table class="table table-hover" id="myTable_{{ $uniqueId }}">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subtask</th>
                                    <th>Sana</th>
                                    <th>Ball</th>
                                    <th>Izoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['assignments'] as $index => $assignment)
                                    <tr class="table-danger">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $assignment->subtask->title ?? 'Nomaʼlum' }}</td>
                                        <td>{{ $assignment->addDate }}</td>
                                        <td><strong>{{ number_format($assignment->rating, 2, '.', '') }}</strong></td>
                                        <td>{{ $assignment->comment ?? 'Izoh yoq' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection

@section('scripts')
    <script>
        function toggleKpiDropdownWithTable(id) {
            const dropdown = document.getElementById('kpiDropdown_' + id);
            const arrow = document.getElementById('arrowIcon_' + id).querySelector('i');

            const isVisible = dropdown.style.display === 'block';

            if (isVisible) {
                dropdown.style.display = 'none';
                arrow.classList.remove('fa-chevron-up');
                arrow.classList.add('fa-chevron-down');
            } else {
                dropdown.style.display = 'block';
                arrow.classList.remove('fa-chevron-down');
                arrow.classList.add('fa-chevron-up');
            }
        }

        function toggleDropdown(id) {
            const dropdown = document.getElementById('kpiDropdown_' + id);
            const arrow = document.getElementById('arrowIcon_' + id).querySelector('i');

            const isVisible = dropdown.style.display === 'block';

            if (isVisible) {
                dropdown.style.display = 'none';
                arrow.classList.remove('fa-chevron-up');
                arrow.classList.add('fa-chevron-down');
            } else {
                dropdown.style.display = 'block';
                arrow.classList.remove('fa-chevron-down');
                arrow.classList.add('fa-chevron-up');
            }
        }

        function toggleTable(id) {
            const isChecked = document.getElementById('tableToggle_' + id).checked;
            const dataTable = document.getElementById('dataTable_' + id);
            const cardView = document.getElementById('cardView_' + id);

            if (isChecked) {
                // Checkbox belgilangan - faqat table ko'rsat
                dataTable.style.display = 'block';
                cardView.style.display = 'none';
            } else {
                // Checkbox olib tashlangan - faqat cardlarni ko'rsat
                dataTable.style.display = 'none';
                cardView.style.display = 'grid'; // 'block' o'rniga 'grid'
            }
        }
    </script>
@endsection

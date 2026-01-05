@extends('layouts.client')

@section('content')
    <!-- Statistics Cards -->
    <div class="row g-4 mt-2">
        @php
            $taskGroups = collect($assignments)->first(); // authenticated userning assignmenti

            // ✅ tasks mavjudligini tekshirish
            $tasks = $taskGroups['tasks'] ?? [];
            
            // task_name bo'yicha guruhlab, har bir task_name uchun umumiy rating hisoblash
            $groupedTasks = collect($tasks)
                ->sortBy('task_id')
                ->groupBy('task_name')
                ->map(function ($items, $key) {
                    return [
                        'task_name' => $key,
                        'total_rating' => $items->sum('rating'),
                    ];
                });

            // Index uchun counter
            $cardIndex = 0;
        @endphp

        {{-- ✅ AGAR TASKLAR BO'LSA --}}
        @if($groupedTasks->isNotEmpty())
            {{-- Har bir task_name uchun kartochka --}}
            @foreach ($groupedTasks as $task)
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card stats-card-{{ $cardIndex % 4 }}">
                        <div class="stats-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stats-content">
                            <h6 class="stats-title">{{ $task['task_name'] ?? 'Nomaʼlum topshiriq' }}</h6>
                            <div class="stats-value">{{ number_format($task['total_rating'], 2) }}</div>
                            <span class="stats-label">BALL</span>
                        </div>
                    </div>
                </div>
                @php
                    $cardIndex++;
                @endphp
            @endforeach
        @else
            {{-- ✅ TASKLAR BO'SH BO'LSA --}}
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    Bu davr uchun sizda hech qanday topshiriq baholanmagan.
                </div>
            </div>
        @endif

        {{-- Bonus kartochkasi --}}
        <div class="col-md-3 col-sm-6">
            <div class="stats-card stats-card-bonus">
                <div class="stats-icon stats-icon-bonus">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="stats-content">
                    <h6 class="stats-title">BONUS</h6>
                    <div class="stats-value">{{ number_format($taskGroups['bonus'] ?? 0, 2) }}</div>
                    <span class="stats-label">QO'SHIMCHA BALL</span>
                </div>
            </div>
        </div>

        {{-- Umumiy ball kartochkasi --}}
        <div class="col-md-3 col-sm-6">
            <div class="stats-card stats-card-total">
                <div class="stats-icon stats-icon-total">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stats-content">
                    <h6 class="stats-title">UMUMIY BALL</h6>
                    <div class="stats-value">
                        {{ number_format($taskGroups['total_with_bonus'] ?? $groupedTasks->sum('total_rating'), 2) }}
                    </div>
                    <span class="stats-label">BONUS BILAN</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-section mt-4">
        <div class="chart-header">
            <div class="chart-title">
                <i class="fas fa-chart-line"></i>
                <span><strong style="color: red">{{ auth()->user()->lastName }} {{ auth()->user()->firstName }}</strong>ning
                    barcha oydagi natijalari</span>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggleChart" onchange="toggleChartVisibility()">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="chart-container" id="chartContainer" style="display: none;">
            <canvas id="bonusChart"></canvas>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        // Sahifa yuklanganda dark mode tekshirish
        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'enabled' && !document.body.classList.contains('dark-mode')) {
                document.body.classList.add('dark-mode');
            }
        });

        // Chart options function
        function getChartOptions() {
            const isDarkMode = document.body.classList.contains('dark-mode') || document.documentElement.classList
                .contains('dark-mode');
            const textColor = isDarkMode ? '#e0e0e0' : '#495057';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        color: textColor,
                        anchor: 'end',
                        align: 'top',
                        offset: 8,
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        formatter: function(value) {
                            return value > 0 ? value : '';
                        }
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? 'rgba(0, 0, 0, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                        titleColor: isDarkMode ? '#fff' : '#000',
                        bodyColor: isDarkMode ? '#fff' : '#000',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2,
                        padding: 12
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Oylar',
                            color: textColor,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: dynamicMax, // Dinamik maksimal qiymat
                        title: {
                            display: true,
                            text: 'Ball',
                            color: textColor,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            };
        }

        // Chart data
        const chartData = [
            @foreach ($totalWithBonusByMonth as $month => $value)
                {{ $value }},
            @endforeach
        ];

        // Maksimal qiymatni topish va 10-15% qo'shish
        const maxDataValue = Math.max(...chartData);
        const dynamicMax = Math.ceil(maxDataValue * 1.15); // 15% qo'shish va yuqoriga yaxlitlash

        const ctx = document.getElementById('bonusChart').getContext('2d');
        const bonusChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    'Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun',
                    'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'
                ],
                datasets: [{
                    label: 'Umumiy baxo bonus bilan',
                    data: chartData,
                    borderColor: 'rgba(13, 110, 253, 1)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8
                }]
            },
            options: getChartOptions(),
            plugins: [ChartDataLabels]
        });

        // Dark mode uchun grafik ranglarini o'zgartirish
        function updateChartForDarkMode() {
            bonusChart.options = getChartOptions();
            bonusChart.update();
        }

        // Dark mode toggle eventini tinglash
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                setTimeout(updateChartForDarkMode, 100);
            });
        }

        // Sahifa to'liq yuklangandan so'ng
        window.addEventListener('load', function() {
            updateChartForDarkMode();
        });
    </script>

    <script>
        function toggleChartVisibility() {
            const checkbox = document.getElementById('toggleChart');
            const chartContainer = document.getElementById('chartContainer');

            if (checkbox.checked) {
                chartContainer.style.display = 'block';
                setTimeout(() => {
                    chartContainer.classList.add('show');
                    bonusChart.resize();
                }, 10);
            } else {
                chartContainer.classList.remove('show');
                setTimeout(() => {
                    chartContainer.style.display = 'none';
                }, 300);
            }
        }
    </script>
@endsection
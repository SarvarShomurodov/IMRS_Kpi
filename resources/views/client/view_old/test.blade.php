@extends('layouts.client')

@section('content')
    <div class="row g-3 mt-3">
        @php
            $taskGroups = collect($assignments)->first(); // authenticated userning assignmenti

            // task_name bo‘yicha guruhlab, har bir task_name uchun umumiy rating hisoblash
            $groupedTasks = collect($taskGroups['tasks'] ?? [] )
                ->sortBy('task_id')
                ->groupBy('task_name')
                ->map(function ($items, $key) {
                    return [
                        'task_name' => $key,
                        'total_rating' => $items->sum('rating'),
                    ];
                });
        @endphp

        {{-- Har bir task_name uchun kartochka --}}
        @foreach ($groupedTasks as $task)
            <div class="col-md-3">
                <div class="card text-white h-100 shadow">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-chart-bar"></i>{{ $task['task_name'] ?? 'Nomaʼlum topshiriq' }}</h6>
                        <p class="card-text mb-0">BALL</p>
                        <h4>{{ number_format($task['total_rating'], 2) }}</h4>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Bonus kartochkasi --}}
        <div class="col-md-3">
            <div class="card border-success bg-dark text-white h-100 shadow">
                <div class="card-body">
                    <h6 class="card-title text-success"><i class="fas fa-star"></i> BONUS</h6>
                    <p class="card-text mb-0">BALL</p>
                    <h4>{{ number_format(collect($assignments)->first()['bonus'] ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>

        {{-- Umumiy ball kartochkasi --}}
        <div class="col-md-3">
            <div class="card border-info text-white bg-dark h-100 shadow">
                <div class="card-body">
                    <h6 class="card-title text-info"><i class="fas fa-award"></i> UMUMIY BONUS BILAN</h6>
                    <p class="card-text mb-0">BALL</p>
                    <h4>{{ number_format($taskGroups['total_with_bonus'] ?? $groupedTasks->sum('total_rating'), 2) }}</h4>
                </div>
            </div>
        </div>
        <hr>
        <div class="switch-wrapper mb-3">
            <label class="switch">
                <input type="checkbox" id="toggleChart" onchange="toggleChartVisibility()">
                <span class="slider"></span>
            </label>
            <span class="switch-label"><strong style="color: red">{{ auth()->user()->lastName }} {{ auth()->user()->firstName }}ning</strong> barcha oydagi natijalari</span>
        </div>
        {{-- Grafik --}}
        <div class="col-md-12" id="chartContainer" style="display: none;">
            <canvas id="bonusChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
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
                    data: [
                        @foreach ($totalWithBonusByMonth as $month => $value)
                            {{ $value }},
                        @endforeach
                    ],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        color: '#ffffff',
                        anchor: 'end',
                        align: 'top',
                        font: {
                            weight: 'bold'
                        },
                        formatter: function(value) {
                            return value;
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Oylar',
                            color: '#ffffff'
                        },
                        ticks: {
                            color: '#ffffff'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#ffffff'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>
    
    <script>
        function toggleChartVisibility() {
            const checkbox = document.getElementById('toggleChart');
            const chartContainer = document.getElementById('chartContainer');
            chartContainer.style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
@endsection

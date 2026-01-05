@extends('layouts.admin')

@section('content')
<div class="mt-5">
    <h3 class="mb-4">{{ $user->firstName }} {{ $user->lastName }} - ({{ $user->position }})</h3>

    <canvas id="kpiChart" height="170"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    const kpiData = @json($kpiResults);

    const allTasksSet = new Set();
    kpiData.forEach(month => {
        Object.keys(month.task_ratings).forEach(taskName => {
            allTasksSet.add(taskName);
        });
    });
    const allTasks = Array.from(allTasksSet);

    const taskColors = [
        'rgba(255, 0, 0, 0.8)',       // Qizil
        'rgba(0, 128, 255, 0.8)',     // Ko'k
        'rgba(255, 215, 0, 0.8)',     // Sariq
        'rgba(0, 255, 128, 0.8)',     // Yashil
        'rgba(128, 0, 255, 0.8)',     // Binafsha
        'rgba(255, 102, 0, 0.8)',     // To'q sariq
        'rgba(255, 20, 147, 0.8)',    // Pushti
        'rgba(0, 255, 255, 0.8)'      // Cyan
    ];

    const datasets = allTasks.map((taskName, idx) => {
        return {
            label: taskName,
            data: kpiData.map(month => month.task_ratings[taskName] ?? 0),
            backgroundColor: taskColors[idx % taskColors.length],
            stack: 'combined'
        };
    });

    // Bonus bar
    datasets.push({
        label: 'Bonus',
        data: kpiData.map(x => x.bonus ?? 0),
        backgroundColor: 'rgba(75, 192, 192, 0.7)',
        stack: 'combined'
    });

    const ctx = document.getElementById('kpiChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: kpiData.map(x => x.month),
            datasets: datasets
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Ballar'
                    }
                },
                y: {
                    stacked: true
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const index = context.dataIndex;
                            const item = kpiData[index];
                            const totalWithBonus = item.total_with_bonus;

                            let contribution = 0;
                            if (totalWithBonus > 0 && item.kpi > 0) {
                                contribution = ((value / totalWithBonus) * item.kpi).toFixed(2);
                            }

                            return `${context.dataset.label}: ${value} (${contribution}% of KPI)`;
                        },
                        footer: function(context) {
                            const index = context[0].dataIndex;
                            const item = kpiData[index];
                            return 'KPI: ' + item.kpi + '%';
                        }
                    }
                },
                datalabels: {
                    color: 'white',
                    anchor: 'center',
                    align: 'center',
                    formatter: function(value) {
                        return value.toString(); // 0 va manfiylar ham ko‘rinadi
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
</script>
@endsection

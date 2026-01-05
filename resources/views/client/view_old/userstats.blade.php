@extends('layouts.client')

@section('content')

    <button id="toggleChart" class="btn btn-outline-light mt-3 mb-2">
        <i class="fas fa-link"></i> Siz bilan bir xil positiondagi xodimlar
    </button>
     <div class="d-flex justify-content-center">
        <h3 id="chartTitle" class="text-white mt-2 mb-4">Barcha xodimlar (KPI grafik)</h3>
     </div>
    <canvas id="allUsersChart" width="100" height="150"></canvas>
    <canvas id="samePositionChart" width="100" height="150" style="display: none;"></canvas>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    const tasks = @json($tasks);
    const taskIds = tasks.map(t => t.id);
    const taskNames = tasks.map(t => t.taskName);
    const taskColors = [
        '#FF6633', '#FF33FF', '#00B3E6', '#1AB399', '#3366E6',
        '#B34D4D', '#809900', '#FF1A66', '#66E64D', '#4DB3FF',
        '#E6B333', '#E666FF', '#6680B3', '#FF4D4D', '#99E6E6',
        '#6666FF', '#FFB399', '#00FF99', '#FF9966', '#CCFF1A'
    ];

    // Backend'dan tayyor ma'lumotlar - faqat Texniksiz
    const allUsers = @json($filteredStaffUsers);
    const sameUsers = @json($filteredSamePositionUsers);
    const assignments = @json($assignments);
    const currentUserId = @json(auth()->id());

    let isSamePositionView = false;
    let positionName = "{{ auth()->user()->position }}";

    console.log('All Users:', allUsers);
    console.log('Same position users:', sameUsers);

    function buildChartData(users) {
        if (!users || users.length === 0) {
            return { labels: [], datasets: [], sortedUsers: [] };
        }

        const userScores = users.map(user => {
            let total = 0;
            const userAssign = assignments[user.id];
            if (userAssign && userAssign.tasks) {
                userAssign.tasks.forEach(task => {
                    total += task.rating;
                });
            }

            const bonus = userAssign && userAssign.bonus && userAssign.bonus > 0 ? userAssign.bonus : 0;
            total += bonus;

            return { ...user, total, bonus };
        });

        userScores.sort((a, b) => b.total - a.total);
        const labels = userScores.map(user => `${user.lastName} ${user.firstName}`);

        const dataMatrix = userScores.map(user => {
            const data = Array(taskIds.length).fill(0);
            const userAssign = assignments[user.id];

            if (userAssign && userAssign.tasks) {
                userAssign.tasks.forEach(task => {
                    const index = taskIds.indexOf(task.task_id);
                    if (index !== -1) data[index] += task.rating;
                });
            }

            return [...data, user.bonus > 0 ? user.bonus : 0];
        });

        const datasets = taskIds.map((taskId, i) => ({
            label: taskNames[i],
            data: dataMatrix.map(row => row[i]),
            backgroundColor: taskColors[i % taskColors.length],
            borderWidth: 1,
            datalabels: {
                color: 'white',
                anchor: 'center',
                align: 'center',
                formatter: (value) => value !== 0 ? value.toFixed(2) : '',
                font: { weight: 'bold' }
            }
        }));

        const hasPositiveBonus = dataMatrix.some(row => row[taskIds.length] > 0);
        if (hasPositiveBonus) {
            datasets.push({
                label: 'Bonus',
                data: dataMatrix.map(row => row[taskIds.length]),
                backgroundColor: '#198754',
                borderWidth: 1,
                datalabels: {
                    color: 'white',
                    anchor: 'center',
                    align: 'center',
                    formatter: (value) => value !== 0 ? value.toFixed(2) : '',
                    font: { weight: 'bold' }
                }
            });
        }

        return { labels, datasets, sortedUsers: userScores };
    }

    function tickColorCallback(users, currentUserId) {
        return function(context) {
            const index = context.index;
            const userId = users[index]?.id;
            return userId === currentUserId ? 'red' : 'white';
        };
    }

    // Initial chartlarni yaratish
    const allChart = buildChartData(allUsers);
    const sameChart = buildChartData(sameUsers);

    const commonChartOptions = (sortedUsers) => ({
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { color: 'white', font: { size: 14 } }   
            },
            datalabels: {
                color: 'white',
                font: { weight: 'bold' },
                anchor: 'center',
                align: 'center',
                formatter: (value) => value !== 0 ? value.toFixed(2) : ''
            }
        },
        scales: {
            x: {
                stacked: true,
                beginAtZero: true,
                title: { display: true, text: 'Ballar', color: 'white' },
                ticks: { precision: 0, color: 'white' }
            },
            y: {
                stacked: true,
                ticks: {
                    precision: 0,
                    color: tickColorCallback(sortedUsers, currentUserId),
                    font: { weight: 'bold', size: 14 }
                }
            }
        }
    });

    const allUsersChart = new Chart(document.getElementById('allUsersChart'), {
        type: 'bar',
        data: allChart,
        options: commonChartOptions(allChart.sortedUsers),
        plugins: [ChartDataLabels]
    });

    const samePositionChart = new Chart(document.getElementById('samePositionChart'), {
        type: 'bar',
        data: sameChart,
        options: commonChartOptions(sameChart.sortedUsers),
        plugins: [ChartDataLabels]
    });

    // Toggle Position button
    document.getElementById('toggleChart').addEventListener('click', function () {
        const allUsersChartCanvas = document.getElementById('allUsersChart');
        const samePositionChartCanvas = document.getElementById('samePositionChart');
        const toggleButton = this;
        const chartTitle = document.getElementById('chartTitle');

        if (isSamePositionView) {
            allUsersChartCanvas.style.display = 'block';
            samePositionChartCanvas.style.display = 'none';
            toggleButton.innerHTML = '<i class="fas fa-link"></i> Siz bilan bir xil positiondagi xodimlar';
            chartTitle.textContent = 'Barcha xodimlar (KPI grafik)';
        } else {
            allUsersChartCanvas.style.display = 'none';
            samePositionChartCanvas.style.display = 'block';
            toggleButton.innerHTML = '<i class="fas fa-users"></i> Barcha xodimlar';
            chartTitle.textContent = `Siz bilan bir xil positiondagi xodimlar (${positionName})`;
        }

        isSamePositionView = !isSamePositionView;
    });
</script>
@endsection
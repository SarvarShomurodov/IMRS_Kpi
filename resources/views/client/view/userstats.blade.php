@extends('layouts.client')

@section('content')

<!-- Chart Controls -->
<div class="chart-controls-wrapper">
    <button id="toggleChart" class="chart-toggle-btn">
        <i class="fas fa-link"></i>
        <span>Siz bilan bir xil positiondagi xodimlar</span>
    </button>
</div>

<!-- Chart Title -->
<div class="chart-title-wrapper">
    <h3 id="chartTitle" class="chart-main-title">
        <i class="fas fa-chart-bar"></i>
        Barcha xodimlar (KPI grafik)
    </h3>
</div>

<!-- Charts Container -->
<div class="charts-container">
    <div class="chart-box" id="allUsersChartBox">
        <canvas id="allUsersChart"></canvas>
    </div>
    <div class="chart-box" id="samePositionChartBox" style="display: none;">
        <canvas id="samePositionChart"></canvas>
    </div>
</div>

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

    const allUsers = @json($filteredStaffUsers);
    const sameUsers = @json($filteredSamePositionUsers);
    const assignments = @json($assignments);
    const currentUserId = @json(auth()->id());

    let isSamePositionView = false;
    let positionName = "{{ auth()->user()->position }}";
    let allUsersChartInstance = null;
    let samePositionChartInstance = null;

    // ✅ Ma'lumotlarni tayyorlash funksiyasi
    function buildChartData(users) {
        if (!users || users.length === 0) {
            return { labels: [], datasets: [], sortedUsers: [] };
        }

        const userScores = users.map(user => {
            let total = 0;
            const userAssign = assignments[user.id];
            
            if (userAssign && userAssign.tasks) {
                userAssign.tasks.forEach(task => {
                    total += task.rating || 0;
                });
            }

            const bonus = userAssign && userAssign.bonus && userAssign.bonus > 0 ? userAssign.bonus : 0;
            total += bonus;

            return { 
                ...user, 
                total, 
                bonus,
                is_deleted: user.is_deleted || false
            };
        });

        userScores.sort((a, b) => b.total - a.total);
        
        const labels = userScores.map(user => {
            let userName = `${user.lastName} ${user.firstName}`;
            if (user.is_deleted) {
                userName += ' ⓧ';
            }
            return userName;
        });

        const dataMatrix = userScores.map(user => {
            const data = Array(taskIds.length).fill(0);
            const userAssign = assignments[user.id];

            if (userAssign && userAssign.tasks && Array.isArray(userAssign.tasks)) {
                userAssign.tasks.forEach(task => {
                    const index = taskIds.indexOf(task.task_id);
                    if (index !== -1) {
                        data[index] += task.rating || 0;
                    }
                });
            }

            return [...data, user.bonus > 0 ? user.bonus : 0];
        });

        const datasets = taskIds.map((taskId, i) => ({
            label: taskNames[i],
            data: dataMatrix.map(row => row[i]),
            backgroundColor: taskColors[i % taskColors.length],
            borderWidth: 0,
            borderRadius: 4,
            datalabels: {
                color: 'white',
                anchor: 'center',
                align: 'center',
                formatter: (value) => value !== 0 ? value.toFixed(2) : '',
                font: { weight: 'bold', size: 11 }
            }
        }));

        const hasPositiveBonus = dataMatrix.some(row => row[taskIds.length] > 0);
        if (hasPositiveBonus) {
            datasets.push({
                label: 'Bonus',
                data: dataMatrix.map(row => row[taskIds.length]),
                backgroundColor: '#198754',
                borderWidth: 0,
                borderRadius: 4,
                datalabels: {
                    color: 'white',
                    anchor: 'center',
                    align: 'center',
                    formatter: (value) => value !== 0 ? value.toFixed(2) : '',
                    font: { weight: 'bold', size: 11 }
                }
            });
        }

        return { labels, datasets, sortedUsers: userScores };
    }

    // ✅ Tick rangini aniqlash
    function tickColorCallback(users, currentUserId) {
        return function(context) {
            const isDarkMode = document.body.classList.contains('dark-mode') || 
                             document.documentElement.classList.contains('dark-mode');
            const index = context.index;
            const user = users[index];
            
            if (user && user.id === currentUserId) {
                return '#ff0000';
            }
            
            if (user && user.is_deleted) {
                return isDarkMode ? '#666666' : '#999999';
            }
            
            return isDarkMode ? '#e0e0e0' : '#495057';
        };
    }

    // ✅ Chart options - MUHIM O'ZGARISH
    const getChartOptions = (sortedUsers) => {
        const isDarkMode = document.body.classList.contains('dark-mode') || 
                         document.documentElement.classList.contains('dark-mode');
        const textColor = isDarkMode ? '#e0e0e0' : '#495057';
        const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        
        return {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            
            // ✅ ASOSIY O'ZGARISH - categoryPercentage va barPercentage
            categoryPercentage: 0.85, // Kategoriyalar orasidagi joy
            barPercentage: 0.8,        // Bar kengligi
            
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { 
                        color: textColor,
                        font: { size: 13, weight: '500' },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }   
                },
                datalabels: {
                    color: 'white',
                    font: { weight: 'bold', size: 11 },
                    anchor: 'center',
                    align: 'center',
                    formatter: (value) => value !== 0 ? value.toFixed(2) : ''
                },
                tooltip: {
                    backgroundColor: isDarkMode ? 'rgba(0, 0, 0, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                    titleColor: isDarkMode ? '#fff' : '#000',
                    bodyColor: isDarkMode ? '#fff' : '#000',
                    borderColor: isDarkMode ? 'rgba(255, 255, 255, 0.2)' : 'rgba(0, 0, 0, 0.2)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        title: function(context) {
                            const index = context[0].dataIndex;
                            const user = sortedUsers[index];
                            if (user && user.is_deleted) {
                                return context[0].label + ' (O\'chirilgan)';
                            }
                            return context[0].label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    title: { 
                        display: true, 
                        text: 'Ballar', 
                        color: textColor,
                        font: { size: 14, weight: 'bold' }
                    },
                    ticks: { 
                        precision: 0, 
                        color: textColor,
                        font: { size: 12 }
                    },
                    grid: {
                        color: gridColor
                    }
                },
                y: {
                    stacked: true,
                    ticks: {
                        precision: 0,
                        color: tickColorCallback(sortedUsers, currentUserId),
                        font: { weight: 'bold', size: 13 }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        };
    };

    // ✅ Chart ma'lumotlarini yaratish
    const allChart = buildChartData(allUsers);
    const sameChart = buildChartData(sameUsers);

    // ✅ All Users Chart yaratish
    if (allChart.labels.length === 0) {
        document.getElementById('allUsersChartBox').innerHTML = 
            '<div class="no-data-message">Ma\'lumot topilmadi</div>';
    } else {
        // ✅ Container balandligini dinamik o'rnatish
        const allUsersContainer = document.getElementById('allUsersChartBox');
        const allBarCount = allChart.labels.length;
        const calculatedHeight = (allBarCount * 40) + 200; // 40px/bar + 200px legend/padding
        allUsersContainer.style.height = calculatedHeight + 'px';
        
        allUsersChartInstance = new Chart(document.getElementById('allUsersChart'), {
            type: 'bar',
            data: allChart,
            options: getChartOptions(allChart.sortedUsers),
            plugins: [ChartDataLabels]
        });
    }

    // ✅ Same Position Chart yaratish
    if (sameChart.labels.length === 0) {
        document.getElementById('samePositionChartBox').innerHTML = 
            '<div class="no-data-message">Ma\'lumot topilmadi</div>';
    } else {
        // ✅ Container balandligini dinamik o'rnatish
        const sameContainer = document.getElementById('samePositionChartBox');
        const sameBarCount = sameChart.labels.length;
        const calculatedHeight = (sameBarCount * 40) + 200; // 40px/bar + 200px legend/padding
        sameContainer.style.height = calculatedHeight + 'px';
        
        samePositionChartInstance = new Chart(document.getElementById('samePositionChart'), {
            type: 'bar',
            data: sameChart,
            options: getChartOptions(sameChart.sortedUsers),
            plugins: [ChartDataLabels]
        });
    }

    // ✅ Dark mode toggle
    function updateChartsForDarkMode() {
        if (allUsersChartInstance) {
            allUsersChartInstance.options = getChartOptions(allChart.sortedUsers);
            allUsersChartInstance.update();
        }
        if (samePositionChartInstance) {
            samePositionChartInstance.options = getChartOptions(sameChart.sortedUsers);
            samePositionChartInstance.update();
        }
    }

    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            setTimeout(updateChartsForDarkMode, 100);
        });
    }

    setTimeout(updateChartsForDarkMode, 50);

    // ✅ Toggle button
    document.getElementById('toggleChart').addEventListener('click', function () {
        const allUsersChartBox = document.getElementById('allUsersChartBox');
        const samePositionChartBox = document.getElementById('samePositionChartBox');
        const toggleButton = this;
        const chartTitle = document.getElementById('chartTitle');

        if (isSamePositionView) {
            allUsersChartBox.style.display = 'block';
            samePositionChartBox.style.display = 'none';
            toggleButton.innerHTML = '<i class="fas fa-link"></i><span>Siz bilan bir xil positiondagi xodimlar</span>';
            chartTitle.innerHTML = '<i class="fas fa-chart-bar"></i> Barcha xodimlar (KPI grafik)';
        } else {
            allUsersChartBox.style.display = 'none';
            samePositionChartBox.style.display = 'block';
            toggleButton.innerHTML = '<i class="fas fa-users"></i><span>Barcha xodimlar</span>';
            chartTitle.innerHTML = `<i class="fas fa-chart-bar"></i> Siz bilan bir xil positiondagi xodimlar (${positionName})`;
        }

        isSamePositionView = !isSamePositionView;
    });
</script>
@endsection
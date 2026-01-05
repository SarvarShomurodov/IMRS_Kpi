@extends('layouts.admin')

@section('content')
    <form method="GET" action="{{ route('grafik') }}" class="row justify-content-start mb-5">
        <div class="col-12 col-md-2 mb-2">
            <select name="position" class="form-select text-black">
                <option value="all" {{ request('position') == 'all' ? 'selected' : '' }}>Hammasi</option>
                @foreach ($positions as $pos)
                    <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>
                        {{ $pos }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Oylik tanlash -->
        <div class="col-12 col-md-2 mb-2">
            <select name="month" class="form-select text-black">
                <option value="">-- Oy bo'yicha filter --</option>
                @foreach ($months as $month)
                    <option value="{{ $month['value'] }}" {{ $selectedMonth == $month['value'] ? 'selected' : '' }}>
                        {{ $month['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Qo'lda sana kiritish -->
        <div class="col-12 col-md-2 mb-2">
            <input type="date" name="from_date" value="{{ request('from_date', $oneMonthAgo) }}" class="form-control"
                placeholder="Boshlanish">
        </div>

        <div class="col-12 col-md-2 mb-2">
            <input type="date" name="to_date" value="{{ request('to_date', $today) }}" class="form-control"
                placeholder="Tugash">
        </div>

        <div class="col-12 col-md-auto mb-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <h4 class="text-center mt-5 mb-4">
        Xodimlarning
        {{ $from ? \Carbon\Carbon::parse($from)->format('d-m-Y') : \Carbon\Carbon::parse($oneMonthAgo)->format('d-m-Y') }}
        dan {{ $to ? \Carbon\Carbon::parse($to)->format('d-m-Y') : \Carbon\Carbon::parse($today)->format('d-m-Y') }} gacha
        ishlagan KPI natijalari
    </h4>

    <!-- Yuklab olish tugmasi -->
    <div class="text-start mb-4">
        <button id="downloadChart" class="btn btn-success">Grafikni yuklab olish (png)</button>
    </div>

    <!-- Chart -->
    <canvas id="kpiChart" width="100" height="150"></canvas>
    <!-- Ma'lumot haqida ogohlantirish -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        Bu grafikda faqat tanlangan davr boshlanishidan oldin qo'shilgan xodimlar ko'rsatiladi. O'chirilgan xodimlarning
        davr ichidagi natijalari saqlanib turadi.
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    @php
        $staffUsersForChart = $staffUsers->map(function ($user) use ($from, $to, $assignments) {
            $assignment = $assignments[$user->id] ?? null;

            return [
                'id' => $user->id,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'roles' => $user->getRoleNames(),
                'type' => $user->type,
                'deleted_at' => $user->deleted_at,
                'is_deleted' => $assignment['is_deleted'] ?? false,
                'is_deleted_in_period' =>
                    $user->deleted_at &&
                    \Carbon\Carbon::parse($user->deleted_at)->between(
                        \Carbon\Carbon::parse($from),
                        \Carbon\Carbon::parse($to),
                    ),
            ];
        });
    @endphp

    <script>
        const rawUsers = @json($staffUsersForChart);
        const assignments = @json($assignments);
        const tasks = @json($tasks);
        const fromDate = @json($from);
        const toDate = @json($to);

        console.log('🔍 Debug Info:');
        console.log('rawUsers:', rawUsers);
        console.log('assignments:', assignments);
        console.log('tasks:', tasks);

        // ✅ Xavfsizlik tekshiruvi
        if (!tasks || tasks.length === 0) {
            console.error('❌ Tasks bo\'sh!');
            document.getElementById('kpiChart').style.display = 'none';
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning';
            alert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Tasklar topilmadi!';
            document.querySelector('.alert-info').after(alert);
        }

        if (!rawUsers || rawUsers.length === 0) {
            console.error('❌ Users bo\'sh!');
            document.getElementById('kpiChart').style.display = 'none';
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning';
            alert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Xodimlar topilmadi!';
            document.querySelector('.alert-info').after(alert);
        }

        const taskNames = tasks.map(t => t.taskName);
        const taskIds = tasks.map(t => t.id);
        const taskColors = [
            '#FF6633', '#FF33FF', '#00B3E6', '#1AB399', '#3366E6',
            '#B34D4D', '#809900', '#FF1A66', '#66E64D', '#4DB3FF',
            '#E6B333', '#E666FF', '#6680B3', '#FF4D4D', '#99E6E6',
            '#6666FF', '#FFB399', '#00FF99', '#FF9966', '#CCFF1A'
        ];

        const bonusColor = '#2ecc71';

        const staffWithData = rawUsers.map(user => {
            const data = Array(taskIds.length).fill(0);
            const userAssign = assignments[user.id];
            let bonus = 0;

            if (userAssign && userAssign.tasks) {
                userAssign.tasks.forEach(task => {
                    const index = taskIds.indexOf(task.task_id);
                    if (index !== -1) {
                        // ✅ Xavfsizlik - rating number ekanligini tekshirish
                        const rating = parseFloat(task.rating) || 0;
                        data[index] += rating;
                    }
                });
                // ✅ Xavfsizlik - bonus number ekanligini tekshirish
                bonus = parseFloat(userAssign.bonus) || 0;
            }

            const bonusForChart = bonus > 0 ? bonus : 0;
            const total = [...data, bonusForChart].reduce((sum, val) => sum + val, 0);

            // User nomi
            let userName = `${user.lastName} ${user.firstName}`;
            if (user.is_deleted) {
                userName += ' 🚫';
            }

            return {
                user: user,
                userName: userName,
                data: [...data, bonusForChart],
                total: total,
                isDeleted: user.is_deleted,
                isDeletedInPeriod: user.is_deleted_in_period
            };
        });

        console.log('📊 staffWithData:', staffWithData);

        // Total bo'yicha saralash
        const sortedStaff = staffWithData.sort((a, b) => b.total - a.total);

        const labels = sortedStaff.map(item => item.userName);
        const staffData = sortedStaff.map(item => item.data);
        const totalRatings = sortedStaff.map(item => item.total);

        console.log('📈 Chart data:');
        console.log('labels:', labels);
        console.log('staffData:', staffData);
        console.log('totalRatings:', totalRatings);

        // Datasets
        const datasets = taskIds.map((taskId, i) => ({
            label: taskNames[i],
            data: staffData.map(row => row[i]),
            backgroundColor: taskColors[i % taskColors.length],
            borderWidth: 1,
            datalabels: {
                color: function(context) {
                    const value = context.dataset.data[context.dataIndex];
                    return value < 0 ? 'black' : 'white';
                },
                anchor: function(context) {
                    const value = context.dataset.data[context.dataIndex];
                    return value < 0 ? 'end' : 'center';
                },
                align: function(context) {
                    const value = context.dataset.data[context.dataIndex];
                    return value < 0 ? 'start' : 'center';
                },
                // ✅ TUZATILDI - xavfsiz formatter
                formatter: (value) => {
                    if (value === null || value === undefined || value === 0) return '';
                    const num = parseFloat(value);
                    return isNaN(num) ? '' : num.toFixed(2);
                },
                font: {
                    weight: 'bold'
                }
            }
        }));

        // Bonus dataset
        datasets.push({
            label: 'Bonus',
            data: staffData.map(row => row[taskIds.length]),
            backgroundColor: bonusColor,
            borderWidth: 1,
            datalabels: {
                color: '#000',
                anchor: 'center',
                align: 'center',
                // ✅ TUZATILDI - xavfsiz formatter
                formatter: function(value) {
                    if (value === null || value === undefined || value <= 0) return '';
                    const num = parseFloat(value);
                    return isNaN(num) ? '' : num.toFixed(2);
                },
                font: {
                    weight: 'bold'
                }
            }
        });

        console.log('📦 Datasets:', datasets);

        // Chart
        try {
            const chart = new Chart(document.getElementById('kpiChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            max: Math.max(...totalRatings, 10) + 10,
                            title: {
                                display: true,
                                text: 'Ballar'
                            }
                        },
                        y: {
                            stacked: true,
                            ticks: {
                                autoSkip: false,
                                font: {
                                    weight: 'bold',
                                    size: 14
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            formatter: Math.round,
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });

            console.log('✅ Chart muvaffaqiyatli yaratildi');

            // Download
            document.getElementById('downloadChart').addEventListener('click', function() {
                const link = document.createElement('a');
                link.download = `kpi_chart_${fromDate}_${toDate}.png`;
                link.href = document.getElementById('kpiChart').toDataURL();
                link.click();
            });
        } catch (error) {
            console.error('❌ Chart yaratishda xatolik:', error);
            document.getElementById('kpiChart').style.display = 'none';
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger';
            alert.innerHTML = '<i class="fas fa-times-circle"></i> Grafikni yaratishda xatolik: ' + error.message;
            document.querySelector('.alert-info').after(alert);
        }
    </script>
@endsection

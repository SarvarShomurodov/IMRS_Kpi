<nav class="col-md-2 d-none d-md-block custom-sidebar">
    <div class="sidebar-content">
        <!-- Logo Section -->
        <div class="logo-section text-center">
            <img class="sidebar-logo" src="{{ asset('admin/images/screen.png') }}" alt="logo" />
        </div>

        <!-- User Info Card -->
        <div class="user-info-card">
            <h5 class="user-title">
                <i class="fas fa-users"></i> Xodimlar uchun
            </h5>
            <div class="user-details">
                <div class="user-detail-item">
                    <i class="fas fa-envelope"></i>
                    <div class="detail-content">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">{{ auth()->user()->email }}</span>
                    </div>
                </div>
                <div class="user-detail-item">
                    <i class="fas fa-id-badge"></i>
                    <div class="detail-content">
                        <span class="detail-label">ID</span>
                        <span class="detail-value">{{ auth()->user()->id }}</span>
                    </div>
                </div>
            </div>

            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button class="btn-logout" type="submit">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Chiqish</span>
                </button>
            </form>
        </div>

        @unless(auth()->user()->hasRole('Texnik'))
        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="filter-title">
                <i class="fas fa-filter"></i> Vaqt filtri
            </h5>

            @php
                $year = request('year');
                $month = request('month');
                
                // ✅ show_all parametrini ham qo'shamiz
                $routes = [
                    'client.index' => route('client.index', array_filter(['year' => $year, 'month' => $month])),
                    'client.subtask' => route('client.subtask', array_filter(['year' => $year, 'month' => $month])),
                    'client.allsubtask' => route('client.allsubtask', array_filter(['year' => $year, 'month' => $month])),
                    'assignments.index' => route('assignments.index', array_filter(['year' => $year, 'month' => $month])),
                ];
            @endphp

            <form action="{{ $routes[Route::currentRouteName()] ?? '#' }}" method="GET" class="filter-form">
                <div class="form-group-custom">
                    <label for="yearSelect" class="custom-label">
                        <i class="fas fa-calendar-alt"></i> Yil
                    </label>
                    <select class="form-select-custom" id="yearSelect" name="year">
                        <option value="">Tanlang</option>
                        <option value="2026" {{ request('year') == '2026' ? 'selected' : '' }}>2026</option>
                        <option value="2025" {{ request('year') == '2025' ? 'selected' : '' }}>2025</option>
                        <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
                        <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
                    </select>
                </div>

                <div class="form-group-custom">
                    <label for="monthSelect" class="custom-label">
                        <i class="fas fa-calendar-day"></i> Oy
                    </label>
                    <select class="form-select-custom" id="monthSelect" name="month">
                        <option value="">Tanlang</option>
                        @foreach ([
                            '01' => 'Yanvar', '02' => 'Fevral', '03' => 'Mart', '04' => 'Aprel',
                            '05' => 'May', '06' => 'Iyun', '07' => 'Iyul', '08' => 'Avgust',
                            '09' => 'Sentabr', '10' => 'Oktabr', '11' => 'Noyabr', '12' => 'Dekabr'
                        ] as $num => $name)
                            <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ✅ show_all parametrini OLIB TASHLAYMIZ filtrlashda --}}
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Filtrlash
                </button>
            </form>
            
            {{-- ✅ Agar filtr qo'llanilgan bo'lsa - tozalash tugmasi --}}
            @if(request('year') || request('month'))
                <a href="{{ $routes[Route::currentRouteName()] ?? route('assignments.index') }}" 
                   class="btn-filter mt-2" 
                   style="background: #6c757d; text-align: center; display: block; text-decoration: none;">
                    <i class="fas fa-times"></i> Filtrni tozalash
                </a>
            @endif
        </div>
        @endunless
    </div>
</nav>
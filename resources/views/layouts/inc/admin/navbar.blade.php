<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="">
                <img src="{{ asset('admin/images/screen.png') }}" alt="logo" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="">
                <img src="{{ asset('admin/images/screen.png') }}" alt="logo" />
            </a>
        </div>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-navv">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h2 class="welcome-text">Saytda hozir, <span
                        class="text-black fw-bold">{{ auth()->user()->firstName }}</span></h2>
                @if (auth()->user() && auth()->user()->hasRole('Super Admin'))
                    <h5 class="welcome-sub-text">IMRS xodimlari <strong>KPI</strong>ni xisoblash sayti <b>Super
                            Admin</b> paneli</h5>
                @else
                    <h5 class="welcome-sub-text">IMRS xodimlari <strong>KPI</strong>ni xisoblash sayti <b>Admin</b>
                        paneli</h5>
                @endif
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
            </li>
            <li class="nav-item">
                <form class="search-form" action="#">
                    <i class="icon-search"></i>
                    <input type="search" class="form-control" placeholder="Search Here" title="Search here">
                </form>
            </li>
            <li class="nav-item dropdown notification-dropdown">
    <a class="nav-link count-indicator position-relative" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
        <i class="icon-bell fs-4"></i>
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp
        @if($unreadCount > 0)
            <span class="count-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </a>
    
    <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list notification-dropdown-menu" 
         aria-labelledby="notificationDropdown">
        
        <!-- Header -->
        <div class="notification-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold text-dark">Bildirishnomalar</h5>
                    <p class="mb-0 text-muted small">Oxirgi 5 ta o'zgarish</p>
                </div>
                @if($unreadCount > 0)
                    <span class="badge bg-danger rounded-pill px-3 py-2">{{ $unreadCount }} yangi</span>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        <div class="notification-body">
            @php
                $notifications = auth()->user()->notifications()->latest()->take(5)->get();
            @endphp
            
            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.index') }}" 
                   class="dropdown-item notification-item {{ !$notification->read_at ? 'unread' : '' }}">
                    <div class="notification-icon-wrapper">
                        <div class="notification-icon {{ !$notification->read_at ? 'icon-primary' : 'icon-secondary' }}">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title mb-1 {{ !$notification->read_at ? 'fw-semibold' : '' }}">
                            {{ Str::limit($notification->data['message_short'] ?? $notification->data['message'] ?? 'Yangi xabar', 60) }}
                        </p>
                        <div class="notification-meta">
                            <i class="far fa-clock me-1"></i>
                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @if(!$notification->read_at)
                        <div class="unread-dot"></div>
                    @endif
                </a>
            @empty
                <div class="empty-notification">
                    <i class="fas fa-bell-slash text-muted mb-3"></i>
                    <p class="text-muted mb-0">Bildirishnomalar yo'q</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="notification-footer">
            <a href="{{ route('notifications.index') }}" class="view-all-link">
                <span>Barchasini ko'rish</span>
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</li>
            <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <img class="img-xs rounded-circle" src="{{ asset('admin/images/user.png') }}"
                        alt="Profile image"></a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">

                    <div class="dropdown-header text-center">
                        <img class="img-xs rounded-circle mb-1" src="{{ asset('admin/images/user.png') }}"
                            alt="Profile image"> </a>
                        <p class="mb-1 mt-3 fw-semibold">{{ auth()->user()->firstName }} {{ auth()->user()->lastName }}
                        </p>
                        <p class="fw-light text-muted mb-0">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="dropdown-item" type="submit"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>{{ __('Log Out') }}
                        </a>
                    </form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
    <!-- Masalan: resources/views/admin/dashboard.blade.php -->

</nav>

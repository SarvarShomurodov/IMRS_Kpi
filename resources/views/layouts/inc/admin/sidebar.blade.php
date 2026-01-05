<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        {{-- Super Admin --}}
        @if (auth()->user() && auth()->user()->hasRole('Super Admin'))
            <li class="nav-item nav-category">Akkauntlar</li>
            <li class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                @canany(['create-user', 'edit-user', 'delete-user'])
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="menu-icon mdi mdi-file-document"></i>
                    <span class="menu-title">Xodimlar ro'yxati</span>
                </a>
                @endcanany 
            </li>
            <li class="nav-item {{ request()->routeIs('admin.projects*') ? 'active' : '' }}">
                @canany(['create-project', 'edit-project', 'delete-project'])
                <a class="nav-link" href="{{ route('admin.projects.index') }}">
                    <i class="menu-icon mdi mdi-folder-open"></i>
                    <span class="menu-title">Loyihalar</span>
                </a>
                @endcanany
            </li>
            <li class="nav-item nav-category">Avtorizatsiya</li>
            <li class="nav-item {{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                @canany(['create-role', 'edit-role', 'delete-role'])
                <a class="nav-link" href="{{ route('admin.roles.index') }}">
                    <i class="menu-icon mdi mdi-folder-open"></i>
                    <span class="menu-title">Rollar</span>
                </a>
                @endcanany
            </li>
            <li class="nav-item nav-category">Tasklar</li>
            <li class="nav-item {{ request()->routeIs('admin.tasks*') ? 'active' : '' }}">
                @canany(['create-task', 'edit-task', 'delete-task'])
                <a class="nav-link" href="{{ route('admin.tasks.index') }}">
                    <i class="menu-icon mdi mdi-format-list-checkbox"></i>
                    <span class="menu-title">Tasks</span>
                </a>
                @endcanany
            </li>
            <li class="nav-item {{ request()->routeIs('admin.subtasks*') ? 'active' : '' }}">
                @canany(['create-subtask', 'edit-subtask', 'delete-subtask'])
                <a class="nav-link" href="{{ route('admin.subtasks.index') }}">
                    <i class="menu-icon mdi mdi-format-list-checkbox"></i>
                    <span class="menu-title">Sub Tasks</span>
                </a>
                @endcanany
            </li>
            <li class="nav-item {{ request()->routeIs('admin.task_assignments*') ? 'active' : '' }}">
                @canany(['create-subtask', 'edit-subtask', 'delete-subtask'])
                <a class="nav-link" href="{{ route('admin.task_assignments.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Task Assignees</span>
                </a>
                @endcanany
            </li>
        @endif
        {{-- Admin --}}
        @if (auth()->user() && auth()->user()->hasRole('Admin'))
            <li class="nav-item nav-category">Sahifalar</li>
            <li class="nav-item {{ request()->routeIs('grafik') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('grafik') }}">
                    <i class="menu-icon mdi mdi-chart-line"></i>
                    <span class="menu-title">Asosiy sahifa</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('task.swod') || request()->routeIs('client-task.show') || request()->routeIs('client-task.task-details') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('task.swod') }}">
                    <i class="menu-icon mdi mdi-chart-pie"></i>
                    <span class="menu-title">SWOD tahlil</span>
                </a>
            </li>
            <li
                class="nav-item {{ request()->routeIs('accounts.staffs') || request()->routeIs('assignment.user') || request()->routeIs('assignment.user.penalties') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('accounts.staffs') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Ilmiy xodimlar ro'yxati</span>
                </a>
            </li>
            {{-- @canany(['create-task', 'edit-task', 'delete-task'])
            <a class="menyu-link {{ Request::is('tasks') || Request::is('tasks/*') ? 'faol-link' : '' }}" href="{{ route('tasks.index') }}">
              <i class="fas fa-clipboard-check menyu-ikon"></i>
              KPI baholash
            </a>
          @endcanany --}}
            <li class="nav-item nav-category">Texnik xodimlar</li>
            <li class="nav-item {{ request()->routeIs('admin.attendance.index') || request()->routeIs('admin.attendance.show') || request()->routeIs('admin.reports.index') || request()->routeIs('admin.reports.show') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.attendance.index') }}">
                    <i class="menu-icon mdi mdi-account-cog"></i>
                    <span class="menu-title">Texnik xodimlar ro'yxati</span>
                </a>
            </li>
            <li class="nav-item nav-category">Yordam</li>
            <li class="nav-item">
                <a class="nav-link" href="https://t.me/SARVAR0297">
                    <i class="menu-icon mdi mdi-send"></i>
                    <span class="menu-title">Qo'llab-quvvatlash</span>
                </a>
            </li>
        @endif
    </ul>
</nav>

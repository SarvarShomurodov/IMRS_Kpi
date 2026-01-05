@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Foydalanuvchini tahrirlash</h2>
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">&larr; Orqaga</a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="col-md-6">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="userEditForm">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="firstName" class="form-label">First Name:</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" value="{{ old('firstName', $user->firstName) }}">
                </div>
                
                <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name:</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" value="{{ old('lastName', $user->lastName) }}">
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address:</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}">
                    @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone number:</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                </div>

                <div class="mb-3">
                    <label for="position" class="form-label">Position:</label>
                    <select class="form-select" id="position" name="position">
                        <option value="Yetakchi mutaxasis" {{ $user->position == 'Yetakchi mutaxasis' ? 'selected' : '' }}>Yetakchi mutaxasis</option>
                        <option value="Bosh mutaxasis" {{ $user->position == 'Bosh mutaxasis' ? 'selected' : '' }}>Bosh mutaxasis</option>
                        <option value="Loyiha raxbari" {{ $user->position == 'Loyiha raxbari' ? 'selected' : '' }}>Loyiha raxbari</option>
                        <option value="Bo'lim boshlig'i" {{ $user->position == "Bo'lim boshlig'i" ? 'selected' : '' }}>Bo'lim boshlig'i</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="salary" class="form-label">Salary:</label>
                    <input type="number" step="0.01" class="form-control" id="salary" name="salary" value="{{ old('salary', $user->salary) }}">
                </div>
                
                <div class="mb-3">
                    <label for="lastDate" class="form-label">Last Working Date:</label>
                    <input type="date" class="form-control" id="lastDate" name="lastDate" value="{{ old('lastDate', $user->lastDate) }}">
                </div>
                
                {{-- 🔥 LOYIHA TANLASH --}}
                <div class="mb-3">
                    <label for="project_id" class="form-label">Project:</label>
                    <select class="form-select" name="project_id" id="project_id">
                        <option value="">--------</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" 
                                    data-project-name="{{ $project->name }}"
                                    {{ $user->project_id == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Loyiha o'zgarish tarixi --}}
                    @if($user->project_changed_at && $user->previous_project_id)
                        <div class="alert alert-info mt-2 p-2" style="font-size: 13px;">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Oxirgi o'zgarish:</strong> {{ $user->project_changed_at->format('d.m.Y') }}
                            <br>
                            <small class="text-muted">
                                {{ $user->previousProject->name ?? 'N/A' }} → {{ $user->project->name ?? 'N/A' }}
                            </small>
                        </div>
                    @endif
                </div>

                {{-- 🔥 YANGI: LOYIHA O'ZGARISH SANASINI BOSHQARISH --}}
                <div class="mb-3" id="projectChangeDateDiv" style="display: none;">
                    <div class="card border-warning">
                        <div class="card-body p-3">
                            <label for="project_change_date" class="form-label">
                                <i class="fas fa-calendar-alt text-warning"></i> 
                                Loyihani o'zgartirish sanasini kiriting:
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="project_change_date" 
                                   name="project_change_date" 
                                   value="{{ now()->format('Y-m-d') }}">
                            <small class="text-muted">
                                Bu sana bonus hisoblashda qaysi loyiha ishlatilishini aniqlaydi
                            </small>

                            {{-- Real-time ma'lumot --}}
                            <div id="dateImpactInfo" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="roles" class="form-label">Roles:</label>
                    <select class="form-select" multiple name="roles[]" id="roles">
                        @foreach ($roles as $role)
                            @if ($role != 'Super Admin' || Auth::user()->hasRole('Super Admin'))
                                <option value="{{ $role }}" {{ in_array($role, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>{{ $role }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('roles')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">New Password (Optional):</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="text-muted">Agar parolni o'zgartirmoqchi bo'lsangiz, kiriting.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update User
                </button>
            </form>
        </div>
    </div>
</div>

{{-- 🔥 JAVASCRIPT: Loyiha o'zgarish sanasini boshqarish --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    const changeDateDiv = document.getElementById('projectChangeDateDiv');
    const changeDateInput = document.getElementById('project_change_date');
    const impactInfoDiv = document.getElementById('dateImpactInfo');
    
    const originalProjectId = {{ $user->project_id ?? 'null' }};
    const oldProjectName = "{{ $user->project->name ?? 'N/A' }}";

    // Loyiha o'zgarganda
    projectSelect.addEventListener('change', function() {
        const newProjectId = parseInt(this.value);
        const newProjectName = this.options[this.selectedIndex].getAttribute('data-project-name');

        // Agar loyiha o'zgarmagan yoki tanlangan bo'lmasa
        if (newProjectId === originalProjectId || !newProjectId) {
            changeDateDiv.style.display = 'none';
            return;
        }

        // Loyiha o'zgardi - sana kiritish bo'limini ko'rsatish
        changeDateDiv.style.display = 'block';
        
        // Bugungi sanani default qilib qo'yish
        changeDateInput.value = new Date().toISOString().split('T')[0];
        
        // Impact ma'lumotini yangilash
        updateImpactInfo(newProjectName);
    });

    // Sana o'zgarganda impact ma'lumotini yangilash
    changeDateInput.addEventListener('change', function() {
        const newProjectName = projectSelect.options[projectSelect.selectedIndex].getAttribute('data-project-name');
        updateImpactInfo(newProjectName);
    });

    function updateImpactInfo(newProjectName) {
        const selectedDate = new Date(changeDateInput.value);
        
        // Davr oralig'ini hisoblash (o'zgarish sanasiga qarab)
        const changeDay = selectedDate.getDate();
        const changeMonth = selectedDate.getMonth();
        const changeYear = selectedDate.getFullYear();
        
        let periodStart, periodEnd;
        
        if (changeDay >= 26) {
            // 26-dan keyingi kunlar
            periodStart = new Date(changeYear, changeMonth, 26);
            periodEnd = new Date(changeYear, changeMonth + 1, 25);
        } else {
            // 25-dan oldingi kunlar
            periodStart = new Date(changeYear, changeMonth - 1, 26);
            periodEnd = new Date(changeYear, changeMonth, 25);
        }

        const midpoint = new Date(periodStart.getTime() + (periodEnd.getTime() - periodStart.getTime()) / 2);

        // O'zgarish ta'sirini aniqlash
        let impactMessage = '';
        
        if (selectedDate <= midpoint) {
            impactMessage = `
                <div class="alert alert-success p-2 mt-2" style="font-size: 12px;">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Yangi loyiha bonusi ishlatiladi</strong>
                    <br>
                    <small>
                        Sabab: O'zgarish davr o'rtasidan OLDIN (${formatDate(midpoint)} gacha)
                        <br>
                        <i class="fas fa-arrow-right"></i> 
                        <strong>${newProjectName}</strong> loyihasining bonus koeffitsienti
                    </small>
                </div>
            `;
        } else {
            impactMessage = `
                <div class="alert alert-primary p-2 mt-2" style="font-size: 12px;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Eski loyiha bonusi ishlatiladi</strong>
                    <br>
                    <small>
                        Sabab: O'zgarish davr o'rtasidan KEYIN (${formatDate(midpoint)} dan keyin)
                        <br>
                        <i class="fas fa-arrow-left"></i> 
                        <strong>${oldProjectName}</strong> loyihasining bonus koeffitsienti
                    </small>
                </div>
            `;
        }

        impactInfoDiv.innerHTML = `
            <div class="text-muted" style="font-size: 11px;">
                <strong>Davr:</strong> ${formatDate(periodStart)} - ${formatDate(periodEnd)}<br>
                <strong>O'rta nuqta:</strong> ${formatDate(midpoint)}<br>
                <strong>O'zgarish sanasi:</strong> ${formatDate(selectedDate)}
            </div>
            ${impactMessage}
        `;
    }

    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}.${month}.${year}`;
    }
});
</script>

<style>
#projectChangeDateDiv {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card.border-warning {
    border-width: 2px;
}
</style>
@endsection
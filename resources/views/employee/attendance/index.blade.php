@extends('layouts.employee')

@section('title', 'Mening Davomatim')

@section('content')
<div class="row">
    <!-- Statistikalar -->
    <div class="col-md-12 mb-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center mb-3">
                            <i class="bi bi-calendar-week me-2"></i> 
                            Haftalik Statistika
                            <small class="ms-2" style="opacity: 0.8; font-size: 0.75rem;">(oxirgi 7 kun)</small>
                        </h5>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="mb-2 fw-bold text-danger" style="font-size: 2rem;">{{ \App\Models\Attendance::formatMinutes($weeklyStats['sababsiz_ishda_bolmagan']) }}</h2>
                                    <small class="text-white-50">Ishda bo'lmagan (Sababsiz)</small>
                                    @if($weeklyStats['sababsiz_ishda_bolmagan'] > 0)
                                        <div class="mt-1">
                                            <small class="badge bg-danger bg-opacity-75">{{ $weeklyStats['sababsiz_ishda_bolmagan'] }} daq</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="mb-2 fw-bold text-info" style="font-size: 2rem;">{{ \App\Models\Attendance::formatMinutes($weeklyStats['sababli_ishda_bolmagan']) }}</h2>
                                    <small class="text-white-50">Ishda bo'lmagan (Sababli)</small>
                                    @if($weeklyStats['sababli_ishda_bolmagan'] > 0)
                                        <div class="mt-1">
                                            <small class="badge bg-info bg-opacity-75">{{ $weeklyStats['sababli_ishda_bolmagan'] }} daq</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center mb-3">
                            <i class="bi bi-calendar-month me-2"></i> 
                            Oylik Statistika
                            <small class="ms-2" style="opacity: 0.8; font-size: 0.75rem;">(oxirgi 30 kun)</small>
                        </h5>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="mb-2 fw-bold text-danger" style="font-size: 2rem;">{{ \App\Models\Attendance::formatMinutes($monthlyStats['sababsiz_ishda_bolmagan']) }}</h2>
                                    <small class="text-white-50">Ishda bo'lmagan (Sababsiz)</small>
                                    @if($monthlyStats['sababsiz_ishda_bolmagan'] > 0)
                                        <div class="mt-1">
                                            <small class="badge bg-danger bg-opacity-75">{{ $monthlyStats['sababsiz_ishda_bolmagan'] }} daq</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="mb-2 fw-bold text-info" style="font-size: 2rem;">{{ \App\Models\Attendance::formatMinutes($monthlyStats['sababli_ishda_bolmagan']) }}</h2>
                                    <small class="text-white-50">Ishda bo'lmagan (Sababli)</small>
                                    @if($monthlyStats['sababli_ishda_bolmagan'] > 0)
                                        <div class="mt-1">
                                            <small class="badge bg-info bg-opacity-75">{{ $monthlyStats['sababli_ishda_bolmagan'] }} daq</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Davomat yozuvlari -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 d-flex align-items-center">
                    <i class="bi bi-list-check me-2"></i> 
                    Davomat tarixi
                </h4>
            </div>
            <div class="card-body p-4">
                @if($attendances->count() > 0)
                    @foreach($attendances as $attendance)
                        <div class="attendance-card {{ $attendance->isAbsent() ? 'absent' : ($attendance->morning_late > 0 ? 'late' : '') }}">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-3 d-flex align-items-center">
                                            <i class="bi bi-calendar3 me-2"></i> 
                                            {{ $attendance->formatted_date }}
                                        </h5>
                                        <div class="mt-2">
                                            @if($attendance->isAbsent())
                                                <span class="badge bg-danger badge-custom">
                                                    <i class="bi bi-x-circle me-1"></i> Kelmagan
                                                </span>
                                            @else
                                                <span class="badge bg-success badge-custom">
                                                    <i class="bi bi-check-circle me-1"></i> Ishda bo'lgan
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-9">
                                        @if($attendance->isAbsent())
                                            <!-- Kelmagan kun -->
                                            @if($attendance->morning_comment)
                                                <div class="alert alert-danger mb-0">
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-info-circle me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Kun izohi:</strong>
                                                            <div class="mt-1">{{ $attendance->morning_comment }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-muted d-flex align-items-center">
                                                    <i class="bi bi-question-circle me-2"></i> 
                                                    <span>Izoh yo'q</span>
                                                </div>
                                            @endif
                                        @else
                                            <!-- Ishda bo'lgan kun -->
                                            <div class="row g-3">
                                                <!-- Ertalab -->
                                            
                                                @if($attendance->morning_late > 479)
                                                    <!-- Agar 479 daqiqadan oshsa, umuman ishga kelmagan deb chiqariladi -->
                                                    <div class="alert alert-danger mb-0">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-info-circle me-2 mt-1"></i>
                                                            <div>
                                                                <strong>Kun izohi:</strong>
                                                                <div class="mt-1">
                                                                    {{ $attendance->morning_comment ?? "Xodim umuman ishga kelmagan" }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($attendance->morning_late > 0)
                                                    <!-- Oddiy kech qolish holati -->
                                                    <div class="col-md-4">
                                                        <div class="p-3 rounded-3 h-100 {{ $attendance->isMorningLateCauseless() ? 'bg-warning bg-opacity-25' : 'bg-info bg-opacity-25' }}">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="bi bi-sunrise me-2 text-warning"></i>
                                                                <strong>Ertalab:</strong>
                                                            </div>
                                                            <div class="fs-5 fw-bold mb-1">{{ $attendance->morning_late }} daqiqa</div>
                                                            @if($attendance->morning_comment)
                                                                <small class="text-muted d-block">({{ $attendance->morning_comment }})</small>
                                                            @else
                                                                <small class="text-warning d-block">(Sababsiz)</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                
                                                <!-- Abet -->
                                                @if($attendance->lunch_duration > 0)
                                                    <div class="col-md-4">
                                                        <div class="p-3 rounded-3 h-100 bg-info bg-opacity-25">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="bi bi-cup-hot me-2 text-info"></i>
                                                                <strong>Abet:</strong>
                                                            </div>
                                                            <div class="fs-5 fw-bold mb-1">{{ $attendance->lunch_duration }} daqiqa</div>
                                                            @if($attendance->lunch_comment)
                                                                <small class="text-muted d-block">({{ $attendance->lunch_comment }})</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <!-- Erta ketish -->
                                                @if($attendance->early_leave > 0)
                                                    <div class="col-md-4">
                                                        <div class="p-3 rounded-3 h-100 bg-secondary bg-opacity-25">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="bi bi-sunset me-2 text-secondary"></i>
                                                                <strong>Erta ketish:</strong>
                                                            </div>
                                                            <div class="fs-5 fw-bold mb-1">{{ $attendance->early_leave }} daqiqa</div>
                                                            @if($attendance->evening_comment)
                                                                <small class="text-muted d-block">({{ $attendance->evening_comment }})</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            @if(!$attendance->morning_late && !$attendance->lunch_duration && !$attendance->early_leave)
                                                <div class="text-success d-flex align-items-center p-3 rounded-3" style="background: linear-gradient(135deg, rgba(51, 214, 159, 0.1), rgba(40, 167, 69, 0.05));">
                                                    <i class="bi bi-check-circle-fill me-2 fs-4"></i> 
                                                    <div>
                                                        <div class="fw-bold">Mukammal ish kuni!</div>
                                                        <small class="text-muted">Hech qanday qoidabuzarlik yo'q</small>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- Vaqt ma'lumotlari -->
                                            <div class="mt-4 p-3 rounded-3" style="background-color: rgba(124, 93, 250, 0.05); border: 1px solid rgba(124, 93, 250, 0.1);">
                                                <div class="d-flex align-items-center text-muted">
                                                    <i class="bi bi-clock me-2"></i> 
                                                    <div class="d-flex flex-wrap gap-3">
                                                        @if($attendance->morning_in)
                                                            <span><strong>Kirish:</strong> {{ $attendance->morning_in->format('H:i') }}</span>
                                                        @endif
                                                        @if($attendance->lunch_out && $attendance->lunch_in)
                                                            <span><strong>Abet:</strong> {{ $attendance->lunch_out->format('H:i') }} - {{ $attendance->lunch_in->format('H:i') }}</span>
                                                        @endif
                                                        @if($attendance->evening_out)
                                                            <span><strong>Chiqish:</strong> {{ $attendance->evening_out->format('H:i') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $attendances->links() }}
                    </div>
                @else
                    <div class="text-center py-5 empty-state">
                        <i class="bi bi-calendar-x" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                        <h4 class="text-muted mb-3">Davomat yozuvlari topilmadi</h4>
                        <p class="text-muted">Admin hali sizning davomatlaringizni kiritmagan</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Additional page-specific styles */
.stats-card .card-body {
    position: relative;
    overflow: hidden;
}

.stats-card .card-body::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    z-index: 1;
}

.attendance-card {
    position: relative;
    overflow: hidden;
}

.attendance-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--success-green) 0%, transparent 100%);
    z-index: 1;
}

.attendance-card.absent::before {
    background: linear-gradient(90deg, var(--danger-red) 0%, transparent 100%);
}

.attendance-card.late::before {
    background: linear-gradient(90deg, var(--warning-orange) 0%, transparent 100%);
}

/* Loading animation for stats */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.stats-card h2 {
    animation: pulse 2s infinite;
}
</style>
@endsection
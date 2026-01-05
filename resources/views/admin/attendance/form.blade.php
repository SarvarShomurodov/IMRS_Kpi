@extends('layouts.admin')

@section('title', 'Davomat kiritish')

@section('content')

<h4 class="mb-0">
    <i class="bi bi-clock-history"></i>
    <b>{{ $user->firstName }} {{ $user->lastName }}</b> uchun davomat kiritish
</h4>
<small class="text-muted">Sana: {{ Carbon\Carbon::parse($date)->format('d/m/Y') }}</small>

<div class="card mt-3">
    <form method="POST" action="{{ route('admin.attendance.store', $user) }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="card-body">
            <!-- Vaqtlar kiritish -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="bi bi-sunrise"></i> Ertalab kirish vaqti
                    </label>
                    <input type="time" 
                           name="morning_in"
                           class="form-control @error('morning_in') is-invalid @enderror"
                           value="{{ old('morning_in', $attendance->morning_in ? $attendance->morning_in->format('H:i') : '') }}"
                           @if(!in_array(auth()->user()->email, config('admin.attendance_edit_emails', []))) readonly @endif>
                    @error('morning_in')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="bi bi-cup-hot"></i> Tushlik chiqish vaqti
                    </label>
                    <input type="time" 
                           name="lunch_out" 
                           class="form-control @error('lunch_out') is-invalid @enderror"
                           value="{{ old('lunch_out', $attendance->lunch_out ? $attendance->lunch_out->format('H:i') : '') }}"
                           @if(!in_array(auth()->user()->email, config('admin.attendance_edit_emails', []))) readonly @endif>
                    @error('lunch_out')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="bi bi-cup-hot"></i> Tushlik qaytish vaqti
                    </label>
                    <input type="time" 
                           name="lunch_in" 
                           class="form-control @error('lunch_in') is-invalid @enderror"
                           value="{{ old('lunch_in', $attendance->lunch_in ? $attendance->lunch_in->format('H:i') : '') }}"
                           @if(!in_array(auth()->user()->email, config('admin.attendance_edit_emails', []))) readonly @endif>
                    @error('lunch_in')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="bi bi-sunset"></i> Kechki chiqish vaqti
                    </label>
                    <input type="time" 
                           name="evening_out"
                           class="form-control @error('evening_out') is-invalid @enderror"
                           value="{{ old('evening_out', $attendance->evening_out ? $attendance->evening_out->format('H:i') : '') }}"
                           @if(!in_array(auth()->user()->email, config('admin.attendance_edit_emails', []))) readonly @endif>
                    @error('evening_out')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if(in_array(auth()->user()->email, config('admin.attendance_edit_emails', [])))
            <hr class="my-4">

            <!-- Izohlar -->
            <h4><i class="bi bi-chat-text"></i> <b>Izohlar: </b></h4>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kechikish sababi</label>
                    <div class="mb-2">
                        <div class="btn-group d-flex" role="group" aria-label="Morning comment type">
                            <input type="radio" class="btn-check" name="morning_comment_type" id="morning_sababsiz" value="sababsiz" onchange="toggleCommentInput('morning')">
                            <label class="btn btn-outline-danger flex-fill" for="morning_sababsiz">
                                <i class="bi bi-x-circle"></i> Sababsiz
                            </label>

                            <input type="radio" class="btn-check" name="morning_comment_type" id="morning_sababli" value="sababli" onchange="toggleCommentInput('morning')">
                            <label class="btn btn-outline-success flex-fill" for="morning_sababli">
                                <i class="bi bi-check-circle"></i> Sababli
                            </label>
                        </div>
                    </div>
                    <div id="morning_comment_wrapper" style="display: none;">
                        <textarea name="morning_comment_text" 
                                  id="morning_comment" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Kechikish sababini yozing..."></textarea>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tushlik uzoq bo'lish sababi</label>
                    <div class="mb-2">
                        <div class="btn-group d-flex" role="group" aria-label="Lunch comment type">
                            <input type="radio" class="btn-check" name="lunch_comment_type" id="lunch_sababsiz" value="sababsiz" onchange="toggleCommentInput('lunch')">
                            <label class="btn btn-outline-danger flex-fill" for="lunch_sababsiz">
                                <i class="bi bi-x-circle"></i> Sababsiz
                            </label>

                            <input type="radio" class="btn-check" name="lunch_comment_type" id="lunch_sababli" value="sababli" onchange="toggleCommentInput('lunch')">
                            <label class="btn btn-outline-success flex-fill" for="lunch_sababli">
                                <i class="bi bi-check-circle"></i> Sababli
                            </label>
                        </div>
                    </div>
                    <div id="lunch_comment_wrapper" style="display: none;">
                        <textarea name="lunch_comment_text" 
                                  id="lunch_comment" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Tushlik uzoq bo'lish sababini yozing..."></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Erta ketish sababi</label>
                    <div class="mb-2">
                        <div class="btn-group d-flex" role="group" aria-label="Evening comment type">
                            <input type="radio" class="btn-check" name="evening_comment_type" id="evening_sababsiz" value="sababsiz" onchange="toggleCommentInput('evening')">
                            <label class="btn btn-outline-danger flex-fill" for="evening_sababsiz">
                                <i class="bi bi-x-circle"></i> Sababsiz
                            </label>

                            <input type="radio" class="btn-check" name="evening_comment_type" id="evening_sababli" value="sababli" onchange="toggleCommentInput('evening')">
                            <label class="btn btn-outline-success flex-fill" for="evening_sababli">
                                <i class="bi bi-check-circle"></i> Sababli
                            </label>
                        </div>
                    </div>
                    <div id="evening_comment_wrapper" style="display: none;">
                        <textarea name="evening_comment_text" 
                                  id="evening_comment" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Erta ketish sababini yozing..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Live hisoblash natijasi -->
            <div id="live-calculation-results"></div>
            @endif

            <!-- Avtomatik hisoblangan qiymatlar - barcha adminlar uchun ko'rinadi -->
            @if($attendance->exists && ($attendance->morning_late > 0 || $attendance->lunch_duration > 0 || $attendance->early_leave > 0))
                <hr class="my-4">
                <h5><i class="bi bi-calculator"></i> Saqlangan natijalar</h5>

                <div class="row">
                    @if($attendance->morning_late > 0)
                        <div class="col-md-4 mb-2">
                            <div class="alert alert-warning mb-0">
                                <strong>Kechikish:</strong> {{ $attendance->morning_late }} daqiqa
                                @if($attendance->morning_comment === 'Sababsiz' || empty($attendance->morning_comment))
                                    <br><small class="text-danger">(Sababsiz)</small>
                                @else
                                    <br><small class="text-success">({{ $attendance->morning_comment }})</small>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($attendance->lunch_duration > 0)
                        <div class="col-md-4 mb-2">
                            <div class="alert alert-info mb-0">
                                <strong>Tushlik vaqti:</strong> {{ $attendance->lunch_duration }} daqiqa
                                @if($attendance->lunch_comment === 'Sababsiz' || empty($attendance->lunch_comment))
                                    <br><small class="text-danger">(Sababsiz)</small>
                                @else
                                    <br><small class="text-success">({{ $attendance->lunch_comment }})</small>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($attendance->early_leave > 0)
                        <div class="col-md-4 mb-2">
                            <div class="alert alert-secondary mb-0">
                                <strong>Erta ketgan:</strong> {{ $attendance->early_leave }} daqiqa
                                @if($attendance->evening_comment === 'Sababsiz' || empty($attendance->evening_comment))
                                    <br><small class="text-danger">(Sababsiz)</small>
                                @else
                                    <br><small class="text-success">({{ $attendance->evening_comment }})</small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Orqaga
                </a>
                @if(in_array(auth()->user()->email, config('admin.attendance_edit_emails', [])))
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Saqlash
                    </button>
                @else
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="bi bi-eye"></i> Faqat ko'rish
                    </button>
                @endif
            </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    @if(in_array(auth()->user()->email, config('admin.attendance_edit_emails', [])))
    // Select dropdown o'zgarishini boshqarish (endi radio button uchun)
    function toggleCommentInput(type) {
        const selectedRadio = document.querySelector(`input[name="${type}_comment_type"]:checked`);
        const wrapperElement = document.getElementById(`${type}_comment_wrapper`);
        const textareaElement = document.getElementById(`${type}_comment`);
        
        if (selectedRadio && selectedRadio.value === 'sababli') {
            // Sababli tanlanganda textarea ko'rsatish va fokus berish
            wrapperElement.style.display = 'block';
            setTimeout(() => {
                textareaElement.focus();
            }, 100);
        } else {
            // Sababsiz tanlanganda textarea yashirish va bo'shatish
            wrapperElement.style.display = 'none';
            textareaElement.value = '';
        }
    }
    @endif

    document.addEventListener('DOMContentLoaded', function() {
        @if(in_array(auth()->user()->email, config('admin.attendance_edit_emails', [])))
        // Vaqt input'lariga event listener qo'shish
        const timeInputs = document.querySelectorAll('input[type="time"]');
        timeInputs.forEach(input => {
            input.addEventListener('change', function() {
                calculateLivePreview();
            });
        });

        // Mavjud ma'lumotlarni PHP dan JavaScript'ga yuklash
        @if($attendance->exists)
            // Ertalab kechikish izohi
            @if(!empty($attendance->morning_comment))
                @if($attendance->morning_comment === 'Sababsiz')
                    document.getElementById('morning_sababsiz').checked = true;
                @else
                    document.getElementById('morning_sababli').checked = true;
                    document.getElementById('morning_comment').value = @json($attendance->morning_comment);
                    toggleCommentInput('morning');
                @endif
            @endif

            // Tushlik uzoq bo'lish izohi
            @if(!empty($attendance->lunch_comment))
                @if($attendance->lunch_comment === 'Sababsiz')
                    document.getElementById('lunch_sababsiz').checked = true;
                @else
                    document.getElementById('lunch_sababli').checked = true;
                    document.getElementById('lunch_comment').value = @json($attendance->lunch_comment);
                    toggleCommentInput('lunch');
                @endif
            @endif

            // Erta ketish izohi
            @if(!empty($attendance->evening_comment))
                @if($attendance->evening_comment === 'Sababsiz')
                    document.getElementById('evening_sababsiz').checked = true;
                @else
                    document.getElementById('evening_sababli').checked = true;
                    document.getElementById('evening_comment').value = @json($attendance->evening_comment);
                    toggleCommentInput('evening');
                @endif
            @endif
        @endif

        // Live hisoblash funksiyasi
        function calculateLivePreview() {
            const morningIn = document.querySelector('input[name="morning_in"]').value;
            const lunchOut = document.querySelector('input[name="lunch_out"]').value;
            const lunchIn = document.querySelector('input[name="lunch_in"]').value;
            const eveningOut = document.querySelector('input[name="evening_out"]').value;

            let results = [];

            // Kechikish hisoblash
            if (morningIn) {
                const workStart = '09:00';
                if (morningIn > workStart) {
                    let minutes = calculateMinutesDiff(workStart, morningIn);

                    // Agar 13:00 dan keyin kelgan bo'lsa, abet vaqtini ayirish
                    const morningHour = parseInt(morningIn.split(':')[0]);
                    if (morningHour >= 13) {
                        minutes = Math.max(0, minutes - 60);
                    }

                    results.push(`<span class="badge bg-warning me-2 mb-2"><i class="bi bi-clock"></i> Kechikish: ${minutes} daqiqa</span>`);
                }
            }

            // Tushlik uzoqligi hisoblash
            if (lunchOut && lunchIn) {
                const duration = calculateMinutesDiff(lunchOut, lunchIn);
                const extra = Math.max(0, duration - 60);
                if (extra > 0) {
                    results.push(`<span class="badge bg-info me-2 mb-2"><i class="bi bi-cup-hot"></i> Tushlik vaqti: ${extra} daqiqa</span>`);
                }
            }

            // Erta ketish hisoblash
            if (eveningOut) {
                const workEnd = '18:00';
                if (eveningOut < workEnd) {
                    const minutes = calculateMinutesDiff(eveningOut, workEnd);
                    results.push(`<span class="badge bg-secondary me-2 mb-2"><i class="bi bi-door-open"></i> Erta ketgan: ${minutes} daqiqa</span>`);
                }
            }

            // Natijalarni ko'rsatish
            const resultsContainer = document.getElementById('live-calculation-results');
            if (results.length > 0) {
                resultsContainer.innerHTML = `
                    <div class="alert alert-light border mb-4">
                        <h6 class="mb-2"><i class="bi bi-calculator"></i> Live hisoblash:</h6>
                        ${results.join('')}
                    </div>
                `;
            } else {
                resultsContainer.innerHTML = '';
            }
        }

        // Daqiqalar orasidagi farqni hisoblash
        function calculateMinutesDiff(time1, time2) {
            const [h1, m1] = time1.split(':').map(Number);
            const [h2, m2] = time2.split(':').map(Number);
            const minutes1 = h1 * 60 + m1;
            const minutes2 = h2 * 60 + m2;
            return Math.abs(minutes2 - minutes1);
        }

        // Global funksiyalarni window'ga qo'shish
        window.calculateLivePreview = calculateLivePreview;
        window.toggleCommentInput = toggleCommentInput;

        // Boshlang'ich hisoblash
        calculateLivePreview();
        @endif
    });
</script>
@endpush
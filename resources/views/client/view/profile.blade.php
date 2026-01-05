@extends('layouts.client')

@section('content')

<!-- Page Header -->
<div class="page-header-wrapper">
    <div class="page-header-content">
        <div class="page-title">
            <i class="fas fa-user-circle"></i>
            <h3>Profil sozlamalari</h3>
        </div>
    </div>
</div>

<!-- Profile Container -->
<div class="profile-container">
    <div class="row g-4">
        <!-- Password Update Form -->
        <div class="col-lg-6">
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="fas fa-key"></i>
                    <h5>Parolni yangilash</h5>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-custom">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-custom">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                    @csrf

                    <div class="form-group-profile">
                        <label for="email" class="form-label-profile">
                            <i class="fas fa-envelope"></i>
                            Email manzil
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control-profile"
                               value="{{ old('email', auth()->user()->email) }}"
                               placeholder="email@example.com"
                               required>
                    </div>

                    <div class="form-group-profile">
                        <label for="old_password" class="form-label-profile">
                            <i class="fas fa-lock"></i>
                            Eski parol
                        </label>
                        <input type="password" 
                               id="old_password" 
                               name="old_password"
                               class="form-control-profile"
                               placeholder="••••••••"
                               required>
                    </div>

                    <div class="form-group-profile">
                        <label for="new_password" class="form-label-profile">
                            <i class="fas fa-lock-open"></i>
                            Yangi parol
                        </label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password"
                               class="form-control-profile"
                               placeholder="••••••••"
                               required>
                    </div>

                    <div class="form-group-profile">
                        <label for="new_password_confirmation" class="form-label-profile">
                            <i class="fas fa-shield-alt"></i>
                            Parolni tasdiqlash
                        </label>
                        <input type="password" 
                               id="new_password_confirmation" 
                               name="new_password_confirmation"
                               class="form-control-profile"
                               placeholder="••••••••"
                               required>
                    </div>

                    <button type="submit" class="btn-update-password">
                        <i class="fas fa-save"></i>
                        <span>Parolni yangilash</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="col-lg-6">
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="fas fa-user"></i>
                    <h5>Profil ma'lumotlari</h5>
                </div>

                <div class="profile-info-list">
                    <div class="profile-info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ auth()->user()->email }}</span>
                        </div>
                    </div>

                    <div class="profile-info-item">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">F.I.Sh</span>
                            <span class="info-value">{{ auth()->user()->lastName }} {{ auth()->user()->firstName }}</span>
                        </div>
                    </div>

                    <div class="profile-info-item">
                        <div class="info-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Lavozim</span>
                            <span class="info-value">{{ auth()->user()->position }}</span>
                        </div>
                    </div>

                    <div class="profile-info-item">
                        <div class="info-icon">
                            <i class="fas fa-hashtag"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Foydalanuvchi ID</span>
                            <span class="info-value">{{ auth()->user()->id }}</span>
                        </div>
                    </div>
                </div>

                <!-- Project Info Highlight -->
                <div class="project-highlight">
                    <div class="project-highlight-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="project-highlight-content">
                        <span class="project-label">Loyiha</span>
                        <span class="project-name">{{ auth()->user()->project->name ?? 'Loyiha biriktirilmagan' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
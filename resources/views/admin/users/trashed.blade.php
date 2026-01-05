@extends('layouts.admin')

@section('content')
<div class="row">
  <div class="col-lg-12 margin-tb">
      <div class="pull-left">
          <h2>O'chirilgan userlar</h2>
      </div>
      <div class="pull-right">
          <a class="btn btn-primary mb-2" href="{{ route('admin.users.index') }}">
            <i class="bi bi-arrow-left"></i> Faol userlar
          </a>
      </div>
  </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="bi bi-trash"></i> O'chirilgan Foydalanuvchilar
        </h5>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Eslatma:</strong> Bu userlar soft delete orqali o'chirilgan. Ular qayta tiklanishi yoki butunlay o'chirilishi mumkin.
            </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="trashedTable">
                <thead class="table-warning">
                    <tr>
                        <th scope="col">№</th>
                        <th scope="col">Ism Familiya</th>
                        <th scope="col">Lavozim</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rollar</th>
                        <th scope="col">O'chirilgan vaqt</th>
                        <th scope="col">Status</th>
                        <th scope="col">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>
                            <strong class="text-muted">{{ $user->firstName }} {{ $user->lastName }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $user->position ?? 'Belgilanmagan' }}</span>
                        </td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            @forelse ($user->getRoleNames() as $role)
                                <span class="badge bg-outline-primary me-1">{{ $role }}</span>
                            @empty
                                <span class="badge bg-secondary">Rol berilmagan</span>
                            @endforelse
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="bi bi-calendar-x"></i>
                                {{ $user->deleted_at->format('d.m.Y H:i') }}<br>
                                <em>{{ $user->deleted_at->diffForHumans() }}</em>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-danger">
                                <i class="bi bi-trash"></i> O'chirilgan
                            </span>
                        </td>
                        <td>
                            <div class="btn-group-vertical" role="group">
                                <!-- QAYTA TIKLASH -->
                                @can('edit-user')
                                    <form action="{{ route('admin.users.restore', $user->id) }}" 
                                          method="post" style="display:inline;" class="mb-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100" 
                                                title="Qayta tiklash"
                                                onclick="return confirm('{{ $user->firstName }} {{ $user->lastName }} ni qayta tiklamoqchimisiz?')">
                                            <i class="bi bi-arrow-clockwise"></i> Qayta tiklash
                                        </button>
                                    </form>
                                @endcan
                                
                                <!-- BUTUNLAY O'CHIRISH -->
                                @can('delete-user')
                                    @if(!$user->hasRole('Super Admin'))
                                        <form action="{{ route('admin.users.force-delete', $user->id) }}" 
                                              method="post" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-dark w-100" 
                                                    title="Butunlay o'chirish"
                                                    onclick="return confirm('DIQQAT! {{ $user->firstName }} {{ $user->lastName }} ni butunlay o\'chirmoqchimisiz? Bu amal qaytarilmaydi va barcha bog\'langan ma\'lumotlar yo\'qoladi!')">
                                                <i class="bi bi-trash-fill"></i> Butunlay o'chirish
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary w-100" disabled title="Super Admin ni butunlay o'chirish mumkin emas">
                                            <i class="bi bi-shield-lock"></i> Himoyalangan
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle"></i>
                                    <strong>Ajoyib! Hech qanday o'chirilgan user yo'q.</strong>
                                    <br>
                                    <small class="text-muted">Barcha userlar faol holatda.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light">
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    O'chirilgan userlar: <strong class="text-danger">{{ $users->count() }}</strong>
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Soft Delete orqali saqlanganlar
                </small>
            </div>
        </div>
    </div>
</div>

@if($users->count() > 0)
<div class="card mt-3">
    <div class="card-body">
        <h6 class="card-title text-warning">
            <i class="bi bi-exclamation-triangle"></i> Muhim eslatmalar:
        </h6>
        <ul class="small text-muted mb-0">
            <li><strong>Qayta tiklash:</strong> User va uning barcha ma'lumotlari qayta faol bo'ladi</li>
            <li><strong>Butunlay o'chirish:</strong> Bu amal qaytarilmaydi! User va bog'langan ma'lumotlar butunlay yo'qoladi</li>
            <li><strong>Bog'langan ma'lumotlar:</strong> task_assignments, assignments, attendances, reports saqlangan</li>
        </ul>
    </div>
</div>
@endif
@endsection
@extends('layouts.admin')

@section('content')
<div class="row">
  <div class="col-lg-12 margin-tb">
      <div class="pull-left">
          <h2>Userlarni boshqarish</h2>
      </div>
      <div class="pull-right">
        @can('create-user')
          <a class="btn btn-primary mb-2" href="{{ route('admin.users.create') }}">User qo'shish</a>
        @endcan
        @can('delete-user')
          <a class="btn btn-warning mb-2" href="{{ route('admin.users.trashed') }}">
            <i class="bi bi-trash"></i> O'chirilgan userlar
          </a>
        @endcan
      </div>
  </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Faol Foydalanuvchilar</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="myTable">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">№</th>
                        <th scope="col">Ism Familiya</th>
                        <th scope="col">Lavozim</th>
                        <th scope="col">Loyiha</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rollar</th>
                        <th scope="col">Status</th>
                        <th scope="col">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>
                            <strong>{{ $user->firstName }} {{ $user->lastName }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $user->position ?? 'Belgilanmagan' }}</span>
                        </td>
                        <td>
                            @if ($user->project)
                                <span class="badge bg-secondary">{{ $user->project->name }}</span>
                            @else
                                <span class="badge bg-secondary">Loyiha belgilanmagan</span>
                            @endif
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse ($user->getRoleNames() as $role)
                                <span class="badge bg-primary me-1">{{ $role }}</span>
                            @empty
                                <span class="badge bg-secondary">Rol berilmagan</span>
                            @endforelse
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Faol
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @if (in_array('Super Admin', $user->getRoleNames()->toArray() ?? []))
                                    @if (Auth::user()->hasRole('Super Admin'))
                                        <a href="{{ route('admin.users.edit', $user->id) }}" 
                                           class="btn btn-sm btn-primary" title="Tahrirlash">
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>
                                    @endif
                                @else
                                    @can('edit-user')
                                        <a href="{{ route('admin.users.edit', $user->id) }}" 
                                           class="btn btn-sm btn-primary" title="Tahrirlash">
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>   
                                    @endcan

                                    @can('delete-user')
                                        @if (Auth::user()->id != $user->id)
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                                  method="post" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="O'chirish"
                                                        onclick="return confirm('{{ $user->firstName }} {{ $user->lastName }} ni o\'chirmoqchimisiz?')">
                                                    <i class="bi bi-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled title="O'zingizni o'chira olmaysiz">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Hech qanday faol user topilmadi!</strong>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <small class="text-muted">
            Jami faol userlar: <strong>{{ $users->count() }}</strong>
        </small>
    </div>
</div>
@endsection
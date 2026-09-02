@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Pengguna</h4>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Pengguna
    </a>
</div>

<div class="table-card p-3">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $i => $user)
            <tr>
                <td>{{ $users->firstItem() + $i }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="badge bg-{{ match($user->role) {
                        'admin'  => 'danger',
                        'kasir'  => 'primary',
                        'gudang' => 'success',
                        default  => 'secondary'
                    } }}">
                        {{ $user->role_label }}
                    </span>
                </td>
                <td>
                    @if($user->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if(auth()->id() !== $user->id)
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus pengguna ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada pengguna</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
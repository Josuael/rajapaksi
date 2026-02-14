@extends('layouts.layout')

@section('title', 'User Management')

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0">User Management</h1>
            <div class="text-muted">Manage access users for this web</div>
        </div>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            + Add User
        </a>
    </div>

    <div class="mt-3 mb-3 h-px bg-slate-300"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="d-flex gap-2 mb-3">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Search name/login/posisi/role...">
        <select name="per_page" class="form-select" style="max-width:120px;">
            @foreach([10,15,25,50,100] as $n)
                <option value="{{ $n }}" @selected((int)request('per_page', 15) === $n)>{{ $n }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary">Search</button>
        @if(request()->query())
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>LoginID</th>
                    <th>Nama</th>
                    <th>Inisial</th>
                    <th>PosisiID</th>
                    <th>Versi</th>
                    <th>Role</th>
                    <th>SU</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->UserID }}</td>
                        <td>{{ $u->LoginID_plain }}</td>
                        <td>{{ $u->Nama }}</td>
                        <td>{{ $u->Inisial }}</td>
                        <td>{{ $u->PosisiID }}</td>
                        <td>{{ $u->Versi }}</td>
                        <td>
                            <span class="badge text-bg-dark text-uppercase">{{ $u->Role }}</span>
                        </td>
                        <td>
                            @if((int)$u->Su === 1)
                                <span class="badge text-bg-success">Yes</span>
                            @else
                                <span class="badge text-bg-secondary">No</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $u->UserID) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                            <form action="{{ route('admin.users.destroy', $u->UserID) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }}
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection

@extends('layouts.layout')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0">Edit User</h1>
            <div class="text-muted">Update web user access</div>
        </div>
        <a href="{{ route('admin.users.index') }}"
           class="text-decoration-none text-light px-4 py-2 bg-red-600 transition delay-150 duration-300 ease-in-out hover:scale-110 hover:bg-red-700 rounded-pill">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>
    </div>

    <div class="mt-3 mb-3 h-px bg-slate-300"></div>

    <div class="mt-8 rounded-3xl overflow-hidden shadow-sm bg-[#0b0f6a] px-6 pt-4 pb-3">
        <div class="mt-8 rounded-3xl bg-slate-100 shadow p-6">

            <form method="POST" action="{{ route('admin.users.update', $user->UserID) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label fw-semibold">LoginID</label>
                    <input type="text" name="login_id" class="form-control" value="{{ old('login_id', $user->LoginPlain) }}" required>
                    @error('login_name') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password (optional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="nama" class="form-control" value="{{ old('nama', $user->Nama) }}">
                    @error('nama') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Inisial <span class="text-muted">(required by DB)</span></label>
                    <input type="text" name="inisial" class="form-control" value="{{ old('inisial', $user->Inisial) }}" maxlength="5" required>
                    @error('inisial') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">PosisiID <span class="text-muted">(required by DB)</span></label>
                    <select name="posisi_id" class="form-select" required>
                        <option value="" disabled>-- Select Posisi --</option>
                        @foreach($posisiOptions as $pos)
                            <option value="{{ $pos }}" {{ old('posisi_id', $user->PosisiID) == $pos ? 'selected' : '' }}>
                                {{ $pos }}
                            </option>
                        @endforeach
                    </select>
                    @error('posisi_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role (akses web)</label>
                    <select name="role" class="form-select" required>
                        @foreach(['admin' => 'ADMIN', 'internal' => 'INTERNAL', 'karyawan' => 'KARYAWAN'] as $k => $v)
                            <option value="{{ $k }}" {{ old('role', $user->Role) == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('role') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Versi <span class="text-muted">(required by DB)</span></label>
                    <input type="text" name="versi" class="form-control" value="{{ old('versi', $user->Versi ?? $defaultVersi) }}" required>
                    @error('versi') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="su" name="su" {{ old('su', (int)$user->Su) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="su">
                            Su (legacy superuser)
                        </label>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save2"></i> Update
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@extends('layouts.layout')

@section('title', $title)

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0">{{ $title }}</h1>
            <div class="text-slate-600">
                Detail page for: <span class="font-semibold">{{ $slug }}</span>
            </div>
        </div>
        <a href="{{ route('home') }}"
           class="text-decoration-none text-light px-4 py-2 bg-red-600 transition delay-150 duration-300 ease-in-out hover:scale-110 hover:bg-red-700 rounded-pill">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>
    </div>

    <div class="mt-3 h-px bg-slate-300"></div>

    <div class="mt-8 rounded-3xl overflow-hidden shadow-sm bg-[#0b0f6a] px-6 pt-4 pb-3">
        <div class="mt-8 rounded-3xl bg-slate-100 shadow p-6">

            <form method="GET" id="filterForm" class="flex flex-wrap gap-3 items-center mb-4">
                <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">
                <input type="hidden" name="q" value="">

                @php
                    $hasProcessCol = $hasProcessCol ?? false;
                    $processOptions = $processOptions ?? collect();
                    $processUsable = (!empty($hasProcessCol) && $processOptions->count());
                @endphp

                @if(($slug ?? '') === 'recap')
                    <div class="w-100"></div>

                    {{-- ✅ RECAP FILTER GRID --}}
                    <div class="row g-3 align-items-end w-100 mb-2">

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold mb-1">Date From</label>
                            <input type="date"
                                   name="tanggal_from"
                                   class="form-control"
                                   value="{{ request('tanggal_from') }}">
                            <div class="form-text">(Filter tanggal dari)</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold mb-1">Date To</label>
                            <input type="date"
                                   name="tanggal_to"
                                   class="form-control"
                                   value="{{ request('tanggal_to') }}">
                                   <div class="form-text">(Filter tanggal sampai)</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold mb-1">Process</label>
                            <select name="process" class="form-select" {{ $processUsable ? '' : 'disabled' }}>
                                <option value="">All Process</option>
                                @if($processUsable)
                                    @foreach($processOptions as $p)
                                        <option value="{{ $p }}" {{ request('process') == $p ? 'selected' : '' }}>
                                            {{ $p }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text">
                                @if($processUsable)
                                    (Pilih process untuk filter data recap)
                                @else
                                    (Process filter tidak tersedia)
                                @endif
                            </div>
                        </div>

                        {{-- ✅ Buttons: desktop kanan, mobile turun & full width --}}
                        <div class="col-12 col-md-3">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-funnel"></i> Apply
                                </button>

                                <a href="{{ url()->current() }}?year={{ request('year', date('Y')) }}&per_page={{ request('per_page', 15) }}"
                                   class="btn btn-outline-secondary px-4 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>

                    </div>

                    {{-- ✅ row bawah: per_page + clear (desktop inline, mobile wrap) --}}
                    <div class="w-100 d-flex flex-wrap gap-2 align-items-center">
                        <select name="per_page" class="px-3 py-2 rounded-lg border">
                            @foreach([10,15,25,50,100] as $n)
                                <option value="{{ $n }}" @selected((int)request('per_page', 15) === $n)>{{ $n }}</option>
                            @endforeach
                        </select>

                        @if(request()->query())
                            <a href="{{ url()->current() }}?year={{ request('year', date('Y')) }}"
                               class="px-4 py-2 rounded-lg border">
                                Clear
                            </a>
                        @endif
                    </div>

                @else
                    <input
                        type="number"
                        name="year"
                        min="2000"
                        max="2100"
                        value="{{ request('year', date('Y')) }}"
                        class="px-3 py-2 rounded-lg border"
                        placeholder="Year"
                    />

                    <select name="per_page" class="px-3 py-2 rounded-lg border">
                        @foreach([10,15,25,50,100] as $n)
                            <option value="{{ $n }}" @selected((int)request('per_page', 15) === $n)>{{ $n }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 btn btn-primary rounded-lg text-white font-semibold">
                        <i class="bi bi-search"></i>
                        Apply
                    </button>

                    @if(request()->query())
                        <a href="{{ url()->current() }}?year={{ request('year', date('Y')) }}" class="px-4 py-2 rounded-lg border">
                            Clear
                        </a>
                    @endif
                @endif

                <div class="w-100"></div>

                {{-- ✅ Table + Column Filters --}}
                @include('pages.partials._process_table', [
                    'slug' => $slug,
                    'columns' => $columns,
                    'rows' => $rows,
                ])

            </form>
        </div>
    </div>
@endsection

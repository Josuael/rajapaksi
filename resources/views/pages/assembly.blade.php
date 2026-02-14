@extends('layouts.layout')

@section('title', $title ?? 'Assembly')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0">{{ $title ?? 'Assembly' }}</h1>
            <div class="text-slate-600">
                Detail page for: <span class="font-semibold">{{ $subtitle ?? '' }}</span>
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
        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('assembly', ['tab' => 'lasting'] + request()->except('page')) }}"
               class="px-3 py-2 rounded-pill text-decoration-none fw
                    {{ $activeTab === 'lasting' ? 'bg-[#33df89] text-dark fw-semibold' : 'bg-light text-dark border' }}">
                Lasting
            </a>

            <a href="{{ route('assembly', ['tab' => 'injection'] + request()->except('page')) }}"
               class="px-3 py-2 rounded-pill text-decoration-none
                    {{ $activeTab === 'injection' ? 'bg-[#33df89] text-dark fw-semibold' : 'bg-light text-dark border' }}">
                Injection
            </a>
        </div>

        <div class="mt-8 rounded-3xl bg-slate-100 shadow p-6">
            <form id="filterForm" method="GET" action="{{ route('assembly', ['tab' => $activeTab]) }}" class="flex flex-wrap gap-3 items-center mb-4">

                {{-- keep year supaya submit gak ilang --}}
                <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">

                {{-- Global q DIMATIIN --}}
                <input type="hidden" name="q" value="">

                {{-- keep tab --}}
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                {{-- FILTER PERIODE --}}
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
                    <i class="bi bi-search"></i> Apply
                </button>

                @if(request()->query())
                    <a href="{{ route('assembly', ['tab' => $activeTab]) }}?year={{ request('year', date('Y')) }}" class="px-4 py-2 rounded-lg border">
                        Clear
                    </a>
                @endif

                <div class="w-100"></div>

                {{-- ✅ Table + Column Filters --}}
                @include('pages.partials._process_table', [
                    'slug' => $slug,
                    'columns' => $columns,
                    'rows' => $rows,
                    'isAssembly' => true,
                ])

            </form>
        </div>
    </div>

    <script>
        // Optional: auto-submit filter on Enter only
    </script>
@endsection

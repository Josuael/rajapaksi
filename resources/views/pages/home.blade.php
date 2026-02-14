@extends('layouts.layout')

@section('title', 'Home Page')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="fw-bold m-0"">Home Page</h1>
    </div>

    <div class="mt-3 h-px bg-slate-300"></div>

    <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-1 xl:grid-cols-2">
        @foreach($cards as $card)
            <a href="{{ route('process.show', $card['slug']) }}" class="home-card group">
                <div class="home-card__inner">
                    <div class="home-card__title">{{ $card['title'] }} </div>
                    <div class="home-card__header mt-4">
                        <div>
                            <div class="home-card__sub fw-medium">Today</div>
                            <div class="home-card__sub2">Product</div>
                        </div>
                        <div class="home-card__value">{{ number_format($card['today'], 0, ',', '.') }}</div>
                    </div>

                    <div class="home-card__row is-green">
                        <div>
                            <div class="home-card__sub fw-medium">Finish</div>
                            <div class="home-card__sub2">Product</div>
                        </div>
                        <div class="home-card__value">{{ number_format($card['finish'], 0, ',', '.') }}</div>
                    </div>

                    <div class="home-card__row is-yellow">
                        <div>
                            <div class="home-card__sub fw-medium">Balance</div>
                            <div class="home-card__sub2">Product</div>
                        </div>
                        <div class="home-card__value">{{ number_format($card['balance'], 0, ',', '.') }}
</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endsection

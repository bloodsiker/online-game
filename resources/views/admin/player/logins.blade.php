@extends('admin.layout.base')

@section('title')
    Входы игрока: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'logins'])

    <section class="card">
        <div class="card-body">
                <div class="mt-3 mb-2">
                    Последние {{ $loginHistory->count() }} входов.
                    <a href="{{ route('admin.login_history.index', ['user' => $player->user_id]) }}">Вся история игрока</a>
                </div>
                @include('admin.login_history.partials.table', ['logs' => $loginHistory, 'showUser' => false])
        </div>
    </section>

@endsection

@extends('admin.layout.base')

@section('title')
    История входов
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form method="get" class="row mb-3">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="user" placeholder="Игрок или ID" value="{{ $filters['user'] }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="ip" placeholder="IP (начало)" value="{{ $filters['ip'] }}">
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="country" placeholder="Страна" maxlength="2" value="{{ $filters['country'] }}" title="Код страны, например UA">
                        </div>
                        <div class="col-md-2">
                            <select name="device_type" class="form-control">
                                <option value="">— все устройства —</option>
                                @foreach($deviceTypes as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['device_type'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] }}" title="Дата от">
                        </div>
                        <div class="col-md-1">
                            <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] }}" title="Дата до">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary">Фильтр</button>
                            <a href="{{ route('admin.login_history.index') }}" class="btn btn-default">Сбросить</a>
                        </div>
                    </form>

                    @include('admin.login_history.partials.table', ['logs' => $logs, 'showUser' => true])

                    {{ $logs->links() }}

                    <p class="small text-muted mt-3 mb-0"><a href="https://db-ip.com" target="_blank" rel="noopener">IP Geolocation by DB-IP</a></p>
                </div>
            </section>
        </div>
    </div>
@endsection

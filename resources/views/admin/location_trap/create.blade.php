@extends('admin.layout.base')

@section('title')
    Создать ловушку
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.location-trap.create') }}" method="post">
                        {{ csrf_field() }}

                        @include('admin.location_trap._form')

                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.location-traps') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection

@push('footer_scripts')
    @include('admin.location_trap._scripts')
@endpush

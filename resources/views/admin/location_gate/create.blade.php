@extends('admin.layout.base')

@section('title')
    Создать врата локаций
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.location-gate.create') }}" method="post">
                        {{ csrf_field() }}

                        @include('admin.location_gate._form', ['gate' => null])

                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.location-gates') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection

@push('footer_scripts')
    @include('admin.location_gate._scripts')
@endpush
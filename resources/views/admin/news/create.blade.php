@extends('admin.layout.base')

@section('title')
    Новая новость
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Новая новость</h2>
                </header>
                <div class="card-body">
                    <form action="{{ route('admin.news.store') }}" method="post">
                        {{ csrf_field() }}
                        @include('admin.news._form', ['news' => null])
                        <div class="text-end mt-3">
                            <a href="{{ route('admin.news') }}" class="btn btn-default">Назад</a>
                            <button class="btn btn-primary">Создать</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection

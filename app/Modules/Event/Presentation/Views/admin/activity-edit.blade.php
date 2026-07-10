@extends('admin.layout.base')

@section('title')
    События — Активность «{{ $activity->title }}»
@endsection

@section('body')

<div class="row">
    <div class="col-md-12">
        <section class="card">
            <header class="card-header">
                <h2 class="card-title">Редактирование активности «{{ $activity->title }}»</h2>
            </header>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.event.activity.update', $activity->id) }}">
                    @csrf
                    @include('event::admin._activity-form')
                    <div class="text-end">
                        <a href="{{ route('admin.event.activities') }}" class="btn btn-default">Назад</a>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

@endsection
@extends('admin.layout.base')
@section('title')Новое мировое событие@endsection
@section('body')<section class="card"><div class="card-body"><form method="post" action="{{ route('admin.event.world-events.store') }}" enctype="multipart/form-data">@csrf @include('event::admin.world-events._form')</form></div></section>@endsection

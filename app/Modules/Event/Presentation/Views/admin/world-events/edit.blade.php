@extends('admin.layout.base')
@section('title')Событие «{{ $event->title }}»@endsection
@section('body')<section class="card"><div class="card-body"><form method="post" action="{{ route('admin.event.world-events.update', $event) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('event::admin.world-events._form')</form></div></section>@endsection

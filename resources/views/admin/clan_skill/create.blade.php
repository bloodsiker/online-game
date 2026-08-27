@extends('admin.layout.base')

@section('title', 'Новый клановый навык')

@section('body')
    @include('admin.clan_skill.partials.definition-form', ['action' => route('admin.clan_skill.create'), 'skill' => null])
@endsection

@extends('admin.layout.base')

@section('title')
    НПС
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.npc.create') }}" class="btn btn-primary btn-sm">Добавить НПС</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="45"></th>
                                <th>Имя</th>
                                <th>Локация</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $npc)
                                <tr style="vertical-align: middle">
                                    <td>{{ $npc->id }}</td>
                                    <td>
                                        @if($npc->image)
                                            <img src="{{ Storage::url($npc->image) }}" style="width:36px;height:36px;object-fit:contain;" alt="">
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.npc.info', $npc->id) }}">{{ $npc->name }}</a></td>
                                    <td>{{ $npc->location ? '[' . $npc->location_id . '] ' . $npc->location->name : '—' }}</td>
                                    <td>
                                        <a href="{{ route('admin.npc.info', $npc->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Нет НПС</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
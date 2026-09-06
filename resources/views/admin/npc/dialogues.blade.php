@extends('admin.layout.base')

@section('title')
    Диалоги НПС
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Диалоги НПС</h2>
                </header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="45"></th>
                                <th>НПС</th>
                                <th>Локация</th>
                                <th width="120">Веток</th>
                                <th width="130">Активных</th>
                                <th width="140"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $npc)
                                <tr style="vertical-align: middle">
                                    <td>{{ $npc->id }}</td>
                                    <td>
                                        @if($npc->image)
                                            <img src="{{ $npc->image }}" style="width:60px;height:60px;object-fit:contain;" alt="">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.npc.info', $npc->id) }}#dialogues">{{ $npc->name }}</a>
                                    </td>
                                    <td>{{ $npc->location ? '[' . $npc->location_id . '] ' . $npc->location->name : '—' }}</td>
                                    <td>{{ $npc->dialogue_nodes_count }}</td>
                                    <td>{{ $npc->active_dialogue_nodes_count }}</td>
                                    <td>
                                        <a href="{{ route('admin.npc.info', $npc->id) }}#dialogues" class="btn btn-xs btn-primary">Редактировать</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Нет НПС</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection

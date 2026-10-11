@extends('admin.layout.base')
@section('title', 'Вольный торг')
@section('body')
<section class="card"><div class="card-body">
    <h3>Вольный торг</h3>
    <p>Лицензия разрешает покупать и продавать у одного НПС. Цены приёма задаются вручную; расстояние влияет только на опыт. Один предмет — один продавец. Цену закупки существующего товара менять нельзя, пока возможен старый груз.</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @php
        $sections = [
            'posts' => ['Торговцы', ['npc_id'=>'ID НПС', 'name'=>'Название', 'license_price'=>'Цена лицензии', 'is_active'=>'Активен (0/1)']],
            'goods' => ['Товары', ['trade_post_id'=>'ID торговца', 'share_item_id'=>'ID предмета', 'base_price'=>'Цена закупки', 'required_skill_lvl'=>'Навык', 'sort_order'=>'Порядок', 'is_active'=>'Активен (0/1)']],
            'routes' => ['Маршруты', ['trade_post_a_id'=>'Торговец A', 'trade_post_b_id'=>'Торговец B', 'distance_tier'=>'near / mid / far', 'exp_per_unit'=>'Опыт за штуку', 'is_active'=>'Активен (0/1)']],
            'offers' => ['Приём товаров', ['trade_post_id'=>'ID покупателя', 'trade_good_id'=>'ID товара', 'buy_price'=>'Цена при навыке 1', 'is_active'=>'Активен (0/1)']],
            'stocks' => ['Общие запасы', ['trade_good_id'=>'ID товара', 'current_quantity'=>'Осталось', 'max_quantity'=>'Максимум', 'restock_amount'=>'Пополнение', 'restock_interval_minutes'=>'Интервал (мин)']],
        ];
    @endphp
    @foreach($sections as $section => [$title, $fields])
        <h4>{{ $title }}</h4>
        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>ID</th>@foreach($fields as $caption)<th>{{ $caption }}</th>@endforeach<th></th></tr></thead><tbody>
        @foreach($$section->concat([null]) as $row)
            @php($formId = 'trade-'.$section.'-'.($row->id ?? 'new'))
            <tr><td>{{ $row->id ?? 'Новая' }}<form id="{{ $formId }}" method="post" action="{{ route('admin.trade.save', $section) }}">@csrf @if($row)<input type="hidden" name="id" value="{{ $row->id }}">@endif</form></td>
                @foreach($fields as $field => $caption)
                    <td><input class="form-control form-control-sm" form="{{ $formId }}" name="{{ $field }}" aria-label="{{ $caption }}" type="{{ in_array($field, ['name','distance_tier']) ? 'text' : 'number' }}" value="{{ $row->$field ?? ($field === 'is_active' ? 1 : '') }}" required style="min-width:85px"></td>
                @endforeach
                <td><button class="btn btn-primary btn-sm" form="{{ $formId }}">{{ $row ? 'Сохранить' : 'Добавить' }}</button></td>
            </tr>
        @endforeach
        </tbody></table></div>
    @endforeach
    <h4>История торговли</h4>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Время</th><th>Игрок</th><th>Торговец</th><th>Товар</th><th>Действие</th><th>Количество</th><th>Монеты</th><th>Опыт</th></tr></thead><tbody>
        @foreach($logs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->player_id }}</td><td>{{ $log->trade_post_id }}</td><td>{{ $log->trade_good_id }}</td><td>{{ $log->action }}</td><td>{{ $log->quantity }}</td><td>{{ $log->amount }}</td><td>{{ $log->experience }}</td></tr>@endforeach
    </tbody></table></div>{{ $logs->links() }}
</div></section>
@endsection

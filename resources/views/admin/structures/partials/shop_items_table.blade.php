<div class="table-responsive">
    <table class="table table-hover table-bordered mb-none">
        <thead>
        <tr>
            <th width="45"></th>
            <th>Название</th>
            <th width="130">Цена (монеты)</th>
            <th width="130">Цена (алмазы)</th>
            @if($structure->isBarterShop())
                <th width="340">Цена предметами</th>
            @endif
            <th width="120">Сортировка</th>
            <th width="150"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $shopItem)
            <tr style="vertical-align: middle">
                <td>
                    @if($shopItem->item?->image)
                        <img src="{{ $shopItem->item->image }}" width="36" alt="">
                    @endif
                </td>
                <td>
                    @if($shopItem->item)
                        <a href="{{ route('admin.item.info', $shopItem->item->id) }}" target="_blank">
                            {{ $shopItem->item->name }}
                        </a>
                    @else
                        —
                    @endif
                    <select name="share_structure_category_id"
                            form="shop-item-form-{{ $shopItem->id }}"
                            class="form-control form-control-sm mt-2">
                        <option value="">Без категории</option>
                        @foreach($structure->categories->sortBy('name') as $category)
                            <option value="{{ $category->id }}" @selected((int) $shopItem->share_structure_category_id === (int) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <form id="shop-item-form-{{ $shopItem->id }}"
                          action="{{ route('admin.structure.info.shop_update', [$structure->id, $shopItem->id]) }}"
                          method="post">
                        {{ csrf_field() }}
                    </form>
                    <input type="number"
                           min="0"
                           class="form-control form-control-sm"
                           name="price"
                           form="shop-item-form-{{ $shopItem->id }}"
                           value="{{ $shopItem->price }}">
                </td>
                <td>
                    <input type="number"
                           min="0"
                           class="form-control form-control-sm"
                           name="diamond"
                           form="shop-item-form-{{ $shopItem->id }}"
                           value="{{ $shopItem->diamond }}">
                </td>
                @if($structure->isBarterShop())
                    <td>
                        @foreach($shopItem->requirements as $requirement)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                @if($requirement->item)
                                    <img src="{{ $requirement->item->image }}" width="28" height="28"
                                         style="object-fit: contain" alt="">
                                    <span class="flex-grow-1">
                                        {{ $requirement->item->name }} × {{ $requirement->quantity }}
                                    </span>
                                @else
                                    <span class="flex-grow-1 text-muted">Удалённый предмет × {{ $requirement->quantity }}</span>
                                @endif
                                <form action="{{ route('admin.structure.info.shop_requirement.delete', [$structure->id, $shopItem->id, $requirement->id]) }}"
                                      method="post" onsubmit="return confirm('Убрать предмет из стоимости?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" title="Удалить">×</button>
                                </form>
                            </div>
                        @endforeach

                        <form action="{{ route('admin.structure.info.shop_requirement.add', [$structure->id, $shopItem->id]) }}"
                              method="post" class="row g-1 align-items-center">
                            @csrf
                            <div class="col-7">
                                <select name="share_item_id" class="form-control shop-requirement-item-select" required></select>
                            </div>
                            <div class="col-3">
                                <input type="number" name="quantity" min="1" value="1"
                                       class="form-control form-control-sm" required title="Количество">
                            </div>
                            <div class="col-2">
                                <button class="btn btn-xs btn-success" title="Добавить в стоимость">+</button>
                            </div>
                        </form>
                    </td>
                @endif
                <td>
                    <input type="number"
                           class="form-control form-control-sm"
                           name="sort_order"
                           form="shop-item-form-{{ $shopItem->id }}"
                           value="{{ $shopItem->sort_order }}">
                </td>
                <td>
                    <button class="btn btn-xs btn-primary"
                            form="shop-item-form-{{ $shopItem->id }}">Сохранить</button>
                    <a href="{{ route('admin.structure.info.shop_delete_item', [$structure->id, $shopItem->share_item_id]) }}"
                       class="btn btn-xs btn-danger"
                       onclick="return confirm('Удалить?')">Удалить</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ $structure->isBarterShop() ? 7 : 6 }}" class="text-center text-muted">Нет предметов</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<ul class="lscroll backpack_list connected-sortable ui-sortable">
    @foreach($items as $item)
        <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}">
            <table width="60" height="60" cellpadding="0" cellspacing="0" border="0"
                   style="background-image: url('{{ asset($item->item->itemInfo->image) }}');">
                <tbody>
                <tr>
                    <td data-id="{{ $item->item->id }}"
                        data-sid="{{ $item->item->share_item_id }}"
                        data-type="{{ $item->item->itemInfo->type->value }}"
                        data-equipped="{{ $item->isEquipped() ? '1' : '0' }}"
                        data-count="{{ $item->count }}"
                        data-name="{{ $item->item->itemInfo->type->value === 'treasure_map' ? $item->item->getName() : $item->item->itemInfo->name }}"
                        data-image="{{ asset($item->item->itemInfo->image) }}"
                        data-is-use="{{ $item->item->itemInfo->is_use ? '1' : '0' }}"
                        data-wearable="{{ $item->item->itemInfo->type->value === 'artifact' && $item->item->itemInfo->count_use === 0 ? '1' : '0' }}"
                        onclick="showCtxMenu(this, event)"
                        onmouseover="showItemInfo(this,event,2)"
                        onmouseout="showItemInfo(this,event,0)"
                        valign="bottom">
                        &nbsp;
                        @if($item->count > 1)
                            <div class="bpdig">{{ $item->count }}</div>
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </li>
    @endforeach
</ul>

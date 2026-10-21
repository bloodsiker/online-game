<tr class="assembly-item-row">
    <td><select class="form-control assembly-item-select" name="{{ $group }}[{{ $index }}][share_item_id]" required style="width:100%">
        @if(!empty($row['share_item_id']))<option value="{{ $row['share_item_id'] }}" selected>[{{ $row['share_item_id'] }}] {{ $itemNames[$row['share_item_id']] ?? 'Предмет #'.$row['share_item_id'] }}</option>@else<option value=""></option>@endif
    </select></td>
    <td style="width:130px"><input type="number" class="form-control" name="{{ $group }}[{{ $index }}][count]" value="{{ $row['count'] ?? 1 }}" min="1" max="{{ $group === 'rewards' ? 1000 : 1000000 }}" required></td>
    @if($group === 'rewards')<td class="assembly-weight" style="width:130px"><input type="number" class="form-control" name="rewards[{{ $index }}][weight]" value="{{ $row['weight'] ?? 1 }}" min="1" max="1000000" required></td>@endif
    <td style="width:85px"><button type="button" class="btn btn-danger btn-sm assembly-remove-row">Убрать</button></td>
</tr>

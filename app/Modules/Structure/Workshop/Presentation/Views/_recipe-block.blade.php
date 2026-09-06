<table class="coll w100 p10h p2v brd2-all" style="margin-bottom: 3px;">
    <tbody>
    <tr class="bg_l">
        <td>
            <div class="collections-title">
                <b class="collection-name"><a href="{{ route('items.info.share', ['id' => $recipe['recipeItemId']]) }}"
                       style="color: {{ $recipe['nameColor'] }};"
                       onclick="window.open(this.href,'','width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">{{ $recipe['name'] }}</a></b>&nbsp;&nbsp;
                <b class="collection-status @if(!$recipe['canCraft']) disabled @endif">{{ $recipe['professionName'] }}: {{ $recipe['currentLevel'] }} ур. / требуется {{ $recipe['requiredLevel'] }} ур.@if(!($recipe['learned'] ?? true)) · рецепт не изучен@endif</b>
            </div>
            <span class="collections-divider"></span>
            <div class="collections-body">
                <table>
                    <tbody><tr>
                        <td>
                            @foreach($recipe['ingredients'] as $ingredient)
                                <span class="collection-slot @if($ingredient['enough']) active @endif">
                                    <span class="collection-slot__img @if(!$ingredient['enough']) grayscale @endif"
                                          data-id="{{ $ingredient['id'] }}"
                                          onmouseover="showItemInfo(this,event,2)"
                                          onmouseout="showItemInfo(this,event,0)">
                                        <a href="{{ route('items.info.share', ['id' => $ingredient['id']]) }}"
                                               onclick="window.open(this.href,'','width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">
                                            <img src="{{ $ingredient['image'] }}">
                                        </a>
                                    </span>
                                    <span class="collection-slot__qty"><b class="collection-slot__qty-current">{{ $ingredient['available'] }}</b>/{{ $ingredient['required'] }}</span>
                                </span>
                            @endforeach
                        </td>
                        <td>
                            <b class="collection-ico" style="color:rgb(149, 92, 74);">=</b>
                        </td>
                        <td>
                            <span class="collection-slot">
                                <span class="collection-slot__img"
                                      data-id="{{ $recipe['resultId'] }}"
                                      onmouseover="showItemInfo(this,event,2)"
                                      onmouseout="showItemInfo(this,event,0)">
                                    <a href="{{ route('items.info.share', ['id' => $recipe['resultId']]) }}"
                                           onclick="window.open(this.href,'','width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">
                                        <img src="{{ $recipe['resultImage'] }}">
                                    </a>
                                    @if($showCraftButton ?? true)
                                        <form class="collect-btn" action="{{ route($craftRouteName, ['id' => $workshop->id, 'recipe' => $recipe['id']]) }}{{ $tabSuffix }}" method="post">
                                            @csrf
                                            <b class="butt2 pointer @if(!$recipe['canCraft']) disabled @endif"><b><input value="Создать" type="submit" style="width: 45px;" @disabled(!$recipe['canCraft'])></b></b>
                                        </form>
                                    @endif
                                </span>
                                <span class="collection-slot__qty"></span>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
</table>

@php
    $sections = [
        'equipment' => ['title' => __('Оружие, щиты и инструменты'), 'types' => ['weapon', 'shield', 'tool']],
        'armor' => ['title' => __('Доспехи'), 'types' => ['armor']],
        'accessories' => ['title' => __('Аксессуары'), 'types' => ['bag', 'belt']],
        'potions' => ['title' => __('Боевые эффекты'), 'types' => ['potion']],
        'food' => ['title' => __('Еда'), 'types' => ['eat']],
        'resources' => ['title' => __('Ресурсы'), 'types' => ['resource']],
        'scrolls' => ['title' => __('Свитки'), 'types' => ['scroll']],
        'treasure_maps' => ['title' => __('treasure.maps'), 'types' => ['treasure_map', 'treasure_map_fragment']],
        'recipes' => ['title' => __('Рецепты'), 'types' => ['recipe']],
        'books' => ['title' => __('Книги заклинаний'), 'types' => ['book']],
        'chests' => ['title' => __('Шкатулки'), 'types' => ['chest']],
        'gems' => ['title' => __('Камни'), 'types' => ['gem', 'mount']],
        'runes' => ['title' => __('Руны'), 'types' => ['rune', 'rune_key']],
        'trade_goods' => ['title' => __('Торговые товары'), 'types' => ['trade_goods']],
    ];
@endphp

<div class="backpack-categories">
    @if($data->getGroup() === 'main')
        @foreach($sections as $sectionId => $section)
            @php
                $sectionItems = collect($section['types'])->flatMap(
                    fn (string $type) => $data->getItems()->get($type, collect())->all()
                );
            @endphp
            @if($sectionItems->isNotEmpty())
                <section id="bag_section_{{ $sectionId }}" class="bag_section" aria-label="{{ $section['title'] }}">
                    <table class="bag-section__heading" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr height="22">
                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                            <td align="center" class="tbl-usi-hdr mbg">{{ $section['title'] }}</td>
                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                        </tr>
                        </tbody>
                    </table>
                    @include('backpack::partials.inventory-list', ['items' => $sectionItems])
                </section>
            @endif
        @endforeach
    @elseif(in_array($data->getGroup(), ['key', 'quest', 'artifact', 'gift', 'misc'], true))
        @include('backpack::partials.inventory-list', ['items' => $data->getBackpack()])
    @endif
</div>

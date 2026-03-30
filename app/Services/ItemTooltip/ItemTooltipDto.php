<?php

namespace App\Services\ItemTooltip;

final class ItemTooltipDto {
    public function __construct(
        public int    $id,
        public string $title,
        public string $color,
        public string $image,
        public string $kind,
        public string $price,
        public string $diamond,
        public array  $lev,
        public array  $skills,
        public string $desc,
        public bool   $store,
        public bool   $nogive,
        public bool   $noweight,
        public bool   $nosell,
        public array  $stats = [],   // характеристики предмета: [{title, value}]
    ) {}

    public function toArray(): array
    {
        return [
            'id'       => (string) $this->id,
            'title'    => $this->title,
            'color'    => $this->color,
            'image'    => $this->image,
            'kind'     => $this->kind,
            'price'    => $this->price,
            'diamond'  => $this->diamond,
            'lev'      => $this->lev,
            'stats'    => $this->stats,
            'skills'   => $this->skills,
            'store'    => $this->store,
            'nogive'   => $this->nogive   ? 'Предмет нельзя передать!' : '',
            'noweight' => $this->noweight ? 'Предмет не занимает места в рюкзаке' : '',
            'nosell'   => $this->nosell   ? 'Предмет нельзя сдать в скупку' : '',
            'desc'     => $this->desc,
        ];
    }
}
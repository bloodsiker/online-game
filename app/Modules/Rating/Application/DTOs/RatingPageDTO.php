<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\DTOs;

final readonly class RatingPageDTO
{
    /**
     * @param  list<RatingMenuItemDTO>  $menu
     * @param  list<RatingEntryDTO>  $entries
     * @param  array{currentPage:int,lastPage:int,pageFrom:int,pageTo:int,urls:array<int,string>,onFirstPage:bool,hasMorePages:bool,previousPageUrl:?string,nextPageUrl:?string,firstItem:int}  $pagination
     */
    public function __construct(
        public string $title,
        public string $columnName,
        public string $type,
        public string $currentUserName,
        public array $menu,
        public array $entries,
        public array $pagination,
    ) {}
}

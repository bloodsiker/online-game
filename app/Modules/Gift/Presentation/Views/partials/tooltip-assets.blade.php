{{-- Подсказка предметов (public/js/item_tooltip.js) на отдельной странице подарков — те же стили, что в user::info. --}}
<style>
    #artifact_alt .aa-table { border-radius: 30px 30px 0 0; box-shadow: 3px 3px 3px -1px rgba(0, 0, 0, .2); font-size: 11px; }
    .aa-tl { background: url(/img/bg/item_info/tbl-pop_corner-top-left.gif) no-repeat; width: 14px; height: 24px; }
    .aa-t { background: url(/img/bg/item_info/tbl-pop_top.gif); height: 24px; }
    .aa-tr { background: url(/img/bg/item_info/tbl-pop_corner-top-right.gif) no-repeat; width: 14px; height: 24px; }
    .aa-l { background: url(/img/bg/item_info/tbl-pop_left.gif) repeat-y; width: 14px; }
    .aa-r { background: url(/img/bg/item_info/tbl-pop_right.gif) repeat-y; width: 14px; }
    .aa-bl { background: url(/img/bg/item_info/tbl-pop_corner-bottom-left.gif) no-repeat; width: 14px; height: 5px; }
    .aa-b { background: url(/img/bg/item_info/tbl-pop_bottom.gif) repeat-x; height: 5px; }
    .aa-br { background: url(/img/bg/item_info/tbl-pop_corner-bottom-right.gif) no-repeat; width: 14px; height: 5px; }
    .list_dark { background-color: #F4BB8A; }
    .skill_list td { padding: 0 7px; }
    .red, .red * { color: #d00000; }
    .redd, .redd * { color: #ba0000 !important; }
</style>
{!! $itemTooltipScript !!}
<script>window.gebi = window.gebi || function (id) { return document.getElementById(id); };</script>
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>

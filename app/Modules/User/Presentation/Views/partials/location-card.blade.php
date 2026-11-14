@if($location)
    <div style="position:relative;width:100%;overflow:hidden;background:linear-gradient(135deg,#d9b98b,#8d704d);border:1px solid #b68b62;box-sizing:border-box;">
        <div aria-hidden="true" style="padding-top:40%;"></div>
        @if($location->map?->profile_image)
            <img src="{{ $location->map->profile_image }}" alt="{{ $location->map->name }}" style="position:absolute;top:0;left:0;display:block;width:100%;height:100%;object-fit:cover;object-position:center;">
        @endif
        <div style="position:absolute;bottom:0;left:0;right:0;padding:18px 8px 8px;background:linear-gradient(transparent,rgba(0,0,0,.75));text-align:center;">
            <b style="color:#fff0c4;text-shadow:0 1px 2px #000;">{{ $location->map?->name }}</b>
        </div>
    </div>
@endif

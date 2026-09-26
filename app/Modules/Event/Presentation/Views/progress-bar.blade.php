<div class="rep-progress-bar" title="{{ $title }}" @isset($kind) data-progress-kind="{{ $kind }}" @endisset>
    <div class="rep-progress-bar__bg"><div class="rep-progress-bar__fill" style="width: {{ $percent }}%;"></div></div>
    <div class="rep-progress-bar__border"><div class="rep-progress-bar__border-left"></div><div class="rep-progress-bar__border-right"></div><div class="rep-progress-bar__border-center"></div></div>
    <div class="rep-progress-bar__text">{{ $percent }}%</div>
</div>

<div class="rep-progress-bar rep-progress-bar--small" title="{{ $page->progressTitle }}"
     role="progressbar" aria-label="Прогресс репутации" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $page->progressPercent }}">
    <div class="rep-progress-bar__bg"><div class="rep-progress-bar__fill" style="width:{{ $page->progressPercent }}%"></div></div>
    <div class="rep-progress-bar__border">
        <div class="rep-progress-bar__border-left"></div>
        <div class="rep-progress-bar__border-right"></div>
        <div class="rep-progress-bar__border-center"></div>
    </div>
    <div class="rep-progress-bar__text">{{ $page->progressPercent }}%</div>
</div>

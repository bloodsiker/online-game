@php
    // full — лайтбокс, preview — большое превью (240×126), thumb — миниатюра (80×60).
    // Файлы: N-N.webp (2000px), N-Nsm.webp (750px), N-Nxs.webp (160×120).
    $screens = collect(range(1, 15))->map(fn (int $number): array => [
        'full' => asset("main/images/screen/{$number}-{$number}.webp"),
        'preview' => asset("main/images/screen/{$number}-{$number}sm.webp"),
        'thumb' => asset("main/images/screen/{$number}-{$number}xs.webp"),
    ]);
    $first = $screens->first();
@endphp
<div class="b-shape__content b-shape__content--column">
    <div class="b-slider b-slider--default homepage-gallery" data-homepage-gallery role="region" aria-label="{{ __('homepage.screenshots') }}">
        <div class="b-slider__preview">
            <a class="homepage-gallery__preview" data-gallery-preview href="{{ $first['full'] }}" aria-haspopup="dialog">
                <img class="b-slider__image" src="{{ $first['preview'] }}" width="240" height="126" decoding="async" alt="{{ __('homepage.gallery.image', ['number' => 1]) }}">
            </a>
        </div>
        <div class="b-slider__thumbs">
            <div class="homepage-gallery__track" data-gallery-track>
                @foreach($screens as $index => $screen)
                    <a class="homepage-gallery__thumb" data-gallery-index="{{ $index }}" data-preview="{{ $screen['preview'] }}" href="{{ $screen['full'] }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}">
                        <img src="{{ $screen['thumb'] }}" width="78" height="60" loading="lazy" decoding="async" alt="{{ __('homepage.gallery.image', ['number' => $index + 1]) }}">
                    </a>
                @endforeach
            </div>
            <button class="b-slider__nav b-slider__nav--prev" data-gallery-prev type="button" aria-label="{{ __('homepage.gallery.previous') }}"></button>
            <button class="b-slider__nav b-slider__nav--next" data-gallery-next type="button" aria-label="{{ __('homepage.gallery.next') }}"></button>
        </div>
    </div>
</div>

<div class="b-shape__content b-shape__content--column">
    <div class="b-slider b-slider--default homepage-gallery" data-homepage-gallery role="region" aria-label="{{ __('homepage.screenshots') }}">
        <div class="b-slider__preview">
            <a class="homepage-gallery__preview" data-gallery-preview href="{{ asset('main/images/screen/1.png') }}" aria-haspopup="dialog">
                <img class="b-slider__image" src="{{ asset('main/images/screen/1.png') }}" width="240" height="176" decoding="async" alt="{{ __('homepage.gallery.image', ['number' => 1]) }}">
            </a>
        </div>
        <div class="b-slider__thumbs">
            <div class="homepage-gallery__track" data-gallery-track>
                @foreach(range(1, 7) as $number)
                    <a class="homepage-gallery__thumb" data-gallery-index="{{ $number - 1 }}" href="{{ asset('main/images/screen/'.$number.'.png') }}" aria-current="{{ $number === 1 ? 'true' : 'false' }}">
                        <img src="{{ asset('main/images/screen/'.$number.'.png') }}" width="78" height="60" loading="lazy" decoding="async" alt="{{ __('homepage.gallery.image', ['number' => $number]) }}">
                    </a>
                @endforeach
            </div>
            <button class="b-slider__nav b-slider__nav--prev" data-gallery-prev type="button" aria-label="{{ __('homepage.gallery.previous') }}"></button>
            <button class="b-slider__nav b-slider__nav--next" data-gallery-next type="button" aria-label="{{ __('homepage.gallery.next') }}"></button>
        </div>
    </div>
</div>

{{-- Лицензионное соглашение (страница /eula). Текст по языку интерфейса. --}}
<style>
    .eula { color: #3b2717; font: 11px/1.65 Tahoma, Arial, sans-serif; }
    .eula h1 { margin: 4px 0 10px; color: #8b1a1a; font: bold 16px/22px Tahoma, Arial, sans-serif; text-align: center; }
    .eula h2 { margin: 14px 0 6px; color: #6b2a12; font: bold 12px/18px Tahoma, Arial, sans-serif; }
    .eula p { margin: 0 0 6px; }
    .eula ol { margin: 0 0 6px; padding-left: 20px; }
    .eula li { margin-bottom: 3px; }
    .eula__updated { color: #7a5a3a; text-align: center; font-style: italic; }
</style>
<article class="b-post b-post--index">
    <div class="b-post__container">
        <div class="b-post__content eula">
            @include('news::partials.eula.'.(app()->getLocale() === 'uk' ? 'uk' : 'ru'))
        </div>
    </div>
</article>

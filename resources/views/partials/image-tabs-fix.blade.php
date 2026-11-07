{{--
    Кнопки-вкладки из картинок (img/bg/btn/btn-left|cent|right*.gif) — исправление для Safari.
    В таблице на всю ширину Safari отдаёт свободное место и ячейкам-уголкам (width=19): уголок оставался
    у края расширенной ячейки, и между ним и фоном кнопки появлялся разрыв. Прижимаем уголки к кнопке,
    убираем зазор под картинкой и перенос подписи. В Chrome/Firefox ничего не меняется.
--}}
<style>
    td:has(> img[src*="bg/btn/btn-left"]) > img { display: block; margin-left: auto; }
    td:has(> img[src*="bg/btn/btn-right"]) > img { display: block; margin-right: auto; }
    td:has(> img[src*="bg/btn/btn-"]) > br { display: none; }
    td[style*="bg/btn/btn-cent"] { white-space: nowrap; }
</style>

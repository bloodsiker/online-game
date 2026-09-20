<script>
    (function () {
        var source = document.getElementById('forum-editor');
        if (!source || source.dataset.editorInitialized === 'true') return;

        source.dataset.editorInitialized = 'true';
        source.classList.add('forum-rich-editor__source');

        var editor = document.createElement('div');
        editor.className = 'forum-rich-editor';
        editor.innerHTML = [
            '<div class="forum-rich-editor__toolbar" role="toolbar" aria-label="Форматирование сообщения">',
            '  <button type="button" data-command="undo" title="Отменить">↶</button>',
            '  <button type="button" data-command="redo" title="Повторить">↷</button>',
            '  <span class="forum-rich-editor__separator"></span>',
            '  <button type="button" data-command="bold" title="Жирный"><b>Ж</b></button>',
            '  <button type="button" data-command="italic" title="Курсив"><i>К</i></button>',
            '  <button type="button" data-command="underline" title="Подчёркнутый"><u>Ч</u></button>',
            '  <button type="button" data-command="removeFormat" title="Убрать форматирование">×</button>',
            '  <span class="forum-rich-editor__separator"></span>',
            '  <button type="button" data-command="insertUnorderedList" title="Маркированный список">•≡</button>',
            '  <button type="button" data-command="insertOrderedList" title="Нумерованный список">1≡</button>',
            '  <span class="forum-rich-editor__dropdown">',
            '    <button type="button" data-action="alignment" title="Выравнивание текста">≡</button>',
            '    <span class="forum-rich-editor__dropdown-menu" hidden>',
            '      <button type="button" data-command="justifyLeft" title="По левому краю">≡←</button>',
            '      <button type="button" data-command="justifyCenter" title="По центру">≡</button>',
            '      <button type="button" data-command="justifyRight" title="По правому краю">→≡</button>',
            '      <button type="button" data-command="justifyFull" title="По ширине">☰</button>',
            '    </span>',
            '  </span>',
            '  <button type="button" data-action="link" title="Вставить ссылку">🔗</button>',
            '  <button type="button" data-action="source" title="Показать HTML">&lt;/&gt;</button>',
            '</div>',
            '<div class="forum-rich-editor__content" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Введите сообщение..."></div>'
        ].join('');

        source.parentNode.insertBefore(editor, source);

        var content = editor.querySelector('.forum-rich-editor__content');
        var sourceButton = editor.querySelector('[data-action="source"]');
        var alignmentButton = editor.querySelector('[data-action="alignment"]');
        var alignmentMenu = editor.querySelector('.forum-rich-editor__dropdown-menu');
        var sourceMode = false;

        function safeEditorHtml(html) {
            var template = document.createElement('template');
            template.innerHTML = html;
            template.content.querySelectorAll('script, style, iframe, object, embed, svg, math').forEach(function (element) {
                element.remove();
            });
            template.content.querySelectorAll('*').forEach(function (element) {
                Array.from(element.attributes).forEach(function (attribute) {
                    var name = attribute.name.toLowerCase();
                    var value = attribute.value.trim().toLowerCase();
                    if (name.indexOf('on') === 0 || name === 'srcdoc') element.removeAttribute(attribute.name);
                    if ((name === 'href' || name === 'src') && /^(javascript|data):/.test(value)) element.removeAttribute(attribute.name);
                });
            });

            return template.innerHTML;
        }

        content.innerHTML = safeEditorHtml(source.value);

        function editorHtml() {
            var copy = content.cloneNode(true);
            copy.querySelectorAll('div').forEach(function (block) {
                var paragraph = document.createElement('p');
                if (/^(left|center|right|justify)$/.test(block.style.textAlign)) {
                    paragraph.style.textAlign = block.style.textAlign;
                }
                while (block.firstChild) paragraph.appendChild(block.firstChild);
                block.replaceWith(paragraph);
            });

            return copy.innerHTML;
        }

        function syncSource() {
            if (!sourceMode) source.value = editorHtml();
        }

        function runCommand(command, value) {
            content.focus();
            document.execCommand(command, false, value || null);
            syncSource();
        }

        editor.querySelectorAll('[data-command]').forEach(function (button) {
            button.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });
            button.addEventListener('click', function () {
                runCommand(button.dataset.command);
                alignmentMenu.hidden = true;
            });
        });

        alignmentButton.addEventListener('mousedown', function (event) {
            event.preventDefault();
        });
        alignmentButton.addEventListener('click', function (event) {
            event.stopPropagation();
            alignmentMenu.hidden = !alignmentMenu.hidden;
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.forum-rich-editor__dropdown')) alignmentMenu.hidden = true;
        });

        editor.querySelector('[data-action="link"]').addEventListener('mousedown', function (event) {
            event.preventDefault();
        });
        editor.querySelector('[data-action="link"]').addEventListener('click', function () {
            var url = window.prompt('Введите адрес ссылки:', 'https://');
            if (!url || url === 'https://') return;
            if (!/^(https?:\/\/|mailto:|\/[^/])/i.test(url)) {
                window.alert('Разрешены ссылки http://, https://, mailto: или внутренние ссылки, начинающиеся с /.');
                return;
            }
            runCommand('createLink', url);
        });

        sourceButton.addEventListener('click', function () {
            if (sourceMode) {
                source.value = safeEditorHtml(source.value);
                content.innerHTML = source.value;
                source.hidden = true;
                content.hidden = false;
                content.focus();
            } else {
                syncSource();
                content.hidden = true;
                source.hidden = false;
                source.focus();
            }

            sourceMode = !sourceMode;
            sourceButton.classList.toggle('active', sourceMode);
        });

        content.addEventListener('input', syncSource);
        source.hidden = true;

        var form = source.closest('form');
        if (form) form.addEventListener('submit', syncSource);
    })();
</script>

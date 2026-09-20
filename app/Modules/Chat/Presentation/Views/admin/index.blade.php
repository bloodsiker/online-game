@extends('admin.layout.base')

@section('title', 'Чат')

@section('body')
    <style>
        .chat-admin-editor { border: 1px solid #ced4da; border-radius: 4px; background: #fff; }
        .chat-admin-editor__toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; padding: 6px; border-bottom: 1px solid #ddd; background: #f5f5f5; }
        .chat-admin-editor__toolbar button { min-width: 32px; height: 30px; padding: 2px 8px; border: 1px solid #bbb; border-radius: 3px; color: #333; background: #fff; cursor: pointer; }
        .chat-admin-editor__toolbar button:hover { border-color: #888; background: #e9ecef; }
        .chat-admin-editor__separator { width: 1px; height: 24px; margin: 0 3px; background: #ccc; }
        .chat-admin-editor__color { width: 36px; height: 30px; padding: 2px; border: 1px solid #bbb; border-radius: 3px; background: #fff; cursor: pointer; }
        .chat-admin-editor__content { min-height: 150px; max-height: 320px; overflow-y: auto; padding: 10px 12px; outline: none; white-space: pre-wrap; }
        .chat-admin-editor__content:empty::before { color: #999; content: attr(data-placeholder); pointer-events: none; }
    </style>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <section class="card card-featured card-featured-primary">
                <header class="card-header">
                    <h2 class="card-title">Отправить системное сообщение</h2>
                    <p class="card-subtitle">Сообщение увидят все находящиеся в игре пользователи независимо от выбранного канала.</p>
                </header>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.chat.send') }}">
                        @csrf
                        <div class="form-group">
                            <label class="col-form-label" for="system-chat-message-type">Тип сообщения</label>
                            <select id="system-chat-message-type" name="type" class="form-control" required>
                                @foreach($sendableMessageTypes as $messageType)
                                    <option
                                        value="{{ $messageType['type'] }}"
                                        data-color="{{ $messageType['color'] }}"
                                        data-icon="{{ $messageType['icon'] }}"
                                        data-bold="{{ $messageType['bold'] ? '1' : '0' }}"
                                        data-italic="{{ $messageType['italic'] ? '1' : '0' }}"
                                        @selected(old('type', 'system') === $messageType['type'])
                                    >{{ $messageType['title'] }} — {{ $messageType['appearance'] }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Тип меняет оформление. Из этой формы сообщение всегда увидят все игроки в течение 30 минут.
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label" for="system-chat-message">Текст сообщения</label>
                            <div class="chat-admin-editor">
                                <div class="chat-admin-editor__toolbar" role="toolbar" aria-label="Форматирование сообщения">
                                    <button type="button" data-editor-command="undo" title="Отменить">↶</button>
                                    <button type="button" data-editor-command="redo" title="Повторить">↷</button>
                                    <span class="chat-admin-editor__separator"></span>
                                    <button type="button" data-editor-command="bold" title="Жирный"><b>Ж</b></button>
                                    <button type="button" data-editor-command="italic" title="Курсив"><i>К</i></button>
                                    <button type="button" data-editor-command="underline" title="Подчёркнутый"><u>Ч</u></button>
                                    <button type="button" data-editor-command="strikeThrough" title="Зачёркнутый"><s>З</s></button>
                                    <button type="button" data-editor-command="removeFormat" title="Убрать форматирование">×</button>
                                    <span class="chat-admin-editor__separator"></span>
                                    <label class="mb-0" title="Цвет выделенного текста">
                                        <span class="sr-only">Цвет текста</span>
                                        <input id="system-chat-message-color" class="chat-admin-editor__color" type="color" value="#ba0000">
                                    </label>
                                </div>
                                <div
                                    id="system-chat-message-editor"
                                    class="chat-admin-editor__content"
                                    contenteditable="true"
                                    role="textbox"
                                    aria-multiline="true"
                                    data-placeholder="Введите сообщение..."
                                ></div>
                            </div>
                            <textarea id="system-chat-message" name="message" hidden>{{ old('message') }}</textarea>
                            <small class="form-text text-muted">
                                Выделите текст для изменения стиля или цвета. Можно использовать игровые шорткоды: [[user_1]], [[item_1]], [[share_item_1]], [[money_100]], [[diamond_5]].
                            </small>
                        </div>

                        <div id="system-chat-message-preview" class="p-2 mb-3" style="border-left:2px solid #cc00ff;color:#cc00ff;font-weight:bold;background:#fff8ff;white-space:pre-line;">
                            <span id="system-chat-message-preview-icon">★</span>
                            <span id="system-chat-message-preview-text">Так сообщение будет выглядеть в чате</span>
                        </div>

                        <button type="submit" class="btn btn-primary">Отправить всем игрокам</button>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Справочник служебных сообщений</h2>
                    <p class="card-subtitle">Фактические цвета и время отображения в текущей реализации чата.</p>
                </header>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Тип</th>
                                <th>Вид</th>
                                <th>Кому видно</th>
                                <th width="155">Отображается</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($systemMessageTypes as $messageType)
                                <tr>
                                    <td><strong>{{ $messageType['title'] }}</strong></td>
                                    <td>
                                        <span style="display:inline-block;width:12px;height:12px;border:1px solid #777;background:{{ $messageType['color'] }};vertical-align:-1px;margin-right:5px;"></span>
                                        {{ $messageType['appearance'] }}
                                    </td>
                                    <td>{{ $messageType['audience'] }}</td>
                                    <td>{{ $messageType['lifetime'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('system-chat-message-type');
            const form = typeSelect.closest('form');
            const messageInput = document.getElementById('system-chat-message');
            const messageEditor = document.getElementById('system-chat-message-editor');
            const colorInput = document.getElementById('system-chat-message-color');
            const preview = document.getElementById('system-chat-message-preview');
            const previewIcon = document.getElementById('system-chat-message-preview-icon');
            const previewText = document.getElementById('system-chat-message-preview-text');

            let savedSelection = null;

            function sanitizeEditorHtml(html) {
                const template = document.createElement('template');
                template.innerHTML = html;
                const allowedTags = ['BR', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'STRIKE', 'SPAN', 'FONT', 'DIV', 'P'];

                template.content.querySelectorAll('script, style, iframe, object, embed, svg, math').forEach(function (element) {
                    element.remove();
                });
                Array.from(template.content.querySelectorAll('*')).reverse().forEach(function (element) {
                    if (!allowedTags.includes(element.tagName)) {
                        element.replaceWith(...element.childNodes);
                        return;
                    }

                    Array.from(element.attributes).forEach(function (attribute) {
                        const isFontColor = element.tagName === 'FONT' && attribute.name.toLowerCase() === 'color' && /^#[0-9a-f]{6}$/i.test(attribute.value);
                        const isSpanColor = element.tagName === 'SPAN' && attribute.name.toLowerCase() === 'style' && /^\s*color\s*:\s*(#[0-9a-f]{3,6}|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))\s*;?\s*$/i.test(attribute.value);
                        if (!isFontColor && !isSpanColor) element.removeAttribute(attribute.name);
                    });
                });

                return template.innerHTML;
            }

            function saveSelection() {
                const selection = window.getSelection();
                if (selection.rangeCount && messageEditor.contains(selection.anchorNode)) {
                    savedSelection = selection.getRangeAt(0).cloneRange();
                }
            }

            function restoreSelection() {
                if (!savedSelection) return;
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(savedSelection);
            }

            function syncMessage() {
                messageInput.value = sanitizeEditorHtml(messageEditor.innerHTML);
                updatePreview();
            }

            function runCommand(command, value) {
                messageEditor.focus();
                restoreSelection();
                document.execCommand(command, false, value || null);
                saveSelection();
                syncMessage();
            }

            function updatePreview() {
                const option = typeSelect.options[typeSelect.selectedIndex];
                const color = option.dataset.color;
                const icon = option.dataset.icon;

                preview.style.color = color;
                preview.style.borderLeftColor = color;
                preview.style.fontWeight = option.dataset.bold === '1' ? 'bold' : 'normal';
                preview.style.fontStyle = option.dataset.italic === '1' ? 'italic' : 'normal';
                previewIcon.textContent = icon;
                previewIcon.style.display = icon ? 'inline' : 'none';
                previewText.innerHTML = messageInput.value.trim() || 'Так сообщение будет выглядеть в чате';
            }

            typeSelect.addEventListener('change', updatePreview);
            messageEditor.innerHTML = sanitizeEditorHtml(messageInput.value);
            messageEditor.addEventListener('keyup', saveSelection);
            messageEditor.addEventListener('mouseup', saveSelection);
            messageEditor.addEventListener('input', syncMessage);
            messageEditor.addEventListener('paste', function (event) {
                event.preventDefault();
                document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
            });

            document.querySelectorAll('[data-editor-command]').forEach(function (button) {
                button.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    saveSelection();
                });
                button.addEventListener('click', function () {
                    runCommand(button.dataset.editorCommand);
                });
            });

            colorInput.addEventListener('mousedown', saveSelection);
            colorInput.addEventListener('input', function () {
                runCommand('foreColor', colorInput.value);
            });

            form.addEventListener('submit', function (event) {
                syncMessage();
                if (messageEditor.textContent.trim().length < 2) {
                    event.preventDefault();
                    window.alert('Введите не менее двух символов текста сообщения.');
                    messageEditor.focus();
                }
            });

            syncMessage();
            updatePreview();
        });
    </script>
@endsection

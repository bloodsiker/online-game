<!doctype html>
<html class="fixed">
<head>
    <meta charset="UTF-8">

    <title>Admin</title>
    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="{{ asset('admin/vendor/bootstrap/css/bootstrap.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/animate/animate.compat.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/font-awesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/boxicons/css/boxicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/magnific-popup/magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/select2/css/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/select2-bootstrap-theme/select2-bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/vendor/pnotify/pnotify.custom.css') }}" />

    <link rel="stylesheet" href="{{ asset('admin/css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/css/skins/default.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/css/custom.css') }}" />
    <style>
        .admin-floating-save {
            position: fixed;
            z-index: 1100;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            padding: 11px;
            border: 1px solid #2f7e2f;
            border-radius: 4px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.3);
        }

        .admin-floating-save[hidden] {
            display: none;
        }

        @media (max-width: 767px) {
            .admin-floating-save {
                right: 12px;
            }
        }
    </style>

    @stack('styles')

    <script src="{{ asset('admin/vendor/modernizr/modernizr.js') }}"></script>
</head>
<body>
<section class="body">

    @include('admin.layout.header')

    <div class="inner-wrapper">

        @include('admin.layout.menu')

        <section role="main" class="content-body">
            @php
                $pageTitle = preg_replace(
                    '/\s+/u',
                    ' ',
                    trim(html_entity_decode(strip_tags($__env->yieldContent('title')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
                ) ?? '';
            @endphp
            <header class="page-header">
                <h2>{{ $pageTitle }}</h2>
                @include('admin.layout.breadcrumbs', ['pageTitle' => $pageTitle])
            </header>

            @yield('body')

        </section>
    </div>
</section>

<button id="admin-floating-save" type="button" class="btn btn-success admin-floating-save" aria-label="Сохранить" title="Сохранить" hidden>
    <i class="fas fa-save" aria-hidden="true"></i>
</button>

<script src="{{ asset('admin/vendor/jquery/jquery.js') }}"></script>
<script src="{{ asset('admin/vendor/jquery-browser-mobile/jquery.browser.mobile.js') }}"></script>
<script src="{{ asset('admin/vendor/popper/umd/popper.min.js') }}"></script>
<script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('admin/vendor/common/common.js') }}"></script>
<script src="{{ asset('admin/vendor/nanoscroller/nanoscroller.js') }}"></script>
<script src="{{ asset('admin/vendor/magnific-popup/jquery.magnific-popup.js') }}"></script>
<script src="{{ asset('admin/vendor/jquery-placeholder/jquery.placeholder.js') }}"></script>
<script src="{{ asset('admin/vendor/pnotify/pnotify.custom.js') }}"></script>
<script src="{{ asset('admin/vendor/select2/js/select2.js') }}"></script>

<script src="{{ asset('admin/js/theme.js') }}"></script>
<script src="{{ asset('admin/js/custom.js') }}"></script>
<script src="{{ asset('admin/js/theme.init.js') }}"></script>
<script src="{{ asset('admin/js/modals.js') }}"></script>

<script>
    function initializeFloatingSave() {
        const floatingSave = document.getElementById('admin-floating-save');
        if (!floatingSave || floatingSave.dataset.initialized === 'true') return;

        const designatedForm = document.querySelector('.content-body form[data-floating-save-form]');
        const submitButtons = designatedForm
            ? designatedForm.querySelectorAll('button, input[type="submit"]')
            : document.querySelectorAll('.content-body form button, .content-body form input[type="submit"]');
        const submitButton = Array.from(submitButtons)
            .find(function (button) {
                const label = button.tagName === 'INPUT' ? button.value : button.textContent;

                return button.type !== 'button'
                    && !button.disabled
                    && /^(сохранить|создать)(?:\s|$)/i.test(label.trim());
            });

        if (!submitButton) return;

        floatingSave.dataset.initialized = 'true';
        floatingSave.hidden = false;
        floatingSave.addEventListener('click', function () {
            const form = submitButton.form;
            if (!form) return;

            form.requestSubmit(submitButton);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeFloatingSave);
    } else {
        initializeFloatingSave();
    }
    window.addEventListener('load', initializeFloatingSave);
</script>

@stack('footer_scripts')

@if(session('success'))
<script>
    $(function() {
        new PNotify({ title: 'Успешно', text: @json(session('success')), type: 'success', styling: 'bootstrap3', buttons: { closer: true, sticker: false } });
    });
</script>
@endif
@if(session('error'))
<script>
    $(function() {
        new PNotify({ title: 'Ошибка', text: @json(session('error')), type: 'error', styling: 'bootstrap3', buttons: { closer: true, sticker: false } });
    });
</script>
@endif

</body>
</html>

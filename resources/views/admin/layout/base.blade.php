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

    <script src="{{ asset('admin/vendor/modernizr/modernizr.js') }}"></script>
</head>
<body>
<section class="body">

    @include('admin.layout.header')

    <div class="inner-wrapper">

        @include('admin.layout.menu')

        <section role="main" class="content-body">
            <header class="page-header">
                <h2>@yield('title')</h2>
                <div class="pull-right" style="padding: 12px 10px;margin-right: 20px;">
                    <a href="" class="btn btn-primary btn-xs">Add</a>
                </div>
            </header>

            @yield('body')

        </section>
    </div>
</section>

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

@stack('footer_scripts')

</body>
</html>

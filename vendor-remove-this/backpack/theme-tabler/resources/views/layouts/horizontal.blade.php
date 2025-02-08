<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" dir="{{ backpack_theme_config('html_direction') }}">

<head>
    @include(backpack_view('inc.head'))
</head>

<body class="{{ backpack_theme_config('classes.body') }}" bp-layout="horizontal">

@include(backpack_view('layouts.partials.light_dark_mode_logic'))

<div class="page">
    <div class="page-wrapper">

        <div class="@if(backpack_theme_config('options.useStickyHeader')) sticky-top @endif">
            @includeWhen(backpack_theme_config('options.doubleTopBarInHorizontalLayouts'), backpack_view('layouts._horizontal.header_container'))
            @include(backpack_view('layouts._horizontal.menu_container'))
        </div>



          <div class="page-body">
            <main class="{{ backpack_theme_config('options.useFluidContainers') ? 'container-fluid' : 'container-xl' }}">

                @yield('before_breadcrumbs_widgets')
                @includeWhen(isset($breadcrumbs), backpack_view('inc.breadcrumbs'))
                @yield('after_breadcrumbs_widgets')
                @yield('header')

                <div class="container-fluid animated fadeIn">
                    @yield('before_content_widgets')
                    @yield('content')
                    @yield('after_content_widgets')
                </div>
            </main>
        </div>

        @include(backpack_view('inc.footer'))
    </div>
</div>

@yield('before_scripts')
@stack('before_scripts')

@include(backpack_view('inc.scripts'))
@include(backpack_view('inc.theme_scripts'))

@yield('after_scripts')
@stack('after_scripts')

<script>
    $('#version-switch').on('click', function() {
        $.ajax({
            url: "{!! route('version-change') !!}",
            data: {
                "_token": "{{ csrf_token() }}",
            },
            type: "POST",
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    new Noty({
                        type: response.type,
                        layout: 'topRight',
                        text: response.message,
                        timeout: 3000,
                    }).show();
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX request failed:", error);
            }
        });
    });
</script>

</body>
</html>

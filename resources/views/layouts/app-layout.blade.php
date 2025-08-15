<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('themes/bootstrap/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('themes/icon.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div id="cc" class="easyui-layout" data-options="fit:true">
        <x-menu />
        <div data-options="region:'center',title:'{{ $title }}'">
            {{ $slot }}
        </div>
    </div>
    <x-title-bar />

    <script type="text/javascript" src="{{ asset('jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('jquery.easyui.min.js') }}"></script>

    @stack('scripts')
</body>

</html>

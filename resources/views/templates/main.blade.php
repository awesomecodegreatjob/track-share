<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Track Share')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @yield('meta_info')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.min.css">
    <link rel="stylesheet" href="{{ asset('/style.css') }}">
</head>
<body>
    @include('components.google_analytics')

    @yield('body')
</body>
</html>

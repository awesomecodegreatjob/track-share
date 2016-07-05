{{--

   @var  string  $agent
   @var  MusicInfo  $info

--}}

        <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta property="og:title" content="{{ $info->title }} by {{ $info->artist }}">
    <meta property="og:image" content="{{ $info->image_link }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.min.css">

</head>
<body>

<div class="row">
    <div class="small-12 columns text-center">
        <a href="/">&lsaquo; Share Another</a>
    </div>
</div>
<div class="row">
    <div class="small-12 columns text-center">
        <img src="{{ $info->image_link }}" alt="{{ $info->title }}"/>
    </div>
</div>
<div class="row">
    <div class="small-12 columns text-center">
        <h3>{{ $info->title }} <small>by: {{ $info->artist }}</small></h3>
    </div>
</div>
<div class="row">
    <div class="small-6 columns text-right">
        <a class="button small alert" href="{{ $google_link }}" target="_blank">Listen on Google Music</a>
    </div>
    <div class="small-6 columns">
        <a class="button small success" href="{{ $spotify_link }}" target="_blank">Listen on Spotify</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/js/foundation.min.js"></script>
</body>
</html>

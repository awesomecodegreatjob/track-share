{{--

   @var  string  $agent
   @var  MusicInfo  $info

--}}

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $info->title }} by {{ $info->artist }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta property="og:title" content="{{ $info->title }} by {{ $info->artist }}">
    <meta property="og:description" content="Share album and track links with all your friends">
    <meta property="og:image" content="{{ $info->image_link }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.min.css">
    <link rel="stylesheet" href="/style.css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/fontello.css" media="screen" charset="utf-8">


</head>
<body>

<div class="row">
    <div class="small-12 columns small-centered back-button">
        <a href="/"><i class="icon-fast-backward"></i> Share Another</a>
    </div>
</div>
<div class="row">
    <div class="small-12 columns text-center">
        <img class="image_link" src="{{ $info->image_link }}" alt="{{ $info->title }}"/>
    </div>
</div>
<br/>
<div class="row">
    <div class="small-12 columns text-center">
        <h3>{{ $info->title }} <small>by: {{ $info->artist }}</small></h3>
    </div>
</div>
<br/>
<div class="row">
    <div class="medium-6 small-12 columns text-right">
        <a class="button google small  link-share-button" href="{{ $google_link }}" target="_blank">Listen on Google&nbsp;Music&nbsp;<i class="icon-headphones"></i></a>
    </div>
    <div class="medium-6 small-12 columns">
        <a class="button spotify small  link-share-button" href="{{ $spotify_link }}" target="_blank">Listen on Spotify&nbsp;<i class="icon-spotify"></i></a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/js/foundation.min.js"></script>
</body>
</html>

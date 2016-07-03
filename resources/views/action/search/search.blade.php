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

    

     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/normalize.min.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.min.css">

</head>
<body>

   <h2>Agent: {{ $agent }}</h2>

   <img src="{{ $info->image_link }}" alt="{{ $info->title }}" />
   <h3>{{ $info->title }}</h3>
   <p>by: {{ $info->artist }}</p>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/js/foundation.min.js"></script>
</body>
</html>

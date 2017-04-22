{{--

   @var  string  $agent
   @var  MusicInfo  $info
   @var  string|null  $google_link
   @var  string|null  $spotify_link

--}}
@extends('templates.main')

@section('title', $info->title . ' by ' . $info->artist)

@section('body')
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
            @if($google_link)
                <a class="button google small link-share-button" href="{{ $google_link }}" target="_blank">Listen on Google&nbsp;Music&nbsp;<i class="icon-gmusic"></i></a>
            @else
                <a class="button google small link-share-button disabled" target="_blank">Listen on Google&nbsp;Music&nbsp;<i class="icon-gmusic"></i></a>
            @endif
        </div>
        <div class="medium-6 small-12 columns">
            @if($spotify_link)
                <a class="button spotify small link-share-button" href="{{ $spotify_link }}" target="_blank">Listen on Spotify&nbsp;<i class="icon-spotify"></i></a>
            @else
                <a class="button spotify small link-share-button disabled" target="_blank">Not available on Spotify</a>
            @endif
        </div>
    </div>
@endsection

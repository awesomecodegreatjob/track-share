{{--

    @var  App\Music  $music

--}}
@extends('templates.main')

@if($music->track)
    @section('title', $music->track . ' by ' . $music->artist)
@else
    @section('title', $music->album . ' by ' . $music->artist)
@endif

@section('body')
    <div class="row">
        <div class="small-12 columns small-centered back-button">
            <a href="/"><i class="icon-fast-backward"></i> Share Another</a>
        </div>
    </div>
    <div class="row">
        <div class="small-12 columns text-center">
            <img class="image_link" src="{{ $music->image_url }}" alt="{{ $music->album }}"/>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="small-12 columns text-center">
            @if($music->track)
                <h3>{{ $music->track }} <small>by {{ $music->band }}</small></h3>
            @else
                <h3>{{ $music->album }} <small>by {{ $music->band }}</small></h3>
            @endif
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="small-12 medium-5 medium-offset-1 large-4 large-offset-2 columns text-right">
            @if($music->google_music_url)
                <a class="button google expand link-share-button" href="{{ $music->google_music_url }}" target="_blank">Listen on Google&nbsp;Music&nbsp;<i class="icon-gmusic"></i></a>
            @else
                <a class="button google expand link-share-button disabled" target="_blank">Not available on Google&nbsp;Music&nbsp;<i class="icon-gmusic"></i></a>
            @endif
        </div>
        <div class="small-12 medium-5 large-4 columns end">
            @if($music->spotify_url)
                <a class="button spotify expand link-share-button" href="{{ $music->spotify_url }}" target="_blank">Listen on Spotify&nbsp;<i class="icon-spotify"></i></a>
            @else
                <a class="button spotify expand link-share-button disabled" target="_blank">Not available on Spotify</a>
            @endif
        </div>
    </div>
@endsection

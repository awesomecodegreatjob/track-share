{{--

    @var MusicInfo $info
    @var Collection<MusicInfo> $matches

--}}

@extends('layouts.main')

@section('pageMeta')
    @if($info->isTrack())
        <meta property="og:title" content="{{ $info->getTrack()  }} by {{ $info->getArtist() }}">
    @else
        <meta property="og:title" content="{{ $info->getAlbum() }} by {{ $info->getArtist() }}">
    @endif
    <meta property="og:description" content="Share your music with all of your friends">
    <meta property="og:image" content="{{ $info->getImageUrl() }}">
@endsection

@section('body')
    <div class="pb-4 sm:w-3/5 mx-auto sm:mt-6">
        <header class="flex justify-between items-center items-end py-4 px-4 sm:px-0 sm:pt-0 sm:items-baseline">
            <h1 class="font-sans">{{ config('app.name', 'Laravel') }}</h1>
            <a href="/" class="hover:underline no-underline text-grey-darker">Share Another</a>
        </header>
        <div class="w-full overflow-hidden bg-white sm:rounded sm:shadow-lg">
            <img class="w-full" src="{{ $info->getImageUrl() }}" alt="Sunset in the mountains">
            <div class="px-6 py-4">
                <div class="font-bold text-xl mb-2">
                    @if ($info->getType() === 'album')
                        {{ $info->getAlbum() }} by {{ $info->getArtist() }}
                    @elseif ($info->getType() === 'track')
                        {{ $info->getTrack() }} by {{ $info->getArtist() }}
                    @endif
                </div>
                <div class="pt-2 clearfix">
                    @foreach($matches as $match)
                    <div class="w-1/2 float-left text-center">
                        @render('MusicLink', [ 'info' => $match ])
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

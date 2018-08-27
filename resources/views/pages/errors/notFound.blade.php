{{--

    @var string $error

--}}
@extends('layouts.main')

@section('body')
    <div class="pb-4 sm:w-3/5 mx-auto sm:mt-6">
        <header class="flex justify-between items-end">
            <h1 class="font-sans pb-4">{{ config('app.name', 'Laravel') }}</h1>
            <a href="/" class="hover:underline pb-4 no-underline text-grey-darker">Share Another</a>
        </header>
        <div class="w-full overflow-hidden bg-white sm:rounded sm:shadow-lg">
            <div class="px-6 py-4">
                <div class="font-bold text-xl mb-2">{{ $error }}</div>
            </div>
        </div>
    </div>
@endsection

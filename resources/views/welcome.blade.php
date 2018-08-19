@extends('layouts.main')

@section('body')
    <div class="flex flex-col">
        <div class="min-h-screen flex items-center justify-center">
            <div class="flex flex-col justify-around h-full">
                <div>
                    <h1 class="text-grey-darker text-center font-thin tracking-wide text-5xl mb-6">
                        {{ config('app.name', 'Track Share') }}
                    </h1>
                    <form action="{{ route('search') }}" method="get">
                        <input type="text" name="q" class="px-3 py-2 border border-grey">
                        <input type="submit" value="Search" class="px-3 py-2 bg-grey hover:bg-grey-dark cursor-pointer">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

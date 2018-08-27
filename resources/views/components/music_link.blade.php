{{--

    @var  MusicInfo  $info
    @var  string[]   $decoration
    @var  string     $decoration['color']
    @var  string     $decoration['name']

--}}
@if ($info->getLink())
    <a href="{{ $info->getLink() }}" class="inline-block px-4 py-3 no-underline bg-{{ $decoration['color'] }}-dark text-white">{{ $decoration['name'] }}</a>
@else
    <a class="inline-block px-4 py-3 no-underline bg-{{ $decoration['color'] }}-dark text-white" disabled>Not Available on {{ $decoration['name'] }}</a>
@endif

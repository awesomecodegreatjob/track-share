<?php

namespace Tests\Feature;

use App\MusicLink;
use PhpSlang\Option\Option;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MusicSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itTakesAUriAndRedirectsToALink()
    {
        MusicLink::setKey('aaa');

        $result = $this->get(
            url()->route('music.search', [ 'q' => 'spotify:track:7H7T22yvZMLVzJHDONDYDp'])
        );

        $result->assertRedirect(
            url()->route('music.link', [ 'key' => 'aaa' ])
        );
    }

    /** @test */
    public function itTakesAUriAndCreatesRecordsASeed()
    {
        $this->get(
            url()->route('music.search', [ 'q' => 'spotify:track:7H7T22yvZMLVzJHDONDYDp'])
        );

        $newLink = MusicLink::find(1);

        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $newLink->seeds['spotify']['id']);
        $this->assertEquals('Tpn5uojhuow4k7p7cw3sofyvbfa', $newLink->seeds['googleMusic']['id']);
    }
}

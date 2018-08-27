<?php

namespace Tests\Feature;

use App\MusicInfo;
use App\MusicLink;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MusicLinkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itShowsATrackLink()
    {
        MusicLink::create([
            'key' => 'xxx',
            'seeds' => [
                'spotify' => [
                    'id' => '7H7T22yvZMLVzJHDONDYDp',
                    'type' => 'track',
                    'service' => 'spotify',
                ],
                'googleMusic' => [
                    'id' => 'Tpn5uojhuow4k7p7cw3sofyvbfa',
                    'type' => 'track',
                    'service' => 'googleMusic',
                ],
            ],
        ]);

        $response = $this->get(url()->route('music.link', [ 1 ]));

        $response->assertSee('Tobacco');
        $response->assertSee('Stretch Your Face');
    }
}

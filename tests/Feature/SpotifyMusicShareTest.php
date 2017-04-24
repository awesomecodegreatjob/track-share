<?php

namespace tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Test\TestCase;

class SpotifyMusicShareTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function itRedirectsOldSpotifyTrackSharesToNewFormat()
    {
        $this->call('get', '/spotify/track/7H7T22yvZMLVzJHDONDYDp');
        $this->assertResponseStatus(302);

        $lastest_music = $this->getLastMusic();

        $this->call('get', '/m/'.$lastest_music->key);

        $this->see('Stretch Your Face by Tobacco');
    }

    /** @test */
    public function itRedirectsOldSpotifyAlbumSharesToNewFormat()
    {
        $this->call('get', '/spotify/album/1M1dhwZE65bqGfbUdMzvlj');
        $this->assertResponseStatus(302);

        $lastest_music = $this->getLastMusic();

        $this->call('get', '/m/'.$lastest_music->key);

        $this->see('Mellow Gold by Beck');
    }
}
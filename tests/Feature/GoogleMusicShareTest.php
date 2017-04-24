<?php

namespace tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Test\TestCase;

class GoogleMusicShareTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function itRedirectsOldGoogleMusicSharesToNewFormat()
    {
        $this->call('get', '/google/Blymcq6c7hwxpt5whoernti6nma');
        $this->assertResponseStatus(302);

        $lastest_music = $this->getLastMusic();

        $this->call('get', '/m/'.$lastest_music->key);

        $this->see('Mimicking Birds by Mimicking Birds');
    }
}
<?php

use App\SpotifyFinder;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SpotifyFinderTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_resource_URIs()
    {
        $finder = new SpotifyFinder;

        $this->assertTrue($finder->matches('spotify:track:7H7T22yvZMLVzJHDONDYDp'));
        $this->assertTrue($finder->matches('https://open.spotify.com/track/7H7T22yvZMLVzJHDONDYDp'));

        $this->assertFalse($finder->matches('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q?t=Entombment_of_a_Machine_-_Job_For_A_Cowboy'));
    }
}

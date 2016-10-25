<?php

use App\SpotifyFinder;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SpotifyFinderTest extends TestCase
{
    protected $track_uri_1 = 'spotify:track:7H7T22yvZMLVzJHDONDYDp';
    protected $track_uri_2 = 'https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj';

    /**
     * @test
     */
    public function it_validates_resource_URIs()
    {
        $finder = new SpotifyFinder;

        $this->assertTrue($finder->matches($this->track_uri_1));
        $this->assertTrue($finder->matches($this->track_uri_2));

        $this->assertFalse($finder->matches('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }

    /**
     * @test
     */
    public function it_extracts_a_resource_id_from_valid_URIs()
    {
        $finder = new SpotifyFinder;

        $this->assertEquals('track', $finder->music_id($this->track_uri_1)[0]);
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $finder->music_id($this->track_uri_1)[1]);

        $this->assertEquals('album', $finder->music_id($this->track_uri_2)[0]);
        $this->assertEquals('1M1dhwZE65bqGfbUdMzvlj', $finder->music_id($this->track_uri_2)[1]);

        $this->assertFalse($finder->music_id('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }
}

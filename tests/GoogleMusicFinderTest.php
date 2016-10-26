<?php

use App\GoogleMusicFinder;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GoogleMusicFinderTest extends TestCase
{
    protected $music_uri_1 = 'https://play.google.com/music/m/Toknxdtl3kx7askzrt7byiusbdm?t=Everything_in_Its_Right_Place_-_Radiohead';
    protected $music_uri_2 = 'https://play.google.com/music/m/B5c5kftiwy4a3zind5r4ip6uepm?t=II_-_Unknown_Mortal_Orchestra';

    /**
     * @test
     */
    public function it_validates_resource_URIs()
    {
        $finder = new GoogleMusicFinder;

        $this->assertTrue($finder->matches($this->music_uri_1));
        $this->assertTrue($finder->matches($this->music_uri_2));

        $this->assertFalse($finder->matches('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj'));
    }

    /**
     * @test
     */
    public function it_extracts_a_resource_id_from_valid_URIs()
    {
        $finder = new GoogleMusicFinder;

        $this->assertEquals('track', $finder->music_id($this->music_uri_1)[0]);
        $this->assertEquals('Toknxdtl3kx7askzrt7byiusbdm', $finder->music_id($this->music_uri_1)[1]);

        $this->assertEquals('track', $finder->music_id($this->music_uri_2)[0]);
        $this->assertEquals('B5c5kftiwy4a3zind5r4ip6uepm', $finder->music_id($this->music_uri_2)[1]);

        $this->assertFalse($finder->music_id('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj'));
    }
}

<?php

namespace Test\Unit;

use App\MusicInfo;
use Test\TestCase;
use App\SpotifyFinder;
use InvalidArgumentException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SpotifyFinderTest extends TestCase
{
    // Stretch Your Face by Tobacco
    protected $music_uri_1 = 'spotify:track:7H7T22yvZMLVzJHDONDYDp';

    // Mellow Gold by Beck
    protected $music_uri_2 = 'https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj';

    /** @test */
    public function it_validates_resource_URIs()
    {
        $finder = new SpotifyFinder;

        $this->assertTrue($finder->matches($this->music_uri_1));
        $this->assertTrue($finder->matches($this->music_uri_2));

        $this->assertFalse($finder->matches('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }

    /** @test */
    public function it_extracts_a_resource_id_from_valid_URIs()
    {
        $finder = new SpotifyFinder;

        $this->assertEquals('track', $finder->music_id($this->music_uri_1)[0]);
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $finder->music_id($this->music_uri_1)[1]);

        $this->assertEquals('album', $finder->music_id($this->music_uri_2)[0]);
        $this->assertEquals('1M1dhwZE65bqGfbUdMzvlj', $finder->music_id($this->music_uri_2)[1]);

        $this->assertFalse($finder->music_id('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }

    /** @test */
    public function itGetsTrackInfoById()
    {
        $finder = new SpotifyFinder;

        $info = $finder->music_info_by_id('track', '7H7T22yvZMLVzJHDONDYDp');

        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $info->id);
        $this->assertEquals('Stretch Your Face', $info->track);
        $this->assertEquals('Maniac Meat', $info->album);
        $this->assertEquals('Tobacco', $info->artist);
        $this->assertInternalType('string', $info->image_link);
        $this->assertEquals('https://open.spotify.com/track/7H7T22yvZMLVzJHDONDYDp', $info->link);
        $this->assertEquals('track', $info->type);
    }

    /** @test */
    public function itGetsAlbumInfoById()
    {
        $finder = new SpotifyFinder;

        $info = $finder->music_info_by_id('album', '1M1dhwZE65bqGfbUdMzvlj');

        $this->assertEquals('1M1dhwZE65bqGfbUdMzvlj', $info->id);
        $this->assertEquals('Mellow Gold', $info->album);
        $this->assertEquals(null, $info->track);
        $this->assertEquals('Beck', $info->artist);
        $this->assertInternalType('string', $info->image_link);
        $this->assertEquals('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj', $info->link);
        $this->assertEquals('album', $info->type);
    }

    /** @test */
    public function itHandlesResourcesThatDoNotExist()
    {
        $finder = new SpotifyFinder;

        $info = $finder->music_info_by_id('track', 'doesnotexist');
        $this->assertNull($info);

        $info = $finder->music_info_by_id('album', 'doesnotexist');
        $this->assertNull($info);
    }

    /** @test */
    public function it_searches_for_a_resource_given_a_name_or_artist()
    {
        $finder = new SpotifyFinder;

        $music = new MusicInfo;
        $music->fill([
            'artist' => 'Tobacco',
            'track' => 'Stretch Your Face',
            'type' => 'track',
        ]);

        $result = $finder->search($music);

        $this->assertInstanceOf('\App\MusicInfo', $result);
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $result->id);
        $this->assertEquals('Stretch Your Face', $result->track);
    }

    /** @test */
    public function it_returns_null_if_resource_is_not_found()
    {
        $finder = new SpotifyFinder;

        $music = new MusicInfo;
        $music->fill([
            'artist' => 'noone',
            'track' => 'nothingishere',
            'type' => 'track',
        ]);

        $this->assertNull($finder->search($music));
    }

    /**
     * @test
     *
     * @expectedException  InvalidArgumentException
     */
    public function it_throws_an_exception_if_search_title_not_provided()
    {
        $finder = new SpotifyFinder;

        $music = new MusicInfo;
        $music->fill([
            'artist' => 'noone',
            'type' => 'track',
        ]);

        $finder->search($music);
    }

    /**
     * @test
     *
     * @expectedException  InvalidArgumentException
     */
    public function it_throws_an_exception_if_search_artist_not_provided()
    {
        $finder = new SpotifyFinder;

        $music = new MusicInfo;
        $music->fill([
            'title' => 'nothinghere',
            'type' => 'track',
        ]);

        $finder->search($music);
    }

    /**
     * @test
     *
     * @expectedException  InvalidArgumentException
     */
    public function it_throws_an_exception_if_search_type_not_provided()
    {
        $finder = new SpotifyFinder;

        $music = new MusicInfo;
        $music->fill([
            'artist' => 'noone',
            'title' => 'nothinghere',
        ]);

        $finder->search($music);
    }
}

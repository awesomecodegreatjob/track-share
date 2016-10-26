<?php

use App\MusicInfo;
use App\SpotifyFinder;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Finder\Finder;

class SpotifyFinderTest extends TestCase
{
    // Stretch Your Face by Tobacco
    protected $music_uri_1 = 'spotify:track:7H7T22yvZMLVzJHDONDYDp';

    // Mellow Gold by Beck
    protected $music_uri_2 = 'https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj';

    /**
     * @test
     */
    public function it_validates_resource_URIs()
    {
        $finder = new SpotifyFinder;

        $this->assertTrue($finder->matches($this->music_uri_1));
        $this->assertTrue($finder->matches($this->music_uri_2));

        $this->assertFalse($finder->matches('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }

    /**
     * @test
     */
    public function it_extracts_a_resource_id_from_valid_URIs()
    {
        $finder = new SpotifyFinder;

        $this->assertEquals('track', $finder->music_id($this->music_uri_1)[0]);
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $finder->music_id($this->music_uri_1)[1]);

        $this->assertEquals('album', $finder->music_id($this->music_uri_2)[0]);
        $this->assertEquals('1M1dhwZE65bqGfbUdMzvlj', $finder->music_id($this->music_uri_2)[1]);

        $this->assertFalse($finder->music_id('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }

    /**
     * @test
     */
    public function it_gets_music_information_by_resource_type_and_id()
    {
        $finder = new SpotifyFinder;

        $resource = $finder->music_id($this->music_uri_1);
        $info = $finder->music_info_by_id($resource[0], $resource[1]);

        $this->assertEquals($resource[1], $info->id);
        $this->assertEquals('Stretch Your Face', $info->title);
        $this->assertEquals('Tobacco', $info->artist);
        $this->assertInternalType('string', $info->image_link);
        $this->assertEquals('https://open.spotify.com/track/' . $resource[1], $info->link);
        $this->assertEquals($resource[0], $info->type);


        $resource = $finder->music_id($this->music_uri_2);
        $info = $finder->music_info_by_id($resource[0], $resource[1]);

        $this->assertEquals($resource[1], $info->id);
        $this->assertEquals('Mellow Gold', $info->title);
        $this->assertEquals('Beck', $info->artist);
        $this->assertInternalType('string', $info->image_link);
        $this->assertEquals('https://open.spotify.com/album/' . $resource[1], $info->link);
        $this->assertEquals($resource[0], $info->type);

        $info = $finder->music_info_by_id('track', 'doesnotexist');
        $this->assertNull($info);

        $info = $finder->music_info_by_id('album', 'doesnotexist');
        $this->assertNull($info);
    }

    /**
     * @test
     */
    public function it_searches_for_a_resource_given_a_name_or_artist()
    {
        $finder = new SpotifyFinder;

        $music = new MusicInfo;
        $music->fill([
            'artist' => 'Tobacco',
            'title' => 'Stretch Your Face',
            'type' => 'track',
        ]);

        $result = $finder->search($music);

        $this->assertInstanceOf('\App\MusicInfo', $result);
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $result->id);
        $this->assertEquals('Stretch Your Face', $result->title);
    }

    /**
     * @test
     */
    public function it_returns_null_if_resource_is_not_found()
    {
        $finder = new SpotifyFinder;

        $music = new MusicInfo;
        $music->fill([
            'artist' => 'noone',
            'title' => 'nothingishere',
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

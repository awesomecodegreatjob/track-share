<?php

namespace Test\Unit;

use App\MusicInfo;
use App\MusicSeed;
use Tests\TestCase;
use App\GoogleMusicFinder;

class GoogleMusicFinderTest extends TestCase
{
    protected $music_uri_1 = 'https://play.google.com/music/m/Toknxdtl3kx7askzrt7byiusbdm?t=Everything_in_Its_Right_Place_-_Radiohead';
    protected $music_uri_2 = 'https://play.google.com/music/m/B5c5kftiwy4a3zind5r4ip6uepm?t=II_-_Unknown_Mortal_Orchestra';

    // Problems
    protected $problem1 = 'https://play.google.com/music/m/Atxifkhyporotholngkhu6irlra?t=Mimicking_Birds';

    /** @test */
    public function itValidatesResourceUris()
    {
        $finder = $this->getGoogleMusicFinder();

        $this->assertTrue($finder->matches($this->music_uri_1));
        $this->assertTrue($finder->matches($this->music_uri_2));

        $this->assertFalse($finder->matches('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj'));
    }

    /** @test */
    public function itGetsTrackInfoById()
    {
        $finder = $this->getGoogleMusicFinder();

        $info = $finder->fetchMusicInfo('Toknxdtl3kx7askzrt7byiusbdm');

        $result = $info->get();

        $this->assertEquals('Everything In Its Right Place', $result->getTrack());
        $this->assertEquals('Radiohead', $result->getArtist());
        $this->assertEquals('Kid A', $result->getAlbum());
        $this->assertInternalType('string', $result->getImageUrl());
        $this->assertInternalType('string', $result->getLink());
        $this->assertEquals('track', $result->getType());
    }

    /** @test */
    public function itGetsAlbumInfoById() {
        $finder = $this->getGoogleMusicFinder();

        $info = $finder->fetchMusicInfo('B5c5kftiwy4a3zind5r4ip6uepm');

        $result = $info->get();

        $this->assertEquals(null, $result->getTrack());
        $this->assertEquals('Unknown Mortal Orchestra', $result->getArtist());
        $this->assertEquals('II', $result->getAlbum());
        $this->assertInternalType('string', $result->getImageUrl());
        $this->assertInternalType('string', $result->getLink());
        $this->assertEquals('album', $result->getType());

        $info = $finder->fetchMusicInfo('Blymcq6c7hwxpt5whoernti6nma');

        $result = $info->get();

        $this->assertEquals('Mimicking Birds', $result->getArtist());
    }

    /** @test */
    public function itSearchesForTracks()
    {
        $f = $this->getGoogleMusicFinder();

        $i = new MusicInfo(
            'spotify',
            'xxx',
            'Maniac Meat',
            'Stretch Your Face',
            'track',
            'Tobacco',
            'http://foo.bar/xxx',
            'http://foo.bar/xxx.jpg'
        );

        $r = $f->search($i);

        $expected = [ 'artist' => 'TOBACCO', 'type' => 'track', 'track' => 'Stretch Your Face' ];
        $this->assertArraySubset($expected, $r->get()->toArray());
    }

    /** @test  */
    public function itRetrievesInfoFromATrackSeed()
    {
        $finder = $this->getGoogleMusicFinder();

        $seed = new MusicSeed('googleMusic', 'track', 'Toknxdtl3kx7askzrt7byiusbdm');

        $result = $finder->musicInfoFromSeed($seed);

        $this->assertArraySubset([
            'service' => 'googleMusic',
            'type' => 'track',
            'id' => 'Toknxdtl3kx7askzrt7byiusbdm',
            'artist' => 'Radiohead',
            'track' => 'Everything In Its Right Place',
            'album' => 'Kid A',
        ], $result->get()->toArray());
    }

    /** @test  */
    public function itRetrievesInfoFromAnAlbumSeed()
    {
        $finder = $this->getGoogleMusicFinder();

        $seed = new MusicSeed('googleMusic', 'album', 'B5c5kftiwy4a3zind5r4ip6uepm');

        $result = $finder->musicInfoFromSeed($seed);

        $this->assertArraySubset([
            'service' => 'googleMusic',
            'type' => 'album',
            'id' => 'B5c5kftiwy4a3zind5r4ip6uepm',
            'artist' => 'Unknown Mortal Orchestra',
            'track' => '',
            'album' => 'II',
        ], $result->get()->toArray());
    }

    /** @test  */
    public function itIsEmptyIfGivenASeedWithAnInvalidId()
    {
        $finder = $this->getGoogleMusicFinder();

        $seed = new MusicSeed('googleMusic', 'album', 'nopenopenope');

        $result = $finder->musicInfoFromSeed($seed);

        $this->assertTrue($result->isEmpty());
    }

    /** @test  */
    public function itIsEmptyIfGivenASeedWithAnInvalidService()
    {
        $finder = $this->getGoogleMusicFinder();

        $seed = new MusicSeed('nope', 'album', 'B5c5kftiwy4a3zind5r4ip6uepm');

        $result = $finder->musicInfoFromSeed($seed);

        $this->assertTrue($result->isEmpty());
    }

    /** @test  */
    public function itRetrievesInfoFromATrackUri()
    {
        $finder = $this->getGoogleMusicFinder();

        $result = $finder->musicInfoFromUri('https://play.google.com/music/m/Toknxdtl3kx7askzrt7byiusbdm');

        $this->assertArraySubset([
            'service' => 'googleMusic',
            'type' => 'track',
            'id' => 'Toknxdtl3kx7askzrt7byiusbdm',
            'artist' => 'Radiohead',
            'track' => 'Everything In Its Right Place',
            'album' => 'Kid A',
        ], $result->get()->toArray());
    }

    /** @test  */
    public function itRetrievesInfoFromAnAlbumUri()
    {
        $finder = $this->getGoogleMusicFinder();

        $result = $finder->musicInfoFromUri('https://play.google.com/music/m/B5c5kftiwy4a3zind5r4ip6uepm');

        $this->assertArraySubset([
            'service' => 'googleMusic',
            'type' => 'album',
            'id' => 'B5c5kftiwy4a3zind5r4ip6uepm',
            'artist' => 'Unknown Mortal Orchestra',
            'track' => '',
            'album' => 'II',
        ], $result->get()->toArray());
    }

    /** @test  */
    public function itRetrievesEmptyResultForAnInvalidUri()
    {
        $finder = $this->getGoogleMusicFinder();

        $result = $finder->musicInfoFromUri('https://foomuzak.io/bar');

        $this->assertTrue($result->isEmpty());
    }

    protected function getGoogleMusicFinder() : GoogleMusicFinder
    {
        return new GoogleMusicFinder('googleMusic');
    }
}


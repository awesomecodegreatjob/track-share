<?php

namespace Tests\Unit;

use App\MusicInfo;
use App\MusicSeed;
use PhpSlang\Either\Either;
use PhpSlang\Option\Option;
use Tests\TestCase;
use Prophecy\Prophet;
use App\SpotifyFinder;
use App\MusicSeed\Result;
use App\MusicSeed\ResultOk;
use App\MusicSeed\ResultError;
use App\Spotify\Client\TrackQuery;
use App\Spotify\Contracts\ApiConnection;

class SpotifyFinderTest extends TestCase
{
    /** @test */
    public function itValidatesUris()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $this->assertTrue($finder->matches('spotify:track:7H7T22yvZMLVzJHDONDYDp'));
        $this->assertTrue($finder->matches('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj'));

        $this->assertFalse($finder->matches('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }

    /** @test */
    public function itTranslatesAValidTrackUriToASeed()
    {
        $uri = 'spotify:track:7H7T22yvZMLVzJHDONDYDp';

        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $spotifyApiConnection
            ->request('GET', 'v1/tracks/7H7T22yvZMLVzJHDONDYDp')
            ->willReturn([
                'id' => '7H7T22yvZMLVzJHDONDYDp',
                'album' => [
                    'name' => 'Foo',
                ],
            ]);

        $resultSeed = $finder->musicInfoFromUri($uri);

        $this->assertEquals('track', $resultSeed->get()->getType());
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $resultSeed->get()->getId());
        $this->assertEquals('spotify', $resultSeed->get()->getService());
    }

    /** @test */
    public function itTranslatesAValidAlbumUriToASeed()
    {
        $uri = 'spotify:album:album123';

        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri($uri);

        $spotifyApiConnection
            ->request('GET', 'v1/album/album123')
            ->willReturn([
                'id' => 'album123',
                'album' => [
                    'name' => 'Foo',
                ],
            ]);

        $this->assertEquals('album', $resultSeed->get()->getType());
        $this->assertEquals('album123', $resultSeed->get()->getId());
        $this->assertEquals('spotify', $resultSeed->get()->getService());
    }

    /** @test */
    public function itTranslatesAValidTrackWebUrlToASeed()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('https://open.spotify.com/track/trackabc');

        $this->assertEquals('track', $resultSeed->get()->getType());
        $this->assertEquals('trackabc', $resultSeed->get()->getId());
        $this->assertEquals('spotify', $resultSeed->get()->getService());
    }

    /** @test */
    public function itTranslatesAValidAlbumWebUrlToASeed()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('https://open.spotify.com/album/album123');

        $this->assertEquals('album', $resultSeed->get()->getType());
        $this->assertEquals('album123', $resultSeed->get()->getId());
        $this->assertEquals('spotify', $resultSeed->get()->getService());
    }

    /** @test */
    public function itTranslatesAnInvalidTrackUriToAnError()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('https://open.musicz.com/album/album123');

        $this->assertTrue($resultSeed->isEmpty());
        $this->assertTrue($resultSeed->isEmpty());
        $this->assertTrue($resultSeed->isEmpty());
    }

    /** @test */
    public function itGetsTrackInfoByMusicSeed()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();

        $spotifyApiConnection
            ->request('GET', 'v1/tracks/7H7T22yvZMLVzJHDONDYDp')
            ->willReturn(
                json_decode($this->trackIdResultForStretchYourFace(), true)
            );

        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $seed = new MusicSeed('spotify', 'track', '7H7T22yvZMLVzJHDONDYDp');

        $musicInfoResult = $finder->musicInfoFromSeed($seed);

        $this->assertEquals('Tobacco', $musicInfoResult->get()->getArtist());
    }

    /** @test */
    public function itGetsTrackInfoById()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $spotifyApiConnection
            ->request('GET', 'v1/tracks/7H7T22yvZMLVzJHDONDYDp')
            ->willReturn(
                json_decode($this->trackIdResultForStretchYourFace(), true)
            );

        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $result = $finder->musicInfoById('track', '7H7T22yvZMLVzJHDONDYDp')->get();

        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $result->getId());
        $this->assertEquals('Stretch Your Face', $result->getTrack());
        $this->assertEquals('Maniac Meat', $result->getAlbum());
        $this->assertEquals('Tobacco', $result->getArtist());
        $this->assertInternalType('string', $result->getImageUrl());
        $this->assertEquals('https://open.spotify.com/track/7H7T22yvZMLVzJHDONDYDp', $result->getLink());
        $this->assertEquals('track', $result->getType());
    }

    /** @test */
    public function itGetsAlbumInfoById()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();

        $spotifyApiConnection
            ->request('GET', 'v1/albums/1M1dhwZE65bqGfbUdMzvlj')
            ->willReturn(
                json_decode($this->albumIdResultForMellowGold(), true)
            );

        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $result = $finder->musicInfoById('album', '1M1dhwZE65bqGfbUdMzvlj')->get();

        $this->assertInstanceOf(MusicInfo::class, $result);
        $this->assertEquals('1M1dhwZE65bqGfbUdMzvlj', $result->getId());
        $this->assertEquals('Mellow Gold', $result->getAlbum());
        $this->assertEquals(null, $result->getTrack());
        $this->assertEquals('Beck', $result->getArtist());
        $this->assertInternalType('string', $result->getImageUrl());
        $this->assertEquals('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj', $result->getLink());
        $this->assertEquals('album', $result->getType());
    }

    /** @test */
    public function itHandlesResourcesThatDoNotExist()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();

        $spotifyApiConnection
            ->request('GET', 'v1/tracks/doesnotexist')
            ->willReturn(
                json_decode($this->albumIdResultNotFound(), true)
            );

        $spotifyApiConnection
            ->request('GET', 'v1/albums/doesnotexist')
            ->willReturn(
                json_decode($this->albumIdResultNotFound(), true)
            );

        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $result = $finder->musicInfoById('track', 'doesnotexist');
        $this->assertTrue($result->isEmpty());

        $result = $finder->musicInfoById('album', 'doesnotexist');
        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function itSearchesForAResourceGivenAnAlbumName()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();

        $spotifyApiConnection
            ->request('GET', 'v1/search', [
                'q' => 'artist:Oh Sees album:Smote Reverser',
                'type' => 'album',
                'limit' => 1,
            ])
            ->willReturn(
                json_decode($this->albumResultForSmoteReverser(), true)
            );

        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $result = $finder->search(new MusicInfo(
            'foo',
            'testid',
            'Smote Reverser',
            '',
            'album',
            'Oh Sees',
            'http://foo.bar',
            'http://foo.bar/img_foo.jpg'
        ));

        $this->assertArraySubset([
            'album' => 'Smote Reverser',
            'track' => '',
            'type' => 'album',
        ], $result->get()->toArray());
    }

    /** @test */
    public function itSearchesForAResourceGivenATrackName()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();

        $spotifyApiConnection
            ->request('GET', 'v1/search', [
                'q' => 'artist:Tobacco track:Stretch Your Face',
                'type' => 'track',
                'limit' => 1,
            ])
            ->willReturn(
                json_decode($this->trackResultForStretchYourFace(), true)
            );

        $finder = new SpotifyFinder('spotify', $spotifyApiConnection->reveal());

        $result = $finder->search(new MusicInfo(
            'foo',
            'testid',
            'Maniac Meat',
            'Stretch Your Face',
            'track',
            'Tobacco',
            'http://foo.bar',
            'http://foo.bar/img_foo.jpg'
        ));

        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $result->get()->getId());
        $this->assertEquals('Stretch Your Face', $result->get()->getTrack());
    }

    /** @test */
    public function itReturnsAnEmptyResponseIfResultNotFound()
    {
        $conn = $this->getMockedSpotifyConnection();

        $conn
            ->request('GET', 'v1/search', [
                'q' => 'artist:noone track:nothinghere',
                'type' => 'track',
                'limit' => 1,
            ])
            ->willReturn(
                json_decode($this->trackResultEmpty(), true)
            );

        $finder = new SpotifyFinder('spotify', $conn->reveal());

        $result = $finder->search(new MusicInfo(
            'foo',
            'testid',
            'Maniac Meat',
            'nothinghere',
            'track',
            'noone',
            'http://foo.bar',
            'http://foo.bar/img_foo.jpg'
        ));

        $this->assertTrue($result->isEmpty());
    }

    private function getMockedSpotifyConnection()
    {
        $prophet = new Prophet;
        $spotifyApiConnection = $prophet->prophesize(ApiConnection::class);

        return $spotifyApiConnection;
    }

    private function albumIdResultNotFound()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/resultNotFound.json');
    }

    private function albumIdResultForMellowGold()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/getMellowGold.json');
    }

    private function trackIdResultForStretchYourFace()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/getStretchYourFace.json');
    }

    private function albumResultForSmoteReverser()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/searchForSmoteReverser.json');
    }

    private function trackResultForStretchYourFace()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/searchForStretchYourFace.json');
    }

    private function trackResultEmpty()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/searchResultEmpty.json');
    }
}


<?php

namespace Tests\Unit;

use App\MusicInfo;
use App\MusicSeed\MusicSeed;
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
        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $this->assertTrue($finder->matches('spotify:track:7H7T22yvZMLVzJHDONDYDp'));
        $this->assertTrue($finder->matches('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj'));

        $this->assertFalse($finder->matches('https://play.google.com/music/m/Tpwsuxmzwi2x7se2jdmtwmxnc3q'));
    }

    /** @test */
    public function itTranslatesAValidTrackUriToASeed()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('spotify:track:7H7T22yvZMLVzJHDONDYDp');

        $this->assertInstanceOf(Result::class, $resultSeed);
        $this->assertInstanceOf(ResultOk::class, $resultSeed);

        $this->assertEquals('track', $resultSeed->getType());
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $resultSeed->getId());
        $this->assertEquals('spotify', $resultSeed->getService());
    }

    /** @test */
    public function itTranslatesAValidAlbumUriToASeed()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('spotify:album:album123');

        $this->assertInstanceOf(Result::class, $resultSeed);
        $this->assertInstanceOf(ResultOk::class, $resultSeed);

        $this->assertEquals('album', $resultSeed->getType());
        $this->assertEquals('album123', $resultSeed->getId());
        $this->assertEquals('spotify', $resultSeed->getService());
    }

    /** @test */
    public function itTranslatesAValidTrackWebUrlToASeed()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('https://open.spotify.com/track/trackabc');

        $this->assertInstanceOf(Result::class, $resultSeed);
        $this->assertInstanceOf(ResultOk::class, $resultSeed);

        $this->assertEquals('track', $resultSeed->getType());
        $this->assertEquals('trackabc', $resultSeed->getId());
        $this->assertEquals('spotify', $resultSeed->getService());
    }

    /** @test */
    public function itTranslatesAValidAlbumWebUrlToASeed()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('https://open.spotify.com/album/album123');

        $this->assertInstanceOf(Result::class, $resultSeed);
        $this->assertInstanceOf(ResultOk::class, $resultSeed);

        $this->assertEquals('album', $resultSeed->getType());
        $this->assertEquals('album123', $resultSeed->getId());
        $this->assertEquals('spotify', $resultSeed->getService());
    }

    /** @test */
    public function itTranslatesAnInvalidTrackUriToAnError()
    {
        $spotifyApiConnection = $this->getMockedSpotifyConnection();
        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $resultSeed = $finder->musicSeedFromUri('https://open.musicz.com/album/album123');

        $this->assertInstanceOf(Result::class, $resultSeed);
        $this->assertInstanceOf(ResultError::class, $resultSeed);

        $this->assertEquals('Invalid music seed.', $resultSeed->getType());
        $this->assertEquals('Invalid music seed.', $resultSeed->getId());
        $this->assertEquals('Invalid music seed.', $resultSeed->getService());
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

        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $seed = MusicSeed::ok('spotify', 'track', '7H7T22yvZMLVzJHDONDYDp');

        $musicInfoResult = $finder->musicInfoFromSeed($seed);

        $response = $musicInfoResult->resolve();

        $this->assertInstanceOf(MusicInfo::class, $response);

        $this->assertEquals('Tobacco', $response->getArtist());
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

        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $result = $finder->musicInfoById('track', '7H7T22yvZMLVzJHDONDYDp');

        $response = $result->resolve();

        $this->assertInstanceOf(MusicInfo::class, $response);
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $response->getId());
        $this->assertEquals('Stretch Your Face', $response->getTrack());
        $this->assertEquals('Maniac Meat', $response->getAlbum());
        $this->assertEquals('Tobacco', $response->getArtist());
        $this->assertInternalType('string', $response->getImageUrl());
        $this->assertEquals('https://open.spotify.com/track/7H7T22yvZMLVzJHDONDYDp', $response->getLink());
        $this->assertEquals('track', $response->getType());
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

        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $result = $finder->musicInfoById('album', '1M1dhwZE65bqGfbUdMzvlj');

        $response = $result->resolve();

        $this->assertInstanceOf(MusicInfo::class, $response);
        $this->assertEquals('1M1dhwZE65bqGfbUdMzvlj', $response->getId());
        $this->assertEquals('Mellow Gold', $response->getAlbum());
        $this->assertEquals(null, $response->getTrack());
        $this->assertEquals('Beck', $response->getArtist());
        $this->assertInternalType('string', $response->getImageUrl());
        $this->assertEquals('https://open.spotify.com/album/1M1dhwZE65bqGfbUdMzvlj', $response->getLink());
        $this->assertEquals('album', $response->getType());
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

        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $result = $finder->musicInfoById('track', 'doesnotexist');
        $response = $result->resolve();
        $this->assertNull($response);

        $result = $finder->musicInfoById('album', 'doesnotexist');
        $response = $result->resolve();
        $this->assertNull($response);
    }

    /** @test */
    public function itSearchesForAResourceGivenAnAlbumOrTrackName()
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

        $finder = new SpotifyFinder($spotifyApiConnection->reveal());

        $result = $finder->search(new TrackQuery('Stretch Your Face', 'Tobacco'));

        $response = $result->resolve();

        $this->assertInstanceOf(MusicInfo::class, $response);
        $this->assertEquals('7H7T22yvZMLVzJHDONDYDp', $response->getId());
        $this->assertEquals('Stretch Your Face', $response->getTrack());
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

        $finder = new SpotifyFinder($conn->reveal());

        $result = $finder->search(new TrackQuery('nothinghere', 'noone'));

        $response = $result->resolve();

        $this->assertNull($response);
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

    private function trackResultForStretchYourFace()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/searchForStretchYourFace.json');
    }

    private function trackResultEmpty()
    {
        return file_get_contents(__DIR__.'/../mocks/spotifyApi/searchResultEmpty.json');
    }
}


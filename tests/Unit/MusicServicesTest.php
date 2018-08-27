<?php

namespace Tests\Unit;

use App\MusicInfo;
use App\MusicSeed;
use App\MusicService;
use PhpSlang\Option\Option;
use Prophecy\Argument;
use Prophecy\Prophet;
use Tests\TestCase;
use App\MusicServices;
use App\SpotifyFinder;
use App\GoogleMusicFinder;

class MusicServicesTest extends TestCase
{
    /** @var Prophet */
    protected $prophet;

    public function setUp()
    {
        $this->prophet = new Prophet;

        parent::setUp();
    }

    /** @test */
    public function itFetchesAListOfMusicProviders()
    {
//        config([ 'musicServices' => [
//            '' => GoogleMusicFinder::class,
//            SpotifyFinder::class,
//        ]]);

        $musicServices = (new MusicServices)->all();

        $this->assertCount(2, $musicServices);
        $this->assertInstanceOf(GoogleMusicFinder::class, $musicServices['googleMusic']);
        $this->assertInstanceOf(SpotifyFinder::class, $musicServices['spotify']);
    }

    /** @test */
    public function itFetchesAllButGivenService()
    {
        $notSpotify = (new MusicServices)->except('spotify');

        $this->assertCount(1, $notSpotify);
    }

    protected function getMockMusicService(
        string $id,
        string $uri,
        bool $isMatch,
        ?MusicSeed $seedFromUri,
        ?MusicInfo $infoFromSearch) : MusicService
    {
        $svc = $this->prophet->prophesize(MusicService::class);

        $svc->getId()->willReturn($id);
        $svc->matches($uri)->willReturn($isMatch);
        $svc->musicSeedFromUri($uri)->willReturn(Option::of($seedFromUri));
        $svc->search(Argument::type(MusicInfo::class))->willReturn(Option::of($infoFromSearch));

        return $svc->reveal();
    }
}

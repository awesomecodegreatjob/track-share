<?php

namespace Tests\Unit;

use App\MusicInfo;
use App\MusicSeed;
use Tests\TestCase;
use App\MusicFinder;
use App\MusicService;
use Prophecy\Prophet;
use App\MusicServices;
use Prophecy\Argument;
use PhpSlang\Option\Option;

class MusicFinderTest extends TestCase
{
    /** @var Prophet */
    protected $prophet;

    public function setUp()
    {
        $this->prophet = new Prophet();
        parent::setUp();
    }

    /** @test */
    public function itFindsTheMusicUriOrigin()
    {
        $uri = 'muzak:album:100';

        $svc1 = $this->getMockMusicService('svc1', $uri, false, null, null);
        $svc2 = $this->getMockMusicService('svc2', $uri, true, new MusicSeed('muzak', 'album', '100'), null);

        $this->mockMusicServices([ $svc1, $svc2 ]);

        /** @var MusicFinder $finder */
        $finder = app(MusicFinder::class);
        $origin = $finder->findOriginFor($uri);

        $this->assertEquals($svc2, $origin->get());
    }

    /** @test */
    public function itTakesAnInvalidUriAndReturnsAnEmptyOption()
    {
        $uri = 'muzak:album:100';

        $svc1 = $this->getMockMusicService('svc1', $uri, false, null, null);
        $svc2 = $this->getMockMusicService('svc2', $uri, false, null, null);

        $this->mockMusicServices([ $svc1, $svc2 ]);

        /** @var MusicFinder $finder */
        $finder = app(MusicFinder::class);
        $response = $finder->getSeedFromUri($uri);

        $this->assertTrue($response->isEmpty());
    }

    /** @test */
    public function itTakesAValidUriAndCreatesASeed()
    {
        $uri = 'muzak:album:100';
        $expectedSeed = new MusicSeed('svc2', 'album', '100');

        $svc1 = $this->getMockMusicService('svc1', $uri, false, null, null);
        $svc2 = $this->getMockMusicService('svc2', $uri, true, $expectedSeed, null);

        $this->mockMusicServices([ $svc1, $svc2 ]);

        /** @var MusicFinder $finder */
        $finder = app(MusicFinder::class);
        $response = $finder->getSeedFromUri($uri);

        $this->assertEquals($expectedSeed, $response->get());
    }

    /** @test */
    public function itTakesAnMissingUriAndReturnsAnEmptyOption()
    {
        $uri = 'muzak:album:100';

        $svc1 = $this->getMockMusicService('svc1', $uri, false, null, null);
        $svc2 = $this->getMockMusicService('svc2', $uri, true, null, null);

        $this->mockMusicServices([ $svc1, $svc2 ]);

        /** @var MusicFinder $finder */
        $finder = app(MusicFinder::class);
        $response = $finder->getSeedFromUri($uri);

        $this->assertTrue($response->isEmpty());
    }

    /** @test */
    public function itTakesMusicInfoAndGathersInfoFromOtherServices()
    {
        $svcOneInfo = new MusicInfo(
            'svc1',
            '1234',
            'Foo Album',
            '',
            'album',
            'The Bars',
            'http://test.foo',
            'http://test.foo/foo_album.jpg'
        );

        $uri = 'muzak:album:100';

        $svc1 = $this->getMockMusicService('svc1', $uri, false, null, $svcOneInfo);
        $svc2 = $this->getMockMusicService('svc2', $uri, true, null, null);

        $this->mockMusicServices([ $svc1, $svc2 ]);

        /** @var MusicFinder $finder */
        $finder = app(MusicFinder::class);
        $infoCollection = $finder->collectMatchingInfo($svcOneInfo);

        $this->assertCount(2, $infoCollection);
        $this->assertEquals('http://test.foo', $infoCollection['svc1']->getLink());
        $this->assertEquals('', $infoCollection['svc2']->getLink());
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

    protected function mockMusicServices(array $services)
    {
        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->all()->willReturn(collect($services));

        app()->bind(MusicServices::class, function () use ($musicServices) {
            return $musicServices->reveal();
        });
    }
}

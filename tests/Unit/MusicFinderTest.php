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

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->all()->willReturn(collect([ $svc1, $svc2 ]));

        $finder = new MusicFinder($musicServices->reveal());

        $origin = $finder->findOriginFor($uri);

        $this->assertEquals($svc2, $origin->get());
    }

    /** @test */
    public function itTakesAnInvalidUriAndReturnsAnEmptyOption()
    {
        $uri = 'muzak:album:100';

        $svc1 = $this->getMockMusicService('svc1', $uri, false, null, null);
        $svc2 = $this->getMockMusicService('svc2', $uri, false, null, null);

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->all()->willReturn(collect([ $svc1, $svc2 ]));

        $finder = new MusicFinder($musicServices->reveal());

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

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->all()->willReturn(collect([ $svc1, $svc2 ]));

        $finder = new MusicFinder($musicServices->reveal());

        $response = $finder->getSeedFromUri($uri);

        $this->assertEquals($expectedSeed, $response->get());
    }

    /** @test */
    public function itTakesAnMissingUriAndReturnsAnEmptyOption()
    {
        $uri = 'muzak:album:100';

        $svc1 = $this->getMockMusicService('svc1', $uri, false, null, null);
        $svc2 = $this->getMockMusicService('svc2', $uri, true, null, null);

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->all()->willReturn(collect([ $svc1, $svc2 ]));

        $finder = new MusicFinder($musicServices->reveal());
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

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->all()->willReturn(collect([ $svc1, $svc2 ]));

        $finder = new MusicFinder($musicServices->reveal());
        $infoCollection = $finder->collectMatchingInfo($svcOneInfo);

        $this->assertCount(2, $infoCollection);
        $this->assertEquals('http://test.foo', $infoCollection['svc1']->getLink());
        $this->assertEquals('', $infoCollection['svc2']->getLink());
    }

    /** @test */
    public function itRetrievesMusicInfoFromSeed()
    {
        $seed = new MusicSeed('svc1', 'album', '100');

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

        $svc1 = $this->prophet->prophesize(MusicService::class);
        $svc1->musicInfoFromSeed($seed)->willReturn(Option::of($svcOneInfo));

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->find('svc1')->willReturn(Option::of($svc1->reveal()));

        $finder = new MusicFinder($musicServices->reveal());

        $retrieved = $finder->getMusicInfoFromSeed($seed);

        $this->assertEquals($svcOneInfo, $retrieved->get());
    }

    /** @test */
    public function itReturnsEmptyResultWhenInvalidServiceUsed()
    {
        $svc1 = $this->prophet->prophesize(MusicService::class);
        $svc1->musicInfoFromSeed(Argument::any())->willReturn('invalid');

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->find('svc1')->willReturn(Option::of(null));

        $finder = new MusicFinder($musicServices->reveal());

        $retrieved = $finder->getMusicInfoFromSeed(new MusicSeed('svc1', 'album', '100'));

        $this->assertTrue($retrieved->isEmpty());
    }

    /** @test */
    public function itReturnsEmptyResultWhenInvalidResourceRequested()
    {
        $seed = new MusicSeed('svc1', 'album', '100');

        $svc1 = $this->prophet->prophesize(MusicService::class);
        $svc1->musicInfoFromSeed($seed)->willReturn(Option::of(null));

        $musicServices = $this->prophet->prophesize(MusicServices::class);
        $musicServices->find('svc1')->willReturn(Option::of($svc1->reveal()));

        $finder = new MusicFinder($musicServices->reveal());

        $retrieved = $finder->getMusicInfoFromSeed($seed);

        $this->assertTrue($retrieved->isEmpty());
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

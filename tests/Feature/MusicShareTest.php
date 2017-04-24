<?php

namespace tests\Feature;

use App\Music;
use Test\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class MusicShareTest extends TestCase
{
    use DatabaseMigrations, WithoutMiddleware;

    /** @test */
    public function itTakesAGoogleMusicShareUrlAndReturnsAShareablePage()
    {
        // Thee Oh Sees - Floating Coffin
        $url = 'https://play.google.com/music/m/B5uprvhaw6jxqz52yqpcgqax7g4?t=Floating_Coffin_-_Thee_Oh_Sees';

        $this->call('get', '/search', [
            'url' => $url,
        ]);

        $this->assertResponseStatus(302);

        $last_music_key = $this->getLastMusic()->key;

        $this->visit('/m/'.$last_music_key);
        $this->see('Floating Coffin');
        $this->see('Thee Oh Sees');
    }

    /** @test */
    public function itReturnsA404IfMusicLinkDoesNotExist()
    {
        $this->call('get', '/m/not-here');
        $this->assertResponseStatus(404);
    }
}
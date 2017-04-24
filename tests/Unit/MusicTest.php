<?php

namespace tests\Unit;

use App\Music;
use Test\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MusicTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function itInitializesGivenAGoogleMusicShareUrl()
    {
        // Thee Oh Sees - Floating Coffin
        $url = 'https://play.google.com/music/m/B5uprvhaw6jxqz52yqpcgqax7g4?t=Floating_Coffin_-_Thee_Oh_Sees';
        $music = Music::createFromUrl($url);

        $this->assertEquals('Thee Oh Sees', $music->band);
        $this->assertEquals('Floating Coffin', $music->album);
        $this->assertInternalType('string', $music->image_url);
        $this->assertEquals(null, $music->track);

        $this->assertEquals('https://play.google.com/music/m/B5uprvhaw6jxqz52yqpcgqax7g4', $music->google_music_url);
        $this->assertEquals(null, $music->spotify_url);

        $this->assertEquals(Music::KEY_LENGTH, strlen($music->key));
    }

    /** @test */
    public function itInitializesGivenASpotifyShareUri()
    {
        // The Black Angels - Deer-Ree-Shee
        $url = 'spotify:track:3UyT5e1tk3WVVsWBSm9i8L';
        $music = Music::createFromUrl($url);

        $this->assertEquals('The Black Angels', $music->band);
        $this->assertEquals('Directions To See A Ghost', $music->album);
        $this->assertEquals('Deer-Ree-Shee', $music->track);

        $this->assertEquals('https://play.google.com/music/m/Tcfxqwui2xbui7rx5rprylozeki', $music->google_music_url);
        $this->assertEquals('https://open.spotify.com/track/3UyT5e1tk3WVVsWBSm9i8L', $music->spotify_url);

        $this->assertEquals(Music::KEY_LENGTH, strlen($music->key));
    }

    /** @test */
    public function itRedirectsToExistingMusicLinksIfAvailable()
    {
        $url = 'spotify:track:3UyT5e1tk3WVVsWBSm9i8L';
        $music = Music::createFromUrl($url);

        $existing_key = $music->key;

        $music = Music::createFromUrl($url);

        $this->assertEquals($existing_key, $music->key, 'Failed to use existing music key');
    }
}
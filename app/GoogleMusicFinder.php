<?php

namespace App;

use CurlHelper;
use PhpSlang\Option\Option;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class GoogleMusicFinder implements MusicService
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Validates Google Music service URIs
     *
     * @param  string  $uri
     * @return  bool
     */
    public function matches($uri)
    {
        return strpos($uri, 'play.google.com') !== false;
    }

    /**
     * @param  MusicInfo  $info
     * @return  Option<MusicInfo>
     */
    public function search(MusicInfo $info) : Option
    {
        if($info->isTrack()) {
            $query = sprintf('%s by %s from %s', $info->getTrack(), $info->getArtist(), $info->getAlbum());
        } else {
            $query = sprintf('%s by %s album', $info->getAlbum(), $info->getArtist());
        }

        $uri = sprintf('https://play.google.com/store/search?q=%s&c=music', str_replace(' ', '%20', $query));

        $response = CurlHelper::factory($uri)->exec();

        $response_body = $response['content'];

        // Initialize crawler
        $crawler = new Crawler($response_body);

        // Get Music ID
        $search_result = $crawler->filter('[data-docid]');
        $music_info = explode('-', $search_result->attr('data-docid'));

        // Get Album Art
        $el_image = $search_result->filter('img.cover-image');
        $image = $el_image->attr('data-cover-large');

        // request jpeg instead of webm image
        $image = $this->cleanseImageUrl($image);

        // Get Title
        $el_title = $search_result->filter('a.title');
        $title = $el_title->attr('title');

        // Get Artist
        $el_artist = $search_result->filter('a.subtitle');
        $artist = $el_artist->attr('title');

        $music_type = $music_info[0];
        if($music_type == 'song') {
            $music_type = 'track';
            $track = $title;
        } else {
            $track = '';
        }

        $id = $music_info[1];

        $link = sprintf('https://play.google.com/music/m/%s', $id);

        $info = new MusicInfo(
            $this->getId(),
            $music_info[1],
            $info->getAlbum(),
            $track,
            $music_type,
            $artist,
            $link,
            $image
        );

        return Option::of($info);
    }

    /**
     * @param  string  $uri
     * @return  Option<MusicInfo>
     */
    public function musicInfoFromUri(string $uri) : Option
    {
        return $this
            ->musicSeedFromUri($uri)
            ->map(function (MusicSeed $seed) {
                return $this->musicInfoFromSeed($seed);
            })
            ->getOrElse(Option::of(null));
    }

    /**
     * @param  MusicSeed  $seed
     * @return  Option<MusicInfo>
     */
    public function musicInfoFromSeed(MusicSeed $seed): Option
    {
        if ($seed->getService() !== $this->getId()) {
            return Option::of(null);
        }

        return $this->fetchMusicInfo(
            $seed->getId()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $uri
     * @return Option<MusicSeed>
     */
    public function musicSeedFromUri(string $uri): Option
    {
        return $this
            ->musicIdFromUri($uri)
            ->map(function (string $id) {
                return new MusicSeed($this->getId(), '', $id);
            });
    }

    /**
     * @param  string  $id
     * @return  Option<MusicInfo>
     */
    public function fetchMusicInfo(string $id) : Option
    {
        $uri = sprintf("https://play.google.com/music/preview/%s", $id);

        $response = CurlHelper::factory($uri)
            ->setGetParams([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
            ])
            ->exec();

        $response_body = $response['content'];

        // Initialize crawler
        $crawler = new Crawler($response_body);

        try {
            $track = $crawler->filter('body > [itemscope][itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] > [itemprop="name"]')->text();
            $artist = $crawler->filter('body > [itemscope][itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] [itemprop="byArtist"]')->text();
            $album = $crawler->filter('body > [itemscope][itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] [itemprop="inAlbum"]')->text();
            $image = $crawler->filter('body > [itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] [itemprop="image"]')->attr('href');

            $type = 'track';
        } catch(InvalidArgumentException $e) {

            try {
                // Album was shared
                $track = '';
                $album = $crawler->filter('body > [itemtype="http://schema.org/MusicAlbum/PlayMusicAlbum"] [itemprop="name"]')->text();
                $artist = $crawler->filter('body > [itemtype="http://schema.org/MusicAlbum/PlayMusicAlbum"] [itemprop="byArtist"]')->text();
                $image = $crawler->filter('body > [itemtype="http://schema.org/MusicAlbum/PlayMusicAlbum"] [itemprop="image"]')->attr('href');

                $type = 'album';
            } catch (InvalidArgumentException $e) {
                return Option::of(null);
            }
        }

        $link = sprintf('https://play.google.com/music/m/%s', $id);

        return Option::of(new MusicInfo(
            $this->getId(),
            $id,
            $album,
            $track,
            $type,
            $artist,
            $link,
            $image
        ));
    }

    /**
     * Takes a Google Music resource URI and returns the resource ID, if successful
     *
     * @param  string  $uri
     *
     * @return  Option<string>
     */
    private function musicIdFromUri($uri) : Option
    {
        $isMatch = preg_match("/play.google.com\/music\/m\/(\w+)/", $uri, $matches);
        if(! $isMatch) {
            $isMatch = preg_match("/play\.google\.com\/music\/listen\?.*album\/(\w+)/", $uri, $matches);
        }

        if($isMatch === 1) {
            return Option::of($matches[1]);
        }

        return Option::of(null);
    }

    /**
     * @param string $url
     * @return string
     */
    protected function cleanseImageUrl($url) : string
    {
        return str_replace('=w340-rw', '=w340', $url);
    }
}

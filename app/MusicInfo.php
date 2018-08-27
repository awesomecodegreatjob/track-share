<?php

namespace App;

use Illuminate\Contracts\Support\Arrayable;

class MusicInfo implements Arrayable
{
    private $service;
    private $id;
    private $album;
    private $track;
    private $type;
    private $artist;
    private $link;
    private $imageUrl;

    public function __construct(
        string $service,
        string $id,
        string $album,
        string $track,
        string $type,
        string $artist,
        string $link,
        string $imageUrl
    )
    {
        $this->service = $service;
        $this->id = $id;
        $this->album = $album;
        $this->track = $track;
        $this->type = $type;
        $this->artist = $artist;
        $this->link = $link;
        $this->imageUrl = $imageUrl;
    }

    public static function fromArray(array $info) : MusicInfo
    {
        $info = new static(
            array_get($info, 'service'),
            array_get($info, 'id'),
            array_get($info, 'album'),
            array_get($info, 'track'),
            array_get($info, 'type'),
            array_get($info, 'artist'),
            array_get($info, 'link'),
            array_get($info, 'image_link')
        );

        return $info;
    }

    public function getService() : string
    {
        return $this->service;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getAlbum() : string
    {
        return $this->album;
    }

    public function getTrack() : string
    {
        return $this->track;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getArtist() : string
    {
        return $this->artist;
    }

    public function getLink() : string
    {
        return $this->link;
    }

    public function getImageUrl() : string
    {
        return $this->imageUrl;
    }

    public function isAlbum() : bool
    {
        return $this->getType() === 'album';
    }

    public function isTrack() : bool
    {
        return $this->getType() === 'track';
    }

    public static function Empty(string $service)
    {
        return new self($service, '', '', '', '', '', '', '');
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'service' => $this->getService(),
            'album' => $this->getAlbum(),
            'track' => $this->getTrack(),
            'type' => $this->getType(),
            'artist' => $this->getArtist(),
            'link' => $this->getLink(),
            'imageUrl' => $this->getImageUrl(),
        ];
    }
}
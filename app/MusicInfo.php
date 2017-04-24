<?php

namespace App;

class MusicInfo
{
    public $id;
    public $album;
    public $track;
    public $type;
    public $artist;
    public $link;
    public $image_link;

    public function fill($info)
    {
        $this->id = array_get($info, 'id');
        $this->album = array_get($info, 'album');
        $this->track = array_get($info, 'track');
        $this->type = array_get($info, 'type');
        $this->artist = array_get($info, 'artist');
        $this->link = array_get($info, 'link');
        $this->image_link = array_get($info, 'image_link');
    }
}

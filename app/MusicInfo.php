<?php

namespace App;

class MusicInfo
{
    public $id;
    public $title;
    public $type;
    public $artist;
    public $link;
    public $image_link;

    public function fill($info)
    {
        $this->id = array_get($info, 'id');
        $this->title = array_get($info, 'title');
        $this->type = array_get($info, 'type');
        $this->artist = array_get($info, 'artist');
        $this->link = array_get($info, 'link');
        $this->image_link = array_get($info, 'image_link');
    }
}

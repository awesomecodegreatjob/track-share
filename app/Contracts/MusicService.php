<?php

namespace App\Contracts;

use App\MusicInfo;

interface MusicService {

    /**
     * Validates music service URIs
     *
     * @param  string  $uri
     *
     * @return  bool
     */
    function matches($uri);

    /**
     * Takes a resource URI and returns the resource type and ID, or
     * false if the URI is not valid.
     *
     * @param  string  $uri
     *
     * @return  string[ resource type, resource id ]|bool
     */
    public function music_id($uri);

    /**
     * Retrieve music info by resource type and ID. Returns null if resource not found.
     *
     * @param  string  $type
     * @param  string  $id
     *
     * @return  MusicInfo|null
     */
    public function music_info_by_id($type, $id);

    /**
     * Retrieve music info by URI
     *
     * @param  string  $uri
     *
     * @return  MusicInfo
     */
    public function music_info($uri);

    /**
     * Take in a MusicInfo instance and find the resource ID. Returns null if
     * resource isn't found.
     *
     * @param  MusicInfo  $info  Must have artist, title and type properties set
     *
     * @return  MusicInfo|null
     */
    public function search(MusicInfo $info);
}
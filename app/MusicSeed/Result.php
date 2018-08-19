<?php

namespace App\MusicSeed;

interface Result
{
    public function getId() : string;

    public function getService() : string;

    public function getType() : string;
}
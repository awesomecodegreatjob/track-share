<?php

namespace App\MusicSeed;

class ResultOk extends MusicSeed implements Result
{
    private $id;

    private $service;

    private $type;

    public function __construct(string $service, string $type, string $id)
    {
        $this->service = $service;
        $this->type = $type;
        $this->id = $id;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getService() : string
    {
        return $this->service;
    }

    public function getType() : string
    {
        return $this->type;
    }
}

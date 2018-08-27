<?php

namespace App;

use Illuminate\Contracts\Support\Arrayable;

class MusicSeed implements Arrayable
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

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'service' => $this->getService(),
            'type' => $this->getType(),
        ];
    }
}

<?php

namespace App;

use Illuminate\Support\Collection;

class MusicServices
{
    function all() : Collection
    {
        return collect(config('musicServices', []))
            ->mapWithKeys(function (string $serviceClass) {
                $s = app($serviceClass);
                return [ $s->getId() => $s ];
            });
    }

    function except(string $service) : Collection
    {
        return $this->all()
            ->filter(function (MusicService $s) use ($service) {
                return $s->getId() === $service;
            });
    }

    // Todo  Test this!
    function find(string $service) : MusicService
    {
        return $this
            ->all()
            ->first(function (MusicService $s) use ($service) {
                return $s->getId() === $service;
            });
    }
}

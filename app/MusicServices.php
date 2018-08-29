<?php

namespace App;

use Illuminate\Support\Collection;
use PhpSlang\Option\Option;

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

    /**
     * @param string $service
     * @return Option<MusicService>
     */
    function find(string $service) : Option
    {
        $service = $this
            ->all()
            ->first(function (MusicService $s) use ($service) {
                return $s->getId() === $service;
            });

        return Option::of($service);
    }
}

<?php

namespace App\MusicSeed;

abstract class MusicSeed
{
    public static function ok(string $service, string $type, string $id) : Result
    {
        return new ResultOk($service, $type, $id);
    }

    public static function error(string $message) : Result
    {
        return new ResultError($message);
    }
}
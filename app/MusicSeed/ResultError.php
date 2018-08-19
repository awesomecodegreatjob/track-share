<?php

namespace App\MusicSeed;

class ResultError extends MusicSeed implements Result
{
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getId(): string
    {
        return $this->message;
    }

    public function getService(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->message;
    }
}

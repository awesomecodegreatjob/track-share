<?php

namespace App\Spotify\Client;

use Carbon\Carbon;

class AuthToken
{
    protected $token;

    protected $type;

    protected $expires;

    public function __construct(string $type, string $token, Carbon $expires)
    {
        $this->type = $type;
        $this->token = $token;
        $this->expires = $expires;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExpires(): Carbon
    {
        return $this->expires;
    }
}

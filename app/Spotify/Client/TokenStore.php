<?php

namespace App\Spotify\Client;

use Carbon\Carbon;

class TokenStore
{
    private $filePath = 'spotify_auth';

    public function store(AuthToken $token)
    {
        $this->getDisk()->put(
            $this->filePath,
            json_encode([
                'token' => $token->getToken(),
                'type' => $token->getType(),
                'expires' => $token->getExpires()->timestamp,
            ])
        );
    }

    public function get() : AuthToken
    {
        [ 'token' => $token, 'type' => $type, 'expires' => $expires ] = json_decode(
            $this->getDisk()->get($this->filePath),
            true
        );

        return new AuthToken(
            $type,
            $token,
            Carbon::createFromTimestamp($expires)
        );
    }

    private function getDisk()
    {
        return \Storage::disk('local');
    }
}

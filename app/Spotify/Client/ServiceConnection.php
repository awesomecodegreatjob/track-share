<?php

namespace App\Spotify\Client;

use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use App\Spotify\Contracts\ApiConnection;
use GuzzleHttp\Exception\ClientException;

class ServiceConnection implements ApiConnection
{
    public function request(string $method, string $uri, array $params = []) : array
    {
        if (!$this->isAuthorized()) {
            $this->attemptAuthorization();
        }

        $token = (new TokenStore)->get();

        try {
            $client = new GuzzleClient;

            $requestPath = sprintf('https://api.spotify.com/%s?%s', $uri, http_build_query($params));

            $res = $client->request(
                $method,
                $requestPath,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$token->getToken(),
                    ]
                ]
            );

            return json_decode($res->getBody(), true);
        } catch (ClientException $e) {
            return json_decode('{ "error": { "status": "400" } }', true);
        }
    }

    private function isAuthorized() : bool
    {
        $authToken = (new TokenStore)->get();

        if (!$authToken) {
            return false;
        }

        return $authToken->getExpires()->greaterThan(Carbon::now());
    }

    private function attemptAuthorization() : void
    {
        $client = new GuzzleClient;

        $res = $client->request(
            'POST',
            'https://accounts.spotify.com/api/token',
            [
                'headers' => [
                    'Authorization' => 'Basic '.base64_encode(env('SPOTIFY_CLIENT_ID').':'.env('SPOTIFY_CLIENT_SECRET'))
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ]
            ]
        );

        [
            'access_token' => $accessToken,
            'token_type' => $tokenType,
            'expires_in' => $expiresIn,
        ] = json_decode($res->getBody(), true);

        (new TokenStore)->store(new AuthToken(
            $tokenType,
            $accessToken,
            Carbon::now()->addSeconds($expiresIn)
        ));
    }
}

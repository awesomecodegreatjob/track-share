<?php

namespace App\Spotify\Contracts;

interface ApiConnection
{
    public function request(string $method, string $uri, array $params = []) : array;
}

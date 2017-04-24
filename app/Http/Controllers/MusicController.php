<?php

namespace App\Http\Controllers;

use App\Music;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MusicController extends Controller
{
    public function show($key)
    {
        $music = Music::getByKey($key);
        return view('actions.music.show', [
            'music' => $music,
        ]);
    }
}

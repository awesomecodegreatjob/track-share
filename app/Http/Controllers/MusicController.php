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
        if($music = Music::getByKey($key))
            return view('actions.music.show', [
                'music' => $music,
            ]);
        else
            return abort(404, 'Requested music link not found');
    }
}

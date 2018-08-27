<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MusicLink
 * @package App
 *
 * @property-read int $id
 * @property string $key
 * @property array $seeds
 */
class MusicLink extends Model
{
    protected $casts = [
        'seeds' => 'array',
    ];

    protected $guarded = [];
}

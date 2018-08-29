<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
    /** @var int */
    const KEY_LENGTH = 6;

    /** @var string|null */
    private static $staticKey = null;

    protected $casts = [
        'seeds' => 'array',
    ];

    protected $guarded = [];

    /**
     * Generate unique, random key of size KEY_LENGTH.
     *
     * @param Collection $seeds
     * @return string|null
     */
    public static function saveSeeds(Collection $seeds): MusicLink
    {
        $link = app(MusicLink::class);

        return static::create([
            'key' => $link->generateKey(),
            'seeds' => $seeds->toArray(),
        ]);
    }

    public static function getByKey(string $key): ?MusicLink
    {
        return static::where('key', $key)->first();
    }

    public static function setKey(string $key)
    {
        self::$staticKey = $key;
    }

    public function generateKey() : string
    {
        if (self::$staticKey) {
            return self::$staticKey;
        }

        $hashCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';

        $hash = null;
        while($hash == null || strlen($hash) < self::KEY_LENGTH) {
            $randomIndex = rand(0, strlen($hashCharacters) - 1);
            $hash .= substr($hashCharacters, $randomIndex, 1);
        }

        if(static::getByKey($hash)) {
            return $this->generateKey();
        } else {
            return $hash;
        }
    }
}

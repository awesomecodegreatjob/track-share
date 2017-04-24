<?php

namespace Test;

use App\Music;
use Exception;
use App\Exceptions\Handler;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(Exception $e) {}
            public function render($request, Exception $e) {
                throw $e;
            }
        });
    }

    /**
     * Retrieve last created Music record. Returns null if no Music exists.
     *
     * @return Music|null
     */
    protected function getLastMusic()
    {
        $last = last(Music::all());
        if($last)
            return $last[0];
        else
            return null;
    }
}

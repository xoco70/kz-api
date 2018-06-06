<?php

use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{

    protected $baseUrl = 'http://localhost';
    protected static $applicationRefreshed = false;
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    /**
     * Creates the application.
     *
     */
    public function createApplication()
    {
        return self::initialize();
    }

    private static $configurationApp = null;

    // It last 13 seconds to initially migrate DB.
    // Worth to use it when tests > 30 sec
    public static function initialize()
    {

        if (is_null(self::$configurationApp)) {
            $app = require __DIR__ . '/../bootstrap/app.php';

            $app->environment('testing');

            if (config('database.default') == 'sqlite') {
                $db = app()->make('db');
                $db->connection()->getPdo()->exec("pragma foreign_keys=1");
            }

//            Artisan::call('migrate');
//            Artisan::call('db:seed');

            self::$configurationApp = $app;
            return $app;
        }

        return self::$configurationApp;
    }


    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function forceRefreshApplication()
    {
        if (!is_null($this->app)) {
            $this->app->flush();
        }
        $this->app = null;
        self::$configurationApp = null;
        self::$applicationRefreshed = true;
        parent::refreshApplication();
    }


//    public function tearDown()
//    {
//        if ($this->app) {
//            foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
//                call_user_func($callback);
//            }
//
//        }
//        if (self::$applicationRefreshed) {
//            self::$applicationRefreshed = false;
//            $this->app->flush();
//            $this->app = null;
//            self::$configurationApp = null;
//        }
//
//        $this->setUpHasRun = false;
//
//        if (property_exists($this, 'serverVariables')) {
//            $this->serverVariables = [];
//        }
//
//        if (class_exists('Mockery')) {
//            Mockery::close();
//        }
//
//        $this->afterApplicationCreatedCallbacks = [];
//        $this->beforeApplicationDestroyedCallbacks = [];
//    }
}

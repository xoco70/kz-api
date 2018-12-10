<?php

use Illuminate\Contracts\Notifications\Dispatcher as NotificationDispatcher;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /** @var array */
    protected $dispatchedNotifications = [];
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

    public static function initialize()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        if (is_null(self::$configurationApp)) {
            $app->environment('testing');

            if (config('database.default') == 'sqlite') {

                $db = app()->make('db');
                $db->connection()->getPdo()->exec("pragma foreign_keys=1");
            }


            Artisan::call('migrate');
            dd("migrate");
            Artisan::call('db:seed');

            self::$configurationApp = $app;
        }

        return $app;
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

    /**
     * Mock the notification dispatcher so all notifications are silenced.
     *
     * @return $this
     */
    protected function withoutNotifications()
    {
        $mock = Mockery::mock(NotificationDispatcher::class);
        $mock->shouldReceive('send')->andReturnUsing(function ($notifiable, $instance, $channels = []) {
            $this->dispatchedNotifications[] = compact(
                'notifiable', 'instance', 'channels'
            );
        });
        $this->app->instance(NotificationDispatcher::class, $mock);
        return $this;
    }

    /**
     * Specify a notification that is expected to be dispatched.
     *
     * @param  mixed $notifiable
     * @param  string $notification
     * @return $this
     */
    protected function expectsNotification($notifiable, $notification)
    {
        $this->withoutNotifications();
        $this->beforeApplicationDestroyed(function () use ($notifiable, $notification) {
            foreach ($this->dispatchedNotifications as $dispatched) {
                $notified = $dispatched['notifiable'];
                if (($notified === $notifiable ||
                        $notified->getKey() == $notifiable->getKey()) &&
                    get_class($dispatched['instance']) === $notification
                ) {
                    return $this;
                }
            }
            throw new Exception(
                'The following expected notification were not dispatched: [' . $notification . ']'
            );
        });
        return $this;
    }

    /**
     * Specify a notification that is not expected to be dispatched.
     *
     * @param  mixed $notifiable
     * @param  string $notification
     * @return $this
     */
    protected function doesntExpectNotification($notifiable, $notification)
    {
        $this->withoutNotifications();
        $this->beforeApplicationDestroyed(function () use ($notifiable, $notification) {
            foreach ($this->dispatchedNotifications as $dispatched) {
                $notified = $dispatched['notifiable'];
                if (($notified === $notifiable ||
                        $notified->getKey() == $notifiable->getKey()) &&
                    get_class($dispatched['instance']) === $notification
                ) {
                    throw new Exception(
                        'These unexpected notifications were fired: [' . $notification . ']'
                    );
                }
            }
        });
        return $this;
    }

    /**
     * Assert that a given where condition matches a soft deleted record
     *
     * @param  string $table
     * @param  array $data
     * @param  string $connection
     * @return $this
     */
    protected function seeIsSoftDeletedInDatabase($table, array $data, $connection = null)
    {
        $database = $this->app->make('db');

        $connection = $connection ?: $database->getDefaultConnection();

        $count = $database->connection($connection)
            ->table($table)
            ->where($data)
            ->whereNotNull('deleted_at')
            ->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }
}

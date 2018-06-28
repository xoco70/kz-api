<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();

$app->withEloquent();


/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    App\Http\Middleware\CorsMiddleware::class
]);


//$app->routeMiddleware([
//    'jwt.auth' => App\Http\Middleware\JwtMiddleware::class,
//]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'jwt.auth' => Tymon\JWTAuth\Http\Middleware\GetUserFromToken::class,
    'jwt.refresh' => Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);

//$app->register(App\Providers\CatchAllOptionsRequestsProvider::class);
$app->register(Aws\Laravel\AwsServiceProvider::class);
$app->register(Urameshibr\Providers\FormRequestServiceProvider::class);


$app->register(Xoco70\LaravelTournaments\TournamentsServiceProvider::class);

$app->register(Barryvdh\Debugbar\LumenServiceProvider::class);
$app->register(OwenIt\Auditing\AuditingServiceProvider::class);
$app->register(Cviebrock\EloquentSluggable\ServiceProvider::class);

if ($app->environment() !== 'production') {
    $app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
}

$app->register(Barryvdh\Snappy\LumenServiceProvider::class);

$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register('Sentry\SentryLaravel\SentryLumenServiceProvider');


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/api.php';
});
//class_alias('Illuminate\Support\Facades\Config', 'Config');
$app->configure('countries');
$app->configure('constants');
$app->configure('options');
$app->configure('debugbar');
$app->alias('mailer', \Illuminate\Contracts\Mail\Mailer::class);

return $app;

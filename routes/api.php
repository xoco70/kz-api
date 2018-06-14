<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/tournaments', 'TournamentController@index');
$router->get('/tournaments/{slug}/edit', 'TournamentController@edit');
$router->delete('/tournaments/{slug}', 'TournamentController@destroy');
$router->put('/tournaments/{slug}', 'TournamentController@update');
$router->get('/tournaments/levels', 'TournamentLevelController@index');
$router->get('/tournaments/presets', 'PresetsController@index');

$router->get('/categories', 'CategoryController@index');
$router->post('/categories', 'CategoryController@store');

$router->get('/tournaments/{slug}/competitors', 'CompetitorController@index');
$router->post('/tournaments/{slug}/competitors', 'CompetitorController@store');
$router->delete('/tournaments/{slug}/competitors/{id}', 'CompetitorController@destroy');



$router->post('/auth/login', 'Auth\AuthController@authenticate');
$router->post('/register', 'Auth\RegisterController@register');
$router->get('register/confirm/{token}', 'Auth\RegisterController@confirm');


$router->group(['middleware' => 'jwt.auth'],
    function () use ($router) {
        $router->get('users', function () {
            $users = \App\User::all();
            return response()->json($users);
        });
    }
);
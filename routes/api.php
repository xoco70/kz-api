<?php

$router->post('/auth/login', 'Auth\AuthController@authenticate');
$router->post('/register', 'Auth\RegisterController@register');
$router->get('register/confirm/{token}', 'Auth\RegisterController@confirm');


$router->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
//$this->post('password/reset', 'Auth\ResetPasswordController@reset');

$router->group(['middleware' => 'jwt.auth'],
    function () use ($router) {
        $router->get('/tournaments', 'TournamentController@index');
        $router->delete('/tournaments/{slug}', 'TournamentController@destroy');
        $router->post('/tournaments', 'TournamentController@store');
        $router->get('/tournaments/{slug}/edit', 'TournamentController@edit');
        $router->put('/tournaments/{slug}', 'TournamentController@update');

        $router->get('/tournaments/{slug}/statistics', 'TournamentController@statistics');

        $router->get('/categories', 'CategoryController@index');
        $router->post('/categories', 'CategoryController@store');

        $router->get('/tournaments/{slug}/competitors', 'CompetitorController@index');
        $router->post('/championships/{id}/competitors', 'CompetitorController@store');
        $router->delete('/tournaments/{slug}/competitors/{id}', 'CompetitorController@destroy');

        $router->post('/championships/{championshipId}/settings', 'ChampionshipSettingsController@store');
        $router->put('/championships/{championshipId}/settings/{id}', 'ChampionshipSettingsController@update');

        $router->get('/tournaments/{slug}/trees', 'TreeController@index');
        $router->post('/championships/{championshipId}/trees', 'TreeController@store');

        $router->get('/tournaments/{slug}/fights', 'FightController@index');

        $router->get('/users', 'UserController@index');
        $router->get('/users/{slug}/edit', 'UserController@edit');
        $router->put('/users/{slug}', 'UserController@update');
    }
);
$router->post('/user/avatar', 'UserController@upload');



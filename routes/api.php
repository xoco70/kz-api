<?php

$router->post('/auth/login', 'Auth\AuthController@authenticate');
$router->post('/register', 'Auth\RegisterController@register');
$router->get('register/confirm/{token}', 'Auth\RegisterController@confirm');


$router->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
//$this->post('password/reset', 'Auth\ResetPasswordController@reset');


$router->group(['middleware' => 'jwt.auth'],
    function () use ($router) {
        $router->get('/tournaments', 'TournamentController@index');
        $router->get('/tournaments/{slug}/edit', 'TournamentController@edit');
        $router->delete('/tournaments/{slug}', 'TournamentController@destroy');
        $router->put('/tournaments/{slug}', 'TournamentController@update');
        $router->get('/tournaments/levels', 'TournamentLevelController@index');
        $router->get('/tournaments/presets', 'PresetsController@index');

        $router->get('/categories', 'CategoryController@index');
        $router->post('/categories', 'CategoryController@store');


        $router->post('/tournaments/{slug}/competitors', 'CompetitorController@store');
        $router->delete('/tournaments/{slug}/competitors/{id}', 'CompetitorController@destroy');

        $router->get('/tournaments/{slug}/competitors', 'CompetitorController@index');
        $router->post('/championships/{id}/competitors', 'CompetitorController@store');

        $router->post('/championships/{championshipId}/settings', 'ChampionshipSettingsController@store');
        $router->put('/championships/{championshipId}/settings/{id}', 'ChampionshipSettingsController@update');

        $router->get('/tournaments/{slug}/trees', 'TreeController@index');
    }
);



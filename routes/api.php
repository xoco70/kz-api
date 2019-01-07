<?php

$router->post('/auth/login', 'Auth\AuthController@authenticate');
$router->post('/auth/socialLogin', 'Auth\AuthController@socialLogin');
$router->post('/register', 'Auth\RegisterController@register');
$router->get('register/confirm/{token}', 'Auth\RegisterController@confirm');


$router->post('password/email', 'Auth\PasswordController@forgot');
$router->post('password/reset', 'Auth\PasswordController@reset');

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
        $router->get('/tournaments/{slug}/teams', 'TeamController@index');
        $router->post('/championships/{championshipId}/trees', 'TreeController@store');
        $router->post('/championships/{championshipId}/teams', 'TeamController@store');
        $router->delete('/championships/{championshipId}/teams/{id}', 'TeamController@destroy');

        $router->post('teams/{teamId}/competitors/{competitorId}/add', 'CompetitorTeamController@store');
        $router->post('teams/{teamId}/competitors/{competitorId}/remove', 'CompetitorTeamController@destroy');
        $router->post('teams/{team1Id}/{team2Id}/competitors/{competitorId}/move', 'CompetitorTeamController@update');

        $router->get('/tournaments/{slug}/fights', 'FightController@index');

        $router->get('/users', 'UserController@index');
        $router->get('/users/{slug}/edit', 'UserController@edit');
        $router->put('/users/{slug}', 'UserController@update');
    }
);
$router->post('/users/{slug}/avatar/', 'UserController@upload');



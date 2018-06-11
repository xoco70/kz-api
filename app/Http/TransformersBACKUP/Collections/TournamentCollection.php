<?php
use League\Fractal;

$tournaments = Tournament::all();

$resource = new Fractal\Resource\Collection($tournaments, function (Tournament $tournament) {
    return [
        'id' => (int)$tournament->id,
        'title' => $tournament->title,
        'year' => $tournament->yr,
        'author' => [
            'name' => $tournament->author_name,
            'email' => $tournament->author_email,
        ],
        'links' => [
            [
                'rel' => 'self',
                'uri' => '/tournaments/' . $tournament->id,
            ]
        ]
    ];
});
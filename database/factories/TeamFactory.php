<?php


use App\Team;

$factory->define(Team::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word
        // championship_id must have a setting of isTeam
    ];
});
<?php

namespace App\Http\Controllers;

use App\Tournament;

class FightController extends Controller
{
    /**
     * Display a listing of the fights.
     *
     * @return Tournament
     */
    public function index($slug)
    {
        return Tournament::with(['championships.category','championships.firstRoundFights' => function ($query) {
            $query->with(['competitor1.user', 'competitor2.user', 'team1', 'team2']);
        }])->where('slug', $slug)
            ->first();
    }
}

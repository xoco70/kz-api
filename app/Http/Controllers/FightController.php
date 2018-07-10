<?php

namespace App\Http\Controllers;

use App\Tournament;
use Illuminate\Http\Response;

class FightController extends Controller
{
    /**
     * Display a listing of the fights.
     *
     * @return Tournament
     */
    public function index($slug)
    {
        return response()->json(Tournament::with(['championships.category', 'championships.firstRoundFights' => function ($query) {
            $query->with(['competitor1.user', 'competitor2.user', 'team1', 'team2']);
        }])->where('slug', $slug)
            ->first(), Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers;

use App\TournamentLevel;


class TournamentLevelController extends Controller
{

    public function index()
    {
        return TournamentLevel::orderBy('id', 'asc')->select('id', 'name')->get();
    }

}

<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Requests\CategoryRequest;
use App\TournamentLevel;
use Illuminate\Http\Request;


class TournamentLevelController extends Controller
{

    public function index()
    {
        return TournamentLevel::orderBy('id', 'asc')->select('id', 'name')->get();
    }

}

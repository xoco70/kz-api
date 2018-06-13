<?php

namespace App\Http\Controllers;

use App\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class CompetitorController extends Controller
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Display a listing of the resource
     */
    public function index($slug)
    {
        $tournament = Tournament::with('championships.users', 'championships.teams', 'championships.category')
            ->where('slug', $slug)->first();
//        $settingSize = $tournament->championshipSettings()->count();
//        $categorySize = $tournament->categories->count();
//        $grades = Grade::getAllPlucked();
//        $countries = Country::getAll();
//        return view("tournaments.users", compact('tournament', 'settingSize', 'categorySize', 'grades', 'countries'));
//        factory(Competitor::class,20)->create();
        return $tournament;
    }

    /**
     * Store a new Competitor
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @internal param CompetitorRequest $form
     */
    public function store(Request $request)
    {
    }

    /**
     * Remove the Competitor from storage.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function destroy($slug)
    {
    }

}

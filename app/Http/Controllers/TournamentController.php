<?php

namespace App\Http\Controllers;

use App\Category;
use App\Exceptions\InvitationNeededException;
use App\Grade;
use App\Http\Resources\TournamentResource;
use App\Tournament;
use App\Venue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;


class TournamentController extends Controller
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
    public function index()
    {
        return TournamentResource::collection(Tournament::paginate(25));
    }

    /**
     * Display a listing of the resource
     */
    public function edit($slug)
    {
        try {
            $tournament = Tournament::where('slug', $slug)->first();
            $selectedCategories = $tournament->categories()->select('category.id', 'name')->get(); // 3,4 ,240
            $defaultCategories = Category::take(7)->select('id', 'name')->get()->sortBy('id'); // 1 2 3 4 5 6 7
            $categories = $selectedCategories->merge($defaultCategories)->toArray();

            $tournament = Tournament::with('competitors', 'championshipSettings', 'championships.settings', 'championships.category', 'venue')
                ->withCount('competitors', 'teams', 'championshipSettings')
                ->where('slug', $slug)->first();

            $tournament->trees_count = $tournament->trees->groupBy('championship_id')->count();
            return response()->json(['tournament' => $tournament, 'categories' => $categories], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Store a new Tournament
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @internal param TournamentRequest $form
     */
    public function store()
    {
        try {
            $categoriesSelected = $this->request->categoriesSelected;
            $ruleId = $this->request->rule_id;
            $request = $this->request->except('categoriesSelected');
            $request['dateIni'] = Tournament::parseDate($this->request->dateIni);
            $request['dateFin'] = Tournament::parseDate($this->request->dateFin);
            $request['registerDateLimit'] = Carbon::now()->addMonth(3)->format('Y-m-d');
            $tournament = $this->request->auth->tournaments()->create($request);
            if ($ruleId == 0) { // No presets,
                $tournament->categories()->sync($categoriesSelected);
                return response()->json($tournament, 200);
            }
            $tournament->setAndConfigureCategories($ruleId);
            return response()->json($tournament, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Update the Tournament in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $slug = $this->request->slug;
        $tab = $this->request->tab;
        try {
            $tournament = Tournament::where('slug', $slug)->first();

            if ($tab == 'general') {
                $tournament->name = $this->request->name;
                // Build date from Json
                $tournament->dateIni = Tournament::parseDate($this->request->dateIni);
                $tournament->dateFin = Tournament::parseDate($this->request->dateFin);
                $tournament->registerDateLimit = Tournament::parseDate($this->request->registerDateLimit);
                $tournament->promoter = $this->request->promoter;
                $tournament->host_organization = $this->request->host_organization;
                $tournament->technical_assistance = $this->request->technical_assistance;
            }
            if ($tab == 'venue') {
                $venue = $tournament->venue;
                if ($venue == null) $venue = new Venue;
                $venue->fill([
                    'venue_name' => $this->request->venue['venue_name'],
                    'address' => $this->request->venue['address'],
                    'details' => $this->request->venue['details'],
                    'city' => $this->request->venue['city'],
                    'CP' => $this->request->venue['CP'],
                    'state' => $this->request->venue['state'],
                    'latitude' => $this->request->venue['latitude'],
                    'longitude' => $this->request->venue['longitude'],
                    'country_id' => $this->request->venue['country_id'],
                ]);
                $venue->save();
                $tournament->venue_id = $venue->id;
            }

            if ($tab == 'categories') {
                $categories = $this->request->categoriesSelected;
                $tournament->categories()->sync($categories);
                return $tournament->where('slug', $slug)->with('championships.category')->first();
            }
            return response()->json($tournament->save(), 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 422);
        }
    }

    /**
     * Remove the Tournament from storage.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function destroy($slug)
    {
        try {
            $tournament = Tournament::where('slug', $slug)->first();
            if (!$tournament) return response()->json('tournaments.wrong_tournament', 422);
            return response()->json($tournament->delete(), 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $tournamentSlug
     * @return JsonResponse
     */
    public function restore($tournamentSlug)
    {
        $tournament = Tournament::withTrashed()->whereSlug($tournamentSlug)->first();
        if ($tournament->restore()) {
            return Response::json(['msg' => Lang::get('msg.tournament_restored_successful', ['name' => $tournament->name]), 'status' => 'success']);
        }

        return Response::json(['msg' => Lang::get('msg.tournament_restored_error', ['name' => $tournament->name]), 'status' => 'error']);

    }

    /**
     * Called when a user want to register an open tournament
     * @param Request $request
     * @param Tournament $tournament
     * @return mixed
     * @throws InvitationNeededException
     */
    public function register(Request $request, Tournament $tournament)
    {

        if (!Auth::check()) {
            Session::flash('message', trans('msg.please_create_account_before_playing', ['tournament' => $tournament->name]));
            return redirect(URL::action('Auth\LoginController@login'));
        }
        if ($tournament->isOpen() && Auth::check()) {
            App::setLocale($request->auth->locale);

            $grades = Grade::getAllPlucked();
            $tournament = Tournament::with('championships.category', 'championships.users')->find($tournament->id);
            return view("categories.register", compact('tournament', 'invite', 'currentModelName', 'grades'));
        }

        throw new InvitationNeededException();
    }
}

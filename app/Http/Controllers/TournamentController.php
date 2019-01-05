<?php

namespace App\Http\Controllers;

use App\Category;
use App\Exceptions\InvitationNeededException;
use App\Http\Resources\TournamentResource;
use App\Tournament;
use App\Venue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


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
     * Show a list of tournament
     */
    public function index()
    {
        if (Auth::user()->isSuperAdmin()) {
            return TournamentResource::collection(Tournament::orderBy('updated_at', 'desc')->paginate(25));
        }

        return TournamentResource::collection(Auth::user()->tournaments()->orderBy('updated_at', 'desc')->paginate(25));

    }

    /**
     * Return needed resources to build the edit tournament page
     */
    public function edit($slug)
    {
        try {
            $tournament = Tournament::where('slug', $slug)->first();
            $selectedCategories = $tournament->categories()->select('category.id', 'name')->get(); // 3,4 ,240
            $defaultCategories = Category::take(7)->select('id', 'name')->get()->sortBy('id'); // 1 2 3 4 5 6 7
            $categories = $selectedCategories->merge($defaultCategories)->toArray();

            $tournament = Tournament::with('competitors', 'championshipSettings', 'championships.settings', 'championships.category', 'venue')
                ->withCount('championshipSettings')
                ->where('slug', $slug)->first();

            return response()->json(['tournament' => $tournament, 'categories' => $categories], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $this->validate($this->request, [
                'name' => 'required',
                'dateIni' => 'required',
                'dateFin' => 'required',
                'rule_id' => 'required',
                'categoriesSelected' => 'min:0',
            ]);
            $categoriesSelected = $this->request->categoriesSelected;
            $ruleId = $this->request->rule_id;
            $request = $this->request->except('categoriesSelected');
            $request['dateIni'] = Tournament::parseDate($this->request->dateIni);
            $request['dateFin'] = Tournament::parseDate($this->request->dateFin);
            $request['registerDateLimit'] = Carbon::now()->addMonth(3)->format('Y-m-d');
            $tournament = Auth::user()->tournaments()->create($request);


            if ($ruleId == 0) { // No presets,
                $tournament->categories()->sync($categoriesSelected);
                return response()->json($tournament, HttpResponse::HTTP_CREATED);
            }
            $tournament->setAndConfigureCategories($ruleId);
            return response()->json($tournament, HttpResponse::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json($e->getMessage(), HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the Tournament in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request, $slug)
    {
        $tab = $this->request->input('tab');
        try {
            $tournament = Tournament::where('slug', $slug)->first();

            if ($tab == 'general') {
                $this->validate($this->request, [
                    'name' => 'required',
                    'dateIni' => 'required',
                    'dateFin' => 'required',
                ]);
                $tournament->name = $this->request->input('name');

                // Build date from Json
                $tournament->dateIni = Tournament::parseDate($this->request->input('dateIni'));
                $tournament->dateFin = Tournament::parseDate($this->request->input('dateFin'));
                $tournament->registerDateLimit = Tournament::parseDate($this->request->input('registerDateLimit'));
                $tournament->promoter = $this->request->input('promoter');
                $tournament->host_organization = $this->request->input('host_organization');
                $tournament->technical_assistance = $this->request->input('technical_assistance');
            }
            if ($tab == 'venue') {
                $this->validate($this->request, [
                    'venue.venue_name' => 'required',
                    'venue.address' => 'required',
                ]);
                $venue = $tournament->venue;
                if ($venue == null) $venue = new Venue;
                $venue->fill([
                    'venue_name' => $this->request->venue['venue_name'],
                    'address' => $this->request->venue['address'],
                    'details' => isset($this->request->venue['details']) ? $this->request->venue['details'] : null,
                    'city' => isset($this->request->venue['city']) ? $this->request->venue['city'] : null,
                    'CP' => isset($this->request->venue['CP']) ? $this->request->venue['CP'] : null,
                    'state' => isset($this->request->venue['state']) ? $this->request->venue['state'] : null,
                    'latitude' => $this->request->venue['latitude'],
                    'longitude' => $this->request->venue['longitude'],
                    'country_id' => $this->request->venue['country_id'],
                ]);
                $venue->save();
                $tournament->venue_id = $venue->id;
            }

            if ($tab == 'categories') {
                $this->validate($this->request, [
                    'categoriesSelected' => 'required|array|min:1',
                ]);
                $categories = $this->request->categoriesSelected;
                $tournament->categories()->sync($categories);
                return $tournament->where('slug', $slug)->with('championships.category')->first();
            }
            return response()->json($tournament->save(), HttpResponse::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json($e->getMessage(), HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
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
            if (!$tournament) return response()->json('tournaments.wrong_tournament', HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            return response()->json($tournament->delete(), HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return stats for tournament right menu
     * @param $slug
     * @return JsonResponse
     */
    public function statistics($slug)
    {
        try {
            $tournament = Tournament::where('slug', $slug)->first();
            return response()->json([
                'competitors_count' => $tournament->competitors()->count(),
                'teams_count' => $tournament->teams()->count(),
                'trees_count' => $tournament->trees->count(),
                'championships_count' => $tournament->championships()->count()
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

//    /**
//     * @param $tournamentSlug
//     * @return JsonResponse
//     */
//    public function restore($tournamentSlug)
//    {
//        $tournament = Tournament::withTrashed()->whereSlug($tournamentSlug)->first();
//        if ($tournament->restore()) {
//            return Response::json(['msg' => Lang::get('msg.tournament_restored_successful', ['name' => $tournament->name]), 'status' => 'success']);
//        }
//
//        return Response::json(['msg' => Lang::get('msg.tournament_restored_error', ['name' => $tournament->name]), 'status' => 'error']);
//
//    }

    /**
     * Called when a user want to register an open tournament
     * @param Request $request
     * @param Tournament $tournament
     * @return mixed
     * @throws InvitationNeededException
     */
//    public function register(Request $request, Tournament $tournament)
//    {
//
//        if (!Auth::check()) {
//            Session::flash('message', trans('msg.please_create_account_before_playing', ['tournament' => $tournament->name]));
//            return redirect(URL::action('Auth\LoginController@login'));
//        }
//        if ($tournament->isOpen() && Auth::check()) {
//            App::setLocale(Auth::user()->locale);
//
//            $grades = Grade::getAllPlucked();
//            $tournament = Tournament::with('championships.category', 'championships.users')->find($tournament->id);
//            return view("categories.register", compact('tournament', 'invite', 'currentModelName', 'grades'));
//        }
//
//        throw new InvitationNeededException();
//    }


}

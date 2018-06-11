<?php

namespace App\Http\Controllers;

use App\Category;
use App\Country;
use App\Exceptions\InvitationNeededException;
use App\Grade;
use App\Http\Requests\TournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Tournament;
use App\Venue;
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
        $tournament = Tournament::where('slug', $slug)->first
        ();
        $selectedCategories = $tournament->categories()->select('category.id', 'name')->get()->toArray(); // 3,4 ,240

        //TODO Bad workaround
        $categories = [];
        foreach ($selectedCategories as $category) {
            unset($category['pivot']);
            $categories[] = $category;
        }

        $defaultCategories = Category::take(7)->select('id', 'name')->get()->sortBy('id')->toArray(); // 1 2 3 4 5 6 7
        foreach ($defaultCategories as $category) {
            $categories[] = $category;
        }

        $tournament = Tournament::with('competitors', 'championshipSettings', 'championships.settings', 'championships.category', 'venue')
            ->withCount('competitors', 'teams', 'championshipSettings')
            ->where('slug', $slug)->first();


        //TODO Should be on his own controller
        $countries = Country::getAll();

        $tournament->trees_count = $tournament->trees->groupBy('championship_id')->count();
        $grades = Grade::getAllPlucked();
//        $levels = TournamentLevel::getAllPlucked();
//        $hanteiLimit = config('options.hanteiLimit');

//        return view('test');
        $response = [
            'tournament' => $tournament,
            'categories' => $categories,
            'countries' => $countries,
            'grades' => $grades,
            'code' => 200

        ];
        return $response;
    }

    /**
     * Store a new Tournament
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @internal param TournamentRequest $form
     */
    public function store(Request $request)
    {
        $request = $request->except('category', 'config');
        $request['registerDateLimit'] = Carbon::now()->addMonth(3);

        $tournament = Auth::user()->tournaments()->create($request);
        if ($request->rule_id == 0) {
            $tournament->categories()->sync($request->input('category'));
            return $tournament;
        }
        $tournament->setAndConfigureCategories($request->rule_id);
        return $tournament;
    }

    /**
     * Update the Tournament in storage.
     *
     * @param TournamentRequest|Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        dd($request);
        //TODO Shouldn't I have a Policy???
//        $venue = $tournament->venue;
//        if ($venue == null)
//            $venue = new Venue;
//
//        if ($venueRequest->has('venue_name')) {
//            $venue->fill($venueRequest->all());
//            $venue->save();
//        } else {
//            $venue = new Venue();
//        }
//        $res = $request->update($tournament, $venue);
//
//        if ($request->ajax()) {
//            $res == 0
//                ? $result = Response::json(['msg' => trans('msg.tournament_update_error', ['name' => $tournament->name]), 'status' => 'error'])
//                : $result = Response::json(['msg' => trans('msg.tournament_update_successful', ['name' => $tournament->name]), 'status' => 'success']);
//            return $result;
//        } else {
//            $res == 0
//                ? flash()->success(trans('msg.tournament_update_error', ['name' => $tournament->name]))
//                : flash()->success(trans('msg.tournament_update_successful', ['name' => $tournament->name]));
//            return redirect(URL::action('TournamentController@edit', $tournament->slug))->with('activeTab', $request->activeTab);
//        }
        return null;
    }

    /**
     * Remove the Tournament from storage.
     *
     * @param $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($slug)
    {
        $tournament = Tournament::where('slug', $slug)->first();
        if (!$tournament) return response()->json(['msg' => 'tournament doesnt exist', 'status' => 'error']);
        if ($tournament->delete()) {
            return response()->json(['msg' => Lang::get('msg.tournament_delete_successful', ['name' => $tournament->name]), 'status' => 'success']);
        }
        return response()->json(['msg' => Lang::get('msg.tournament_delete_error'), 'status' => 'error']);
    }

    /**
     * @param $tournamentSlug
     * @return \Illuminate\Http\JsonResponse
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
            App::setLocale(Auth::user()->locale);

            $grades = Grade::getAllPlucked();
            $tournament = Tournament::with('championships.category', 'championships.users')->find($tournament->id);
            return view("categories.register", compact('tournament', 'invite', 'currentModelName', 'grades'));
        }

        throw new InvitationNeededException();
    }
}

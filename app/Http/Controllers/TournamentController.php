<?php

namespace App\Http\Controllers;

use App\Exceptions\InvitationNeededException;
use App\Grade;
use App\Http\Requests\TournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Tournament;
use App\Venue;
use Illuminate\Http\Request;
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
     * Store a new Tournament
     *
     * @param TournamentRequest $form
     * @return \Illuminate\Http\Response
     */
    public function store(TournamentRequest $form)
    {
        $tournament = $form->persist();
        $msg = trans('msg.tournament_create_successful', ['name' => $tournament->name]);
        flash()->success($msg);
        return redirect(URL::action('TournamentController@edit', $tournament->slug));
    }

    /**
     * Update the Tournament in storage.
     *
     * @param TournamentRequest $request
     * @param VenueRequest $venueRequest
     * @param Tournament $tournament
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TournamentRequest $request, VenueRequest $venueRequest, Tournament $tournament)
    {
        //TODO Shouldn't I have a Policy???
        $venue = $tournament->venue;
        if ($venue == null)
            $venue = new Venue;

        if ($venueRequest->has('venue_name')) {
            $venue->fill($venueRequest->all());
            $venue->save();
        } else {
            $venue = new Venue();
        }
        $res = $request->update($tournament, $venue);

        if ($request->ajax()) {
            $res == 0
                ? $result = Response::json(['msg' => trans('msg.tournament_update_error', ['name' => $tournament->name]), 'status' => 'error'])
                : $result = Response::json(['msg' => trans('msg.tournament_update_successful', ['name' => $tournament->name]), 'status' => 'success']);
            return $result;
        } else {
            $res == 0
                ? flash()->success(trans('msg.tournament_update_error', ['name' => $tournament->name]))
                : flash()->success(trans('msg.tournament_update_successful', ['name' => $tournament->name]));
            return redirect(URL::action('TournamentController@edit', $tournament->slug))->with('activeTab', $request->activeTab);
        }
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

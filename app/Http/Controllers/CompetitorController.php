<?php

namespace App\Http\Controllers;

use App\Competitor;
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
        $tournament = Tournament::with('championships.competitors.user', 'championships.teams', 'championships.category')
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
     */
    public function store(Request $request)
    {
//        $championshipId = $request->championshipId;
//        $championship = Championship::findOrFail($championshipId);
//
//        foreach ($request->firstnames as $id => $firstname) {
//            $email = $request->emails[$id] ?? Auth::user()->id . sha1(rand(1, 999999999999)) . (User::count() + 1) . "@kendozone.com";
//            $lastname = $request->lastnames[$id] ?? '';
//
//            $user = Competitor::createUser([
//                'firstname' => $firstname,
//                'lastname' => $lastname,
//                'name' => $firstname . " " . $lastname,
//                'email' => $email
//            ]);
//
//            $championships = $user->championships();
//            // If user has not registered yet this championship
//            if (!$championships->get()->contains($championship)) {
//                // Get Competitor Short ID
//                $categories = $tournament->championships->pluck('id');
//                $shortId = Competitor::getShortId($categories, $tournament);
//                $championships->attach($championshipId, ['confirmed' => 0, 'short_id' => $shortId]);
//            }
//            //TODO Should add a test for this
//            // We send him an email with detail (and user /password if new)
//            if (strpos($email, '@kendozone.com') === -1) { // Substring is not present
//                $code = resolve(Invite::class)->generateTournamentInvite($user->email, $tournament);
//                $user->notify(new InviteCompetitor($user, $tournament, $code, $championship->category->name));
//            }
//        }
//        flash()->success(trans('msg.user_registered_successful', ['tournament' => $tournament->name]));
//        return redirect(URL::action('CompetitorController@index', $tournament->slug));
    }

    /**
     * Remove the Competitor from storage.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function destroy($tournamentSlug, $competitorId)
    {
        try {
            Competitor::destroy($competitorId);
            return response()->json(['msg' => trans('msg.user_delete_successful'), 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'status' => 'error']);
        }
    }
}
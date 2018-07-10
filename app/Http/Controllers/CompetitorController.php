<?php

namespace App\Http\Controllers;

use App\Championship;
use App\Competitor;
use App\Invite;
use App\Notifications\InviteCompetitor;
use App\Tournament;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
        return response()->json($tournament, HttpResponse::HTTP_OK);
    }

    /**
     * Store a new Competitor
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $championshipId)
    {
        try {
            $competitors = $request->competitors;

            $championship = Championship::findOrFail($championshipId);
            $tournament = $championship->tournament;
            // TODO Validation doesn't pass testing.
//            $validate = $this->validate($this->request, [
//                'competitors.*.firstname' => 'required',
//                'competitors.*.lastname' => 'required',
////                'competitors.*.email' => 'email',
//            ]);


            foreach ($competitors as $competitor) {

                $firstname = $competitor['firstname'];
                $email = $competitor['email'] != null
                    ? $competitor['email']
                    : Auth::user()->id . sha1(rand(1, 999999999999)) . (User::count() + 1) . "@kendozone.com";
                $lastname = $competitor['lastname'];

                $user = Competitor::createUser([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'name' => $firstname . " " . $lastname,
                    'email' => $email
                ]);

                $championships = $user->championships();
//            // If user has not registered yet this championship
                if (!$championships->get()->contains($championship)) {
                    // Get Competitor Short ID
                    $categories = $tournament->championships->pluck('id');
                    $shortId = Competitor::getShortId($categories, $tournament, $user);
                    $championships->attach($championshipId, ['confirmed' => 0, 'short_id' => $shortId]);
                }
                //TODO Should add a test for this
                // We send him an email with detail (and user /password if new)
                if (!strpos($email, '@kendozone.com')) { // Substring is not present
                    $code = app(Invite::class)->generateTournamentInvite($user->email, $tournament);
                    $user->notify(new InviteCompetitor($user, $tournament, $code, $championship->category->name));
                }
            }
            return response()->json($championship->competitors()->with('user')->get(), HttpResponse::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json($e->getMessage(), HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }


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
            return response()->json('msg.user_delete_successful', HttpResponse::HTTP_OK);
        } catch (\Exception $e) {
            //TODO May not work, and return a big HTML
            return response()->json($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

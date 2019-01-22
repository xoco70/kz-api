<?php

namespace App\Http\Controllers;

use App\FightersGroup;
use App\Team;
use App\Tournament;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    /**
     * Display a listing of teams with competitors.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function index(Request $request, $slug)
    {
        $tournament = Tournament::with(['championships' => function ($query) {
            $query->with('teams', 'category', 'settings')
                ->whereHas('category', function ($subquery) {
                    $subquery->where('isTeam', '=', 1);
                });
        }])
            ->withCount('competitors', 'teams')
            ->where('slug', $slug)->first();

        // TODO Should merge those 2 queries in a single one

        $arrChampionshipsWithTeamsAndCompetitors = $tournament->championships->map(function ($championship) {
            $competitors = $championship->competitors->load('user')->map(function ($competitor) {
                return ["id" => $competitor->id, "name" => $competitor->user->name];
            })->toArray();
            $teams = $championship->teams()->with('competitors.user')->select('id', 'name')->get()->toArray();
            $tempAssignCompatitors = new Collection();
            $assignedCompetitors = $this->getAssignedCompetitors($championship, $tempAssignCompatitors);
            $freeCompetitors = $championship->competitors;
            if ($assignedCompetitors != null) {
                $freeCompetitors = $freeCompetitors->diff($assignedCompetitors);
            }

            return [
                'championship' => $championship->id, // TODO ???
                'competitors' => $competitors,
                'assignedCompetitors' => $assignedCompetitors,
                'freeCompetitors' => $freeCompetitors,
                'teams' => $teams
            ];
        })->toArray();
        return response()->json(['tournament' => $tournament, 'championships' => $arrChampionshipsWithTeamsAndCompetitors]);
    }

    /**
     * Add a Team
     *
     * @param Request $request
     * @param $championshipId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $championshipId)
    {
        try {
            $team = Team::where('championship_id', $championshipId)->orderBy('id', 'desc')->first();
            $short_id = 1;
            if ($team != null) {
                $short_id = $team->short_id + 1;
            }
            $request->request->add(['short_id' => $short_id]);
            $request->request->add(['championship_id' => $championshipId]);
            $team = Team::create($request->all());
            return response()->json($team, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json(trans('msg.team_create_error_already_exists', ['name' => $request['name']]), Response::HTTP_CONFLICT);
        }
    }

    /**
     * Delete a Team
     *
     * @param Request $request
     * @param $championshipId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $championshipId, $teamId)
    {
        try {
            return response()->json(Team::destroy($teamId), HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    //TODO Should go in the championship model

    /**
     * Get all competitors assigned to a team
     * @param $championship
     * @param $tempAssignCompatitors
     * @return mixed
     */
    private function getAssignedCompetitors($championship, $tempAssignCompatitors)
    {
        return $championship->teams->reduce(function ($acc, $team) use ($tempAssignCompatitors) {
            return $tempAssignCompatitors->push($team->competitors()->with('user')->get())->collapse();
        });
    }

}

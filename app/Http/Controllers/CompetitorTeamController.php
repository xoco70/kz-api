<?php

namespace App\Http\Controllers;

use App\Team;
use Illuminate\Http\Response;

class CompetitorTeamController extends Controller
{
    /**
     * Add a Competitor to Team
     * @param $teamId int
     * @param $competitorId int
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($teamId, $competitorId) // add competitor to team
    {
        $team = Team::findOrFail($teamId);
        $team->competitors()->attach($competitorId);
        return response()->json(null, Response::HTTP_OK);
    }

    /**
     * // move competitor from team1 to team2
     * @param $team1Id int
     * @param $team2Id int
     * @param $competitorId int
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($team1Id, $team2Id, $competitorId)
    {

        $team1 = Team::findOrFail($team1Id);
        $team2 = Team::findOrFail($team2Id);

        $team1->competitors()->detach($competitorId);
        $team2->competitors()->attach($competitorId);
        return response()->json(null, Response::HTTP_OK);

    }

    /**
     * Remove a competitor from his team
     * @param $teamId int
     * @param $competitorId int
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($teamId, $competitorId) // remove competitor to team
    {

        $team = Team::findOrFail($teamId);
        $result = $team->competitors()->detach($competitorId);
        if ($result == 0) return response()->json('error', Response::HTTP_CONFLICT);
        return response()->json(null, Response::HTTP_OK);
    }


}

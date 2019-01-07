<?php

namespace App\Http\Controllers;

use App\Team;
use Illuminate\Http\Response;

class CompetitorTeamController extends Controller
{
    public function store($teamId, $competitorId) // add competitor to team
    {
        try {
            $team = Team::find($teamId);
            $team->competitors()->attach($competitorId);
            return response()->json('success', Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($team1Id, $team2Id, $competitorId) // move competitor from team1 to team2
    {
        try {
            $team1 = Team::find($team1Id);
            $team2 = Team::find($team2Id);

            $team1->competitors()->detach($competitorId);
            $team2->competitors()->attach($competitorId);
            return response()->json('success', Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($teamId, $competitorId) // remove competitor to team
    {
        try {
            $team = Team::find($teamId);
            $team->competitors()->detach($competitorId);
            return response()->json('success', Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


}

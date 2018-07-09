<?php

namespace App\Http\Controllers;

use App\Championship;
use App\FightersGroup;
use App\Grade;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class TreeController extends Controller
{
    /**
     * Display a listing of trees.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function index(Request $request, $slug)
    {
        $request->request->add(['slug' => $slug]);
        return FightersGroup::getTournament($request);
    }

    /**
     * Build Tree
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|string
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $tournament = FightersGroup::getTournament($request); // Builder

        foreach ($tournament->championships as $championship) {

            $generation = $championship->chooseGenerationStrategy();
            //TODO Generation has twice the setting Object, once in championship, and once in root
            try {
                $generation->run();

                $trees = FightersGroup::getTournament($request);
                return response()->json($trees, HttpResponse::HTTP_OK);
            } catch (Exception $e) {
                return response()->json($e->getMessage(),HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return redirect(route('tree.index', $tournament->slug))->with('activeTreeTab', $request->activeTreeTab);
    }

    public function single(Request $request)
    {
        $championship = Championship::find($request->championship);
        $grades = Grade::getAllPlucked();
        return view('pdf.tree', compact('championship', 'grades'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $championshipId)
    {
        $championship = Championship::find($championshipId);
        $numFighter = 0;
        $query = FightersGroup::with('fights')
            ->where('championship_id', $championship->id);

        $fighters = $request->singleElimination_fighters;
        $scores = $request->score;
        if ($championship->hasPreliminary()) {
            $query = $query->where('round', '>', 1);
            $fighters = $request->preliminary_fighters;
        }
        $groups = $query->get();

        foreach ($groups as $group) {
            foreach ($group->fights as $fight) {
                // Find the fight in array, and update order
                $fight->c1 = $fighters[$numFighter];
                $scores[$numFighter] != null
                    ? $fight->winner_id = $fighters[$numFighter]
                    : $fight->winner_id = null;
                $numFighter++;

                $fight->c2 = $fighters[$numFighter];
                if ($fight->winner_id == null) {
                    $scores[$numFighter] != null
                        ? $fight->winner_id = $fighters[$numFighter]
                        : $fight->winner_id = null;
                }

                $numFighter++;
                $fight->save();
            }
        }
        return back();
    }

}

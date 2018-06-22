<?php

namespace App\Http\Controllers;

use App\Championship;
use App\FightersGroup;
use App\Grade;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;

class TreeController extends Controller
{
    /**
     * Display a listing of trees.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function index($slug)
    {
        $tournament = FightersGroup::getTournament($slug);
        return $tournament;
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

        if ($request->auth->cannot('store', [FightersGroup::class, $tournament])) {
            throw new AuthorizationException();
        }
        foreach ($tournament->championships as $championship) {
            $generation = $championship->chooseGenerationStrategy();

            try {
                $generation->run();
                flash()->success(trans('msg.championships_tree_generation_success'));
            } catch (TreeGenerationException $e) {
                flash()->error($e->message);
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

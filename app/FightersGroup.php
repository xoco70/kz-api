<?php

namespace App;

use Illuminate\Http\Request;

class FightersGroup extends \Xoco70\LaravelTournaments\Models\FightersGroup
{
    /**
     * Get tournament with a lot of stuff Inside - Should Change name
     * @param $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getTournament(Request $request)
    {
        if ($request->has('slug')) { // param is a Tournament Slug
            return static::getTournamentFromTournament($request->slug);
        }
        // param is a Championship Id
        return static::getTournamentFromChampionship($request->id);
    }

    public static function getTournamentFromChampionship($id)
    {
        return Tournament::whereHas('championships', function ($query) use ($id) {
            return $query->where('id', $id);
        })
            ->with(['championships' => function ($query) use ($id) {
                $query->where('id', '=', $id)
                    ->with([
                        'competitors.user',
                        'settings',
                        'category',
                        'fightersGroups' => function ($query) {
                            return $query->with('fights','teams', 'competitors.user');
                        }]);
            }])
            ->firstOrFail();
    }

    public static function getTournamentFromTournament($slug)
    {
        return Tournament::with(['championships' => function ($query) use ($slug) {
            $query->with([
                'competitors.user',
                'settings',
                'category',
                'fightersGroups' => function ($query) {
                    return $query->with('fights','teams', 'competitors.user');
                }]);
        }])->withCount('competitors', 'teams')
            ->where('slug', $slug)->firstOrFail();
    }


    /**
     * Check if Request contains tournamentSlug / Should Move to TreeRequest When Built.
     *
     * @param $request
     *
     * @return bool
     */
    public static function hasTournamentInRequest($request)
    {
        return $request->tournament != null;
    }

    /**
     * Check if Request contains championshipId / Should Move to TreeRequest When Built.
     *
     * @param $request
     *
     * @return bool
     */
    public static function hasChampionshipInRequest($request)
    {
        return $request->championshipId != null; // has return false, don't know why
    }
}
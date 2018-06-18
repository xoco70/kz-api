<?php

namespace App;

class FightersGroup extends \Xoco70\LaravelTournaments\Models\FightersGroup
{
    /**
     * Get tournament with a lot of stuff Inside - Should Change name
     * @param $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getTournament($param)
    {
        $tournament = null;
        if (is_int($param)) {
            return static::getTournamentFromChampionship($param);
        }
        return static::getTournamentFromTournament($param);

    }

    public static function getTournamentFromChampionship($id)
    {
        return Tournament::whereHas('championships', function ($query) use ($id) {
            return $query->where('id', $id);
        })
            ->with(['championships' => function ($query) use ($id) {
                $query->where('id', '=', $id)
                    ->with([
                        'settings',
                        'category',
                        'users',
                        'fightersGroups' => function ($query) {
                            return $query->with('teams', 'competitors', 'fights');
                        }]);
            }])
            ->firstOrFail();
    }

    public static function getTournamentFromTournament($slug)
    {
        return Tournament::with(['championships' => function ($query) use ($slug) {
            $query->with([
                'settings',
                'category',
                'users',
                'fightersGroups' => function ($query) {
                    return $query->with('teams', 'competitors', 'fights');
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
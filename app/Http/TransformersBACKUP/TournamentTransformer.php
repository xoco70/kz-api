<?php
namespace App\Http\Transformers;

use App\Tournament;
use App\User;
use League\Fractal;
use App\Http\Transformers\VenueTransformer;


class TournamentTransformer extends Fractal\TransformerAbstract
{
    protected $availableIncludes = [
        'venue'
    ];

    public function transform(Tournament $tournament)
    {


        return [
            'id' => $tournament->id,
            'slug' => $tournament->slug,
            'user' => User::findOrFail($tournament->user_id)->email,
            'date' => $tournament->dateIni,
            'name' => $tournament->name,
            'numCompetitors' => $tournament->competitors->count()
        ];
    }

    /**
     * Include Venue
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeVenue(Tournament $tournament)
    {
        $venue = $tournament->venue;

        return $this->item($venue, new VenueTransformer);
    }
}
<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user' => User::findOrFail($this->user_id)->email,
            'date' => $this->dateIni,
            'slug' => $this->slug,
            'venue_id' => $this->venue_id,
            'championships' => ChampionshipResource::collection($this->whenLoaded('championships')),
            'venue' => new VenueResource($this->whenLoaded('venue')),
            'competitors_count' => $this->competitors->count()
        ];
    }
}

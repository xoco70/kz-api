<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompetitorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
//        return [
//            'id' => $this->id,
//            'short_id' => $this->name,
//            'championship_id' => User::findOrFail($this->user_id)->email,
//            'user_id' => $this->dateIni,
//            'confirmed' => $this->slug,
//            'venue_id' => $this->venue_id,
//            'created_at' => ChampionshipResource::collection($this->whenLoaded('championships')),
//            'updated_at' => new VenueResource($this->whenLoaded('venue')),
//            'deleted_at' => $this->competitors->count()
//        ];
    }
}

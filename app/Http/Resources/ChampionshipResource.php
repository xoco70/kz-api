<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipResource extends JsonResource
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
            'tournament_id' => $this->tournament_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
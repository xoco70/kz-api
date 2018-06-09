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
            'slug' => $this->slug,
            'user' => User::findOrFail($this->user_id)->email,
            'date' => $this->dateIni,
            'name' => $this->name,
            'numCompetitors' => $this->competitors->count()
        ];
    }
}

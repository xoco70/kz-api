<?php


namespace App\Http\Transformers;


use App\Venue;

class VenueTransformer
{

    public function transform(Venue $venue)
    {
        return $venue->toArray();
    }
}
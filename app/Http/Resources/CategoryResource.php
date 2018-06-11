<?php


namespace App\Http\Resources;


class CategoryResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
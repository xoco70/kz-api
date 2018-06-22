<?php

namespace App\Http\Controllers;

use App\Association;
use App\Club;
use App\Federation;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;


class UserController extends Controller
{

    public function index()
    {
        return UserResource::collection(User::paginate(25));
    }

    public function edit(Request $request, $slug)
    {
        try {
            return response()->json(
                [
                    'user' => User::where('slug', $slug)->first(),
                    'federations' => Federation::all(),
                    'associations' => Association::all(),
                    'clubs' => Club::all(),
                ], 200
            );
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Model
     */

    public function update(Request $request, $slug)
    {

    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{

    public function index()
    {
        return UserResource::collection(User::paginate(25));
    }

    public function edit($slug)
    {
        try {
            return response()->json(User::where('slug', $slug)->first(), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
        try {
            $this->validate($request, [
                'name' => 'required',
            ]);
            $user = User::find($request->id);
            $user->name = $request->name;
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->country_id = $request->country_id ?? null;
            $user->grade_id = $request->grade_id  ?? null;
            $user->update();

            return $user;

        } catch (ValidationException $e) {
            return response()->json($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upload(Request $request)
    {
//        $request->validate([
//            'file' => ['required', 'image']
//        ]);
        $file = $request
            ->file('file')
            ->store('images/avatar', 'public');

//        Image::make(storage_path('app/public/' . $file))
//            ->resize(200, 200, function ($constraint) {
//                $constraint->aspectRatio();
//                $constraint->upsize();
//            })
//            ->save();

//        Auth::user()->update([
//            'avatar' => basename($file)
//        ]);
        return response([], 204);
    }
}

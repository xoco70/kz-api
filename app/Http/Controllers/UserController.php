<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Exception\NotFoundException;
use Intervention\Image\Facades\Image;


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
            $user->grade_id = $request->grade_id ?? null;
            $user->update();

            return $user;

        } catch (ValidationException $e) {
            return response()->json($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upload($slug, Request $request)
    {
        try {
            $this->validate($request, [
                'file' => ['required', 'image']
            ]);
            $file = $request
                ->file('file');
            $imgName = str_slug($file->getClientOriginalName());
            $ext = '.' . $file->guessClientExtension();
            $imgName .= '-' . md5($imgName . microtime()) . $ext;
            $img = Image::make($file);
            $img->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $resource = $img->stream()->detach();
            Storage::disk('s3')->put('avatar/' . $imgName, $resource);
            // TODO  We should be logged, and be able to use: Auth::user()
            $user = User::where('slug', $slug)->firstOrFail();
            $user->update(['avatar' => $imgName]);
            return response()->json($imgName, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json('', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotFoundException $e) {
            return response()->json('', Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}



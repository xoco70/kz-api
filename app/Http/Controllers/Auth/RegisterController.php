<?php

namespace App\Http\Controllers\Auth;

use App\Grade;
use App\Http\Controllers\Controller;
use App\Invite;
use App\Notifications\AccountRegistered;
use App\Tournament;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Handle a registration request for the application.
     *
     * @param Request $request
     * @return bool
     */
    public function register(Request $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'verified' => 0,
            ]);

        } catch (QueryException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => '500']);
        }

        $user->notify(new AccountRegistered($user));

        return response()->json(['message' => 'OK', 'code' => '200']);


    }

    /**
     * Confirm a user's email address.
     *
     * @param  string $token
     * @return mixed
     */
    public function confirm($token)
    {
        // TODO Redirect to Front error page is fail
        $user = User::where('token', $token)->firstOrFail();
        $user->verified = true;
        $user->save();
        return redirect(env('URL_FRONT').'login?welcome=1');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\PasswordChangedEmail;
use App\Notifications\ResetLinkEmailSent;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function forgot(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $user = User::where('email', $request->email)->firstOrFail();

            $token = str_random(64);
            DB::table(config('auth.passwords.users.table'))->insert([
                'email' => $user->email,
                'token' => $token
            ]);
            $user->notify(new ResetLinkEmailSent($user, $token));
            return response()->json(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['class' => $e->getTraceAsString(), 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reset(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->setRememberToken(Str::random(60));
            $user->save();
            $user->notify(new PasswordChangedEmail($user));
            return response()->json(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['class' => $e->getTraceAsString(), 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

}

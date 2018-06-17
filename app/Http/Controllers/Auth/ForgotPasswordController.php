<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\ResetLinkEmailSent;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $user = User::where('email', $request->email)->firstOrFail();
        $token = str_random(64);
        DB::table(config('auth.passwords.users.table'))->insert([
            'email' => $user->email,
            'token' => $token
        ]);
        $user->notify(new ResetLinkEmailSent($user, $token));

//        Mail::send('password', ['user' => $user, 'token' => $token], function ($m) use ($user) {
//            $m->from('contact@kendozone.com', 'Kendozone Team');
//            $m->to($user->email, $user->name)->subject('Your Reminder!');
//        });

//        Mail::send([
//            'to' => $user->email,
//            'subject' => 'Your Password Reset Link',
//            'view' => config('auth.passwords.users.email'),
//            'view_data' => [
//                'token' => $token,
//                'user' => $user
//            ]
//        ]);
    }


}

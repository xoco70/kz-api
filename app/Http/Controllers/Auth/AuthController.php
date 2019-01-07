<?php

namespace app\Http\Controllers\Auth;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController
{

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @return mixed
     */
    public function authenticate()
    {
//        $this->validate($this->request, [
//            'email' => 'required|email',
//            'password' => 'required'
//        ]);
        // Find the user by email
        $user = User::where('email', $this->request->email)->first();
        if (!$user) {
            return response()->json('login.invalid_credentials', HttpResponse::HTTP_UNAUTHORIZED);
        }
        $credentials = Input::only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json('login.invalid_credentials', HttpResponse::HTTP_UNAUTHORIZED);
        }
        return response()->json(compact('token', 'user'), HttpResponse::HTTP_ACCEPTED);
    }

    public function socialLogin(Request $request, Response $response)
    {
        $email = $request->email;
        $user = User::firstOrNew(['email' => $email]);
        if (!$user->exists) {
            $user->name = $request->name;
            $user->avatar = $request->avatar;
            $user->provider = $request->provider;
            $user->token = $request->token;
            $user->save();
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([ 'user' => $user, 'token' => $token ]);

    }
}
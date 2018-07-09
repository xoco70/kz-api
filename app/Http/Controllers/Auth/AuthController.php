<?php

namespace app\Http\Controllers\Auth;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Response as HttpResponse;


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

//    /**
//     * Create a new token.
//     *
//     * @param  \App\User $user
//     * @return string
//     */
//    protected function jwt(User $user)
//    {
//        $payload = [
//            'iss' => "lumen-jwt", // Issuer of the token
//            'sub' => $user, // Subject of the token
//            'iat' => time(), // Time when JWT was issued.
//            'exp' => time() + 60 * 60 // Expiration time
//        ];
//
//        // As you can see we are passing `JWT_SECRET` as the second parameter that will
//        // be used to decode the token in the future.
//        return JWT::encode($payload, env('JWT_SECRET'));
//    }

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
        $user = User::where('email', $this->request->input('email'))->first();
        if (!$user) {
            return response()->json('login.wrong_email', HttpResponse::HTTP_UNAUTHORIZED);
        }
        $credentials = Input::only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json('login.wrong_password', HttpResponse::HTTP_UNAUTHORIZED);
        }
        return response()->json(compact('token','user'), HttpResponse::HTTP_ACCEPTED);
    }
}
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Auth;

class CustomException extends \Exception
{
    protected $sentryID;

    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            $params = [];
            if (Auth::check()) {
                $params = [
                    'user' => [
                        'id' => $request->auth->id,
                        'email' => $request->auth->email
                    ],
                ];
            }

            $this->sentryID = app('sentry')->captureException($exception, $params);
        }
        parent::report($exception);
    }
}

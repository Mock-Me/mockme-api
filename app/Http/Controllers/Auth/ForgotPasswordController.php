<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordResetLinkMail;
use App\Jobs\SendSignupMail;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
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

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.

        $user = $this->getUser($request);

        if(is_null($user)) {
            $this->sendSignupMail($request->input('email'));
        } else {
            $this->sendPasswordResetMail($user, $this->broker()->createToken($user));
        }

        return response()->json([
            'message' => "If you're a registered user, an email has been sent to {$request->input('email')} with a password reset link."
        ]);
    }

    protected function getUser(Request $request)
    {
        return $this->broker()->getUser($request->only(['email']));
    }

    /**
     * Send a sign up email to the email
     *
     * @param string $email
     */
    protected function sendSignupMail(string $email)
    {
        SendSignupMail::dispatch($email);
    }

    /**
     * Send a password reset email with token link
     *
     * @param Authenticatable $user
     * @param string $token
     */
    protected function sendPasswordResetMail(Authenticatable $user, string $token)
    {
        SendPasswordResetLinkMail::dispatch($user, $token);
    }


}

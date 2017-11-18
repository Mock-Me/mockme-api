<?php

namespace Tests\Feature\Auth;

use App\User;
use Carbon\Carbon;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Reset Password validates that a token, an email, and a new password is required
     *
     * @return void
     */
    public function testPasswordResetValidatesFields()
    {
        $response = $this->postJson('/reset-password');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'token',
            'email',
            'password',
        ]);
    }

    /**
     * Reset Password validates password reset token
     */
    public function testPasswordResetValidatesToken()
    {
        $response = $this->postJson('/reset-password', [
            'token' => 'fake-token',
            'email' => 'chris@cmsoft.co.za',
            'password' => 'secret',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email'
        ]);
    }

    /**
     * Reset Password validates that token belongs to user
     */
    public function testPasswordResetValidatesTokenAgainstUser()
    {
        $user = factory(User::class)->create();
        $response = $this->postJson('/reset-password', [
            'token' => 'fake-token',
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email'
        ]);
    }

    /**
     * Reset Password changes password if token is valid
     */
    public function testPasswordResetResetsPasswordIfTokenIsValid()
    {
        $user = factory(User::class)->create();
        $token = Password::broker()->createToken($user);
        $response = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'secretreset',
        ]);
        $user->refresh();
        $response->assertStatus(200);
        $response->assertExactJson([
            'token' => $user->api_token,
        ]);
        $user = User::find($user->id);
        $this->assertTrue(Hash::check('secretreset', $user->password));
    }

    /**
     * Reset Password View route serves 404 if not email or token is present
     */
    public function testPasswordResetServes404IfNotEmailOrTokenPresent()
    {
        $response = $this->get('/reset/');
        $response->assertStatus(404);
    }

    /**
     * Reset Password View serves 404 if user is not found
     */
    public function testPasswordResetReturns404IfNoUserIsFound()
    {
        $email = base64_decode('chris@cmsoft.co.za');
        $response = $this->get("/reset/{$email}/fake-token");
        $response->assertStatus(404);
    }

    /**
     * Reset Password View serves 200 if token has expired
     */
    public function testPasswordResetReturns200IfTokenHasExpired()
    {
        $user = factory(User::class)->create();
        $token = Password::broker()->createToken($user);
        /**
         * @Hack @Note
         *
         * @Chris: This is a bit of a hack as I cannot see where to manually expire a token,
         * and sensibly this functionality should not exist as one would rather delete the token.
         * Hence I am manually deleting out of the DB
         */
        DB::table('password_resets')->where('token', $token)->update([
            'created_at' => Carbon::now()->subYear(),
        ]);
        $base64Email = base64_encode($user->email);
        $response = $this->get("/reset/{$base64Email}/{$token}");
        $response->assertStatus(200);
    }

    /**
     * Reset Password View returns 200 if token is valid
     */
    public function testPasswordResetReturns200IfTokenIsValid()
    {
        $user = factory(User::class)->create();
        $token = Password::broker()->createToken($user);
        $base64Email = base64_encode($user->email);
        $response = $this->get("/reset/{$base64Email}/{$token}");
        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\User;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
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
        $response = $this->postJson('/api/reset-password');

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
        $response = $this->postJson('/api/reset-password', [
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
        $response = $this->postJson('/api/reset-password', [
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
        $response = $this->postJson('/api/reset-password', [
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
}

<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendPasswordResetLinkMail;
use App\Jobs\SendSignupMail;
use App\User;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Reset password validates that an email is required
     *
     * @return void
     */
    public function testResetPasswordRouteValidatesEmailRequired()
    {
        $response = $this->postJson('/forgot-password');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test reset password sends sign up mail if email not found in users table
     *
     * @return void
     */
    public function testResetPasswordSendsSignUpMailIfEmailNotPresentInUsersTable()
    {
        Queue::fake();

        $email = 'none-existing@example.com';

        $response = $this->postJson('/forgot-password', [
            'email' => $email,
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendSignupMail::class, function ($job) use ($email) {
            return $job->email === $email;
        });
    }

    /**
     * Test reset password sends reset link if email is found
     */
    public function testResetEmailIsSentIfEmailIsFound()
    {
        Queue::fake();

        $user = factory(User::class)->create();

        $response = $this->postJson('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendPasswordResetLinkMail::class, function ($job) use ($user) {
            return $job->user->email === $user->email;
        });
    }
}

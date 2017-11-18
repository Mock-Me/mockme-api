<?php

namespace Tests\Feature\Feature\Auth;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Tests that login returns validation errors
     *
     * @return void
     */
    public function testLoginGivesValidationErrorsIfFieldAreNotPassed()
    {
        $response = $this->postJson('/login');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Tests that login fails when no user can be found
     *
     * @return void
     */
    public function testLoginFailsWhenNoUserIsFound()
    {
        $response = $this->postJson('/login', [
            'email' => 'chris@cmsoft.co.za',
            'password' => 'foobar',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    /**
     * Test that login fails when incorrect credentials are passed
     *
     * @return void
     */
    public function testLoginFailsWhenIncorrectCredentialsArePassed()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('correctPassword'),
        ]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    /**
     * Test that the login call returns an api token if successfully authenticated
     *
     * @return void
     */
    public function testLoginReturnsApiTokenOnSuccessfulAuthentication()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('correctPassword'),
        ]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'correctPassword',
        ]);

        $response->assertJsonStructure([
            'token',
        ]);
    }

}

<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{

    use RefreshDatabase;
    /**
     * Test that register brings back validation errors if nothing is passed
     *
     * @return void
     */
    public function testValidationErrorsIfNothingPassed()
    {
        $response = $this->postJson('/register');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
    }

    /**
     * Test that register returns an api token on successful registration
     */
    public function testRegisterReturnsApiTokenOnSuccessFulRegistration()
    {
        $response = $this->postJson('/register', [
            'name' => 'Chris',
            'email' => 'chris@cmsoft.co.za',
            'password' => 'foobar',
            'password_confirmation' => 'foobar',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'token',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Chris',
            'email' => 'chris@cmsoft.co.za',
        ]);
    }
}

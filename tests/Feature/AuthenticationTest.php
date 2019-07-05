<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanLoginWithCorrectCredentials()
    {
        $user = $this->createFakeUser($password = 'password');

        $credentials = [
            'email' => $user->email,
            'password' => $password
        ];

        $response = $this->postJson('api/auth/login', $credentials);

        $response->assertStatus(200);
        $response->assertJson(['token_type' => 'bearer']);
        $this->assertArrayHasKey('access_token', $response->decodeResponseJson());

        $this->assertAuthenticated('api');
        $this->assertAuthenticatedAs($user, 'api');
    }

    public function testUserCannotLoginWithWrongPassword()
    {
        $user = $this->createFakeUser();

        $credentials = [
            'email' => $user->email,
            'password' => 'wrong_password'
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
        $this->assertGuest('api');
    }

    public function testUserCannotLoginWithWrongEmail()
    {
        $credentials = [
            'email' => 'test@test.pt',
            'password' => 'password'
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
        $this->assertGuest('api');
    }

    public function testAuthenticatedUserCanGetData()
    {
        $user = $this->createFakeUser();
        $token = $this->generateJwtTokenForUser($user);

        $response = $this->withJwtToken($token)->postJson('/api/auth/me');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'email_verified_at',
            'updated_at',
            'created_at',
        ]);
    }

    public function testAuthenticatedUserCanLogout()
    {
        $user = $this->createFakeUser();
        $token = $this->generateJwtTokenForUser($user);

        $response = $this->withJwtToken($token)->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $this->assertGuest('api');
    }

    public function testUnauthenticatedUserCannotLogout()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    protected function createFakeUser($password = 'password')
    {
        return factory(User::class)->create([
            'password' => Hash::make($password)
        ]);
    }

    protected function generateJwtTokenForUser($user)
    {
        return $this->app['auth']->guard('api')->tokenById($user->id);
    }

    protected function withJwtToken($token)
    {
        $this->withHeader('Authorization', 'Bearer ' . $token);

        return $this;
    }
}

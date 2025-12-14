<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase; // Clears DB after each test

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        // Create a test user
        $user = Users::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'User'
        ]);

        // Attempt to login
        $response = $this->post(route('login'), [
            'identifier' => 'testuser', // Can be email or username
            'password' => 'password123',
        ]);

        // Assert user is authenticated
        $this->assertAuthenticatedAs($user);

        // Assert redirection based on role
        $response->assertRedirect(route('dashboard.use'));
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        // Create a test user
        Users::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt login with wrong password
        $response = $this->post(route('login'), [
            'identifier' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        // Assert user is NOT authenticated
        $this->assertGuest();

        // Assert validation error
        $response->assertSessionHasErrors(['error']);
    }

    /** @test */
    public function user_can_signup_with_valid_data()
    {
        $response = $this->post(route('signup'), [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123',
        ]);

        // Assert user is created
        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'new@example.com',
        ]);

        // Assert redirection to login with success message
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Account created successfully!');
    }

    /** @test */
    public function user_cannot_signup_with_existing_email()
    {
        // Create existing user
        Users::factory()->create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt to sign up with same email
        $response = $this->post(route('signup'), [
            'username' => 'newuser',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        // Assert validation error
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function user_can_logout()
    {
        // Create and log in a user
        $user = Users::factory()->create(); // No need for ->first()
        $this->actingAs($user);
    
        // Attempt to logout
        $response = $this->post(route('logout'));
    
        // Assert user is logged out
        $this->assertGuest();
    
        // Assert redirection to login page
        $response->assertRedirect(route('login'));
    }
}

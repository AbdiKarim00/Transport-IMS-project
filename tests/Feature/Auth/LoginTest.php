<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home')); // Or '/dashboard' or whatever the intended redirect is
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/login');
        $response->assertRedirect(route('home')); // Adjust if redirect path is different
    }

    /**
     * Define the home route for testing redirects.
     * Laravel's default Authenticate middleware redirects to `route('home')`
     * if not overridden. We need a named 'home' route for these tests to pass.
     * If your app uses a different route name or path, adjust accordingly.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Define a dummy 'home' route if it doesn't exist for testing purposes
        if (!app('router')->has('home')) {
            app('router')->get('/home', function () {
                return "Home Page";
            })->name('home');
        }
        // Also ensure a named 'login' route exists for unauthenticated redirects
        if (!app('router')->has('login')) {
             app('router')->get('/login', function () {
                return view('auth.login');
             })->name('login');
        }
    }
}

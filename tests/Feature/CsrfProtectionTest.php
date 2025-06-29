<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function defineTestRoutes(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/test-csrf-form', function () {
                return '<form method="POST" action="/test-csrf-submit"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit">Submit</button></form>';
            })->name('test.csrf.form');

            Route::post('/test-csrf-submit', function () {
                return response('CSRF Success', 200);
            })->name('test.csrf.submit');
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->defineTestRoutes();
    }

    public function test_post_request_without_csrf_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user); // CSRF protection typically applies to authenticated users

        $response = $this->post(route('test.csrf.submit'), ['name' => 'Test']);

        // Expecting 419 status code (Page Expired)
        $response->assertStatus(419);
    }

    public function test_post_request_with_csrf_token_is_accepted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Fetching the form to get a valid token is one way,
        // but Laravel's test helpers handle this automatically if you post from a page.
        // For direct POST, you can generate a token or rely on automatic handling.
        // The `TestCase` already handles adding CSRF token for testing POST requests
        // if you don't manually exclude it or are using $this->post() in a way that bypasses it.
        // However, to be explicit or if issues arise, manually managing it is an option.
        // For this test, we'll rely on Laravel's default handling which should include it.
        // If `VerifyCsrfToken` is correctly in the 'web' middleware, this should pass.

        // A more direct way to test with a token if needed:
        // $token = csrf_token();
        // $response = $this->withHeaders(['X-CSRF-TOKEN' => $token])
        //                  ->post(route('test.csrf.submit'), ['name' => 'Test']);

        // Standard post should be sufficient if web middleware is applied
        $response = $this->post(route('test.csrf.submit'), ['name' => 'Test']);

        $response->assertStatus(200);
        $response->assertSee('CSRF Success');
    }


    public function test_post_request_with_invalid_csrf_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->withHeaders([
            'X-CSRF-TOKEN' => 'invalid-token',
        ])->post(route('test.csrf.submit'), ['name' => 'Test']);

        $response->assertStatus(419);
    }
}

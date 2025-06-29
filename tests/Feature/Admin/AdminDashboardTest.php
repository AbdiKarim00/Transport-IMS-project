<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Role; // Assuming Role model exists
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->adminRole = Role::create(['name' => 'admin']);
        Role::create(['name' => 'driver']); // Non-admin role

        // Define a dummy 'login' route if it doesn't exist for testing purposes
        if (!app('router')->has('login')) {
             app('router')->get('/login', function () {
                return view('auth.login');
             })->name('login');
        }
        // Define a dummy admin dashboard route for testing
        if (!app('router')->has('admin.dashboard')) {
            app('router')->get('/admin/dashboard', function () {
                return "Admin Dashboard";
            })->middleware(['web', 'auth', 'admin'])->name('admin.dashboard');
        }
    }

    public function test_guest_is_redirected_from_admin_dashboard_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_gets_forbidden_for_admin_dashboard(): void
    {
        $nonAdminRole = Role::where('name', 'driver')->first();
        $user = User::factory()->create();
        // Manually associate role - adjust if your User model has a different way to set roles
        // This assumes a 'role_id' foreign key on the users table or similar logic in hasRole()
        // If User->roles() is a BelongsToMany, you'd use $user->roles()->attach($nonAdminRole);
        // Based on User model: `public function roles() { return $this->belongsTo(Role::class, 'role_id'); }`
        // So we need to ensure the user is associated with this role.
        // The UserFactory should be updated or we assign role_id here.
        // For now, let's assume a simple scenario or that UserFactory handles this.
        // A more robust way would be to create a User and assign the role.
        // For the User model provided `roles()` is a BelongsTo relationship.
        // So, we need to set the role_id on the user.
        $user->role_id = $nonAdminRole->id;
        $user->save();


        $this->actingAs($user);
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $adminUser = User::factory()->create();
        // Associate admin role
        $adminUser->role_id = $this->adminRole->id;
        $adminUser->save();


        $this->actingAs($adminUser);
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}

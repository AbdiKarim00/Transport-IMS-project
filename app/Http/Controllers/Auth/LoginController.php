<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'personal_number';
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Update login tracking (only if columns exist)
        try {
            $user->update([
                'last_login' => now(),
                'login_attempts' => 0,
                'locked_until' => null
            ]);
        } catch (\Exception $e) {
            // If columns don't exist, skip this update
        }

        // Check for temporary password (only if column exists)
        try {
            if ($user->is_temporary_password) {
                return redirect()->route('password.change');
            }
        } catch (\Exception $e) {
            // If column doesn't exist, skip this check
        }

        // Role-based redirection using personal_number patterns or role relationships
        return $this->redirectBasedOnRole($user);
    }

    /**
     * Redirect user based on their role
     *
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBasedOnRole($user)
    {
        // Check if user has role relationship
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('driver')) {
                return redirect()->route('driver.dashboard');
            }
            if ($user->hasRole('transport_officer')) {
                return redirect()->route('transport_officer.dashboard');
            }
            if ($user->hasRole('operational_admin')) {
                return redirect()->route('operational_admin.dashboard');
            }
        }

        // Fallback: Check role_id if it exists
        if (isset($user->role_id)) {
            switch ($user->role_id) {
                case 1:
                    return redirect()->route('admin.dashboard');
                case 2:
                    return redirect()->route('driver.dashboard');
                case 3:
                    return redirect()->route('transport_officer.dashboard');
                case 4:
                    return redirect()->route('operational_admin.dashboard');
            }
        }

        // Fallback: Check personal_number patterns
        $personalNumber = $user->personal_number ?? '';

        if (str_starts_with($personalNumber, 'ADMIN') || str_starts_with($personalNumber, 'ADM')) {
            return redirect()->route('admin.dashboard');
        }

        if (str_starts_with($personalNumber, 'DRV') || str_starts_with($personalNumber, 'DRIVER')) {
            return redirect()->route('driver.dashboard');
        }

        if (str_starts_with($personalNumber, 'TRN') || str_starts_with($personalNumber, 'TRANSPORT')) {
            return redirect()->route('transport_officer.dashboard');
        }

        if (str_starts_with($personalNumber, 'OPS') || str_starts_with($personalNumber, 'OPERATIONAL')) {
            return redirect()->route('operational_admin.dashboard');
        }

        // Default fallback to driver dashboard for now
        return redirect()->route('driver.dashboard');
    }
}

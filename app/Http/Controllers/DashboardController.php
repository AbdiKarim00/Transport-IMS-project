<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = auth()->user();

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

        // Default fallback to driver dashboard
        return redirect()->route('driver.dashboard');
    }
}

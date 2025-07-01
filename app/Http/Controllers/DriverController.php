<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Driver::with(['user', 'driverLicense', 'driverStatus'])->get();
    }

    public function showDashboard()
    {
        $user = auth()->user();

        // Try to get driver profile, create if doesn't exist
        $driver = null;

        if (method_exists($user, 'driver')) {
            $driver = $user->driver;
        }

        // If no driver profile exists, create a basic one or use user data
        if (!$driver) {
            // For now, create a mock driver object with user data
            $driver = (object) [
                'id' => $user->id,
                'user' => $user,
                'employee_id' => $user->personal_number ?? 'N/A',
                'hire_date' => $user->created_at ?? now(),
                'is_active' => true,
            ];
        }

        // Get current assignments and trips (handle if relationships don't exist)
        $currentTrip = null;
        $upcomingTrips = collect();
        $recentTrips = collect();
        $assignedVehicle = null;

        // Only query if driver has proper relationships
        if (is_object($driver) && method_exists($driver, 'trips') && isset($driver->id)) {
            try {
                $currentTrip = $driver->trips()
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->with(['vehicle', 'route'])
                    ->first();

                $upcomingTrips = $driver->trips()
                    ->where('status', 'scheduled')
                    ->where('scheduled_start_time', '>', now())
                    ->with(['vehicle', 'route'])
                    ->orderBy('scheduled_start_time')
                    ->limit(5)
                    ->get();

                $recentTrips = $driver->trips()
                    ->whereIn('status', ['completed', 'cancelled'])
                    ->with(['vehicle', 'route'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
                    ->get();

                // Vehicle information
                $assignedVehicle = $driver->currentVehicleAssignment?->vehicle;
            } catch (\Exception $e) {
                // If relationships don't exist, use empty collections
                $currentTrip = null;
                $upcomingTrips = collect();
                $recentTrips = collect();
                $assignedVehicle = null;
            }
        }

        // Driver statistics (safe defaults)
        $stats = [
            'total_trips' => 0,
            'completed_trips' => 0,
            'total_distance' => 0,
            'fuel_consumed' => 0,
            'on_time_percentage' => 0,
        ];

        // Only calculate if driver has proper relationships
        if (is_object($driver) && method_exists($driver, 'trips') && isset($driver->id)) {
            try {
                $stats = [
                    'total_trips' => $driver->trips()->count(),
                    'completed_trips' => $driver->trips()->where('status', 'completed')->count(),
                    'total_distance' => $driver->trips()->where('status', 'completed')->sum('distance_covered') ?? 0,
                    'fuel_consumed' => $driver->trips()->where('status', 'completed')->sum('fuel_used') ?? 0,
                    'on_time_percentage' => $this->calculateOnTimePercentage($driver),
                ];
            } catch (\Exception $e) {
                // Keep default values if queries fail
            }
        }

        // License information (safe defaults)
        $license = null;
        $licenseExpiry = null;
        $daysToExpiry = null;

        try {
            if (is_object($driver) && method_exists($driver, 'driverLicense')) {
                $license = $driver->driverLicense;
            } elseif (is_object($driver) && isset($driver->user)) {
                // Create a mock license object
                $license = (object) [
                    'license_number' => $driver->user->personal_number ?? 'N/A',
                    'expiry_date' => now()->addYear(),
                    'status' => 'active'
                ];
            }

            $licenseExpiry = $license ? $license->expiry_date : null;
            $daysToExpiry = $licenseExpiry ? now()->diffInDays($licenseExpiry, false) : null;
        } catch (\Exception $e) {
            // Keep null values if license queries fail
        }

        // Performance metrics
        $performanceMetrics = [
            'safety_score' => $this->calculateSafetyScore($driver),
            'fuel_efficiency' => $this->calculateFuelEfficiency($driver),
            'punctuality_score' => $this->calculatePunctualityScore($driver),
        ];

        // Recent notifications/alerts
        $alerts = collect();
        if ($licenseExpiry && $daysToExpiry <= 30) {
            $alerts->push([
                'type' => 'warning',
                'message' => "Your license expires in {$daysToExpiry} days",
                'action' => 'Renew License'
            ]);
        }

        if ($assignedVehicle) {
            $maintenanceDue = $assignedVehicle->maintenanceSchedules()
                ->where('status', 'due')
                ->count();
            if ($maintenanceDue > 0) {
                $alerts->push([
                    'type' => 'info',
                    'message' => "Vehicle has {$maintenanceDue} maintenance item(s) due",
                    'action' => 'View Details'
                ]);
            }
        }

        return view('driver.dashboard', compact(
            'driver',
            'currentTrip',
            'upcomingTrips',
            'recentTrips',
            'assignedVehicle',
            'stats',
            'license',
            'daysToExpiry',
            'performanceMetrics',
            'alerts'
        ));
    }

    private function calculateOnTimePercentage($driver)
    {
        try {
            if (!is_object($driver) || !method_exists($driver, 'trips') || !isset($driver->id)) {
                return 0;
            }

            $completedTrips = $driver->trips()->where('status', 'completed')->count();
            if ($completedTrips === 0) return 0;

            $onTimeTrips = $driver->trips()
                ->where('status', 'completed')
                ->whereRaw('actual_start_time <= scheduled_start_time + interval \'15 minutes\'')
                ->count();

            return round(($onTimeTrips / $completedTrips) * 100, 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculateSafetyScore($driver)
    {
        try {
            // Base score starts at 100
            $score = 100;

            // Deduct points for incidents (if incidents table exists)
            $incidents = 0; // Placeholder - implement when incidents are available
            $score -= ($incidents * 10);

            // Add points for trip completion without issues
            if (is_object($driver) && method_exists($driver, 'trips') && isset($driver->id)) {
                $completedTrips = $driver->trips()->where('status', 'completed')->count();
                $score += min($completedTrips * 0.5, 20);
            }

            return max(min($score, 100), 0);
        } catch (\Exception $e) {
            return 85; // Default good score
        }
    }

    private function calculateFuelEfficiency($driver)
    {
        try {
            if (!is_object($driver) || !method_exists($driver, 'trips') || !isset($driver->id)) {
                return 0;
            }

            $trips = $driver->trips()
                ->where('status', 'completed')
                ->where('distance_covered', '>', 0)
                ->where('fuel_used', '>', 0)
                ->get();

            if ($trips->isEmpty()) return 0;

            $totalDistance = $trips->sum('distance_covered');
            $totalFuel = $trips->sum('fuel_used');

            return $totalFuel > 0 ? round($totalDistance / $totalFuel, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculatePunctualityScore($driver)
    {
        return $this->calculateOnTimePercentage($driver);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:drivers',
            'license_id' => 'required|exists:driver_licenses,id|unique:drivers',
            'hire_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $driver = Driver::create($request->all());
        return response()->json($driver, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        return $driver->load(['user', 'driverLicense', 'driverStatus']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id|unique:drivers,user_id,' . $driver->id,
            'license_id' => 'sometimes|required|exists:driver_licenses,id|unique:drivers,license_id,' . $driver->id,
            'hire_date' => 'sometimes|required|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $driver->update($request->all());
        return response()->json($driver, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        $driver->delete();
        return response()->json(null, 204);
    }
}

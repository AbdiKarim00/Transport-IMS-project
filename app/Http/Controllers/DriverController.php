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
        $driver = auth()->user()->driver;

        if (!$driver) {
            return redirect()->route('dashboard')->with('error', 'Driver profile not found.');
        }

        // Get current assignments and trips
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

        // Driver statistics
        $stats = [
            'total_trips' => $driver->trips()->count(),
            'completed_trips' => $driver->trips()->where('status', 'completed')->count(),
            'total_distance' => $driver->trips()->where('status', 'completed')->sum('distance_covered') ?? 0,
            'fuel_consumed' => $driver->trips()->where('status', 'completed')->sum('fuel_used') ?? 0,
            'on_time_percentage' => $this->calculateOnTimePercentage($driver),
        ];

        // License information
        $license = $driver->driverLicense;
        $licenseExpiry = $license ? $license->expiry_date : null;
        $daysToExpiry = $licenseExpiry ? now()->diffInDays($licenseExpiry, false) : null;

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
        $completedTrips = $driver->trips()->where('status', 'completed')->count();
        if ($completedTrips === 0) return 0;

        $onTimeTrips = $driver->trips()
            ->where('status', 'completed')
            ->whereRaw('actual_start_time <= scheduled_start_time + interval \'15 minutes\'')
            ->count();

        return round(($onTimeTrips / $completedTrips) * 100, 1);
    }

    private function calculateSafetyScore($driver)
    {
        // Base score starts at 100
        $score = 100;

        // Deduct points for incidents (if incidents table exists)
        $incidents = 0; // Placeholder - implement when incidents are available
        $score -= ($incidents * 10);

        // Add points for trip completion without issues
        $completedTrips = $driver->trips()->where('status', 'completed')->count();
        $score += min($completedTrips * 0.5, 20);

        return max(min($score, 100), 0);
    }

    private function calculateFuelEfficiency($driver)
    {
        $trips = $driver->trips()
            ->where('status', 'completed')
            ->where('distance_covered', '>', 0)
            ->where('fuel_used', '>', 0)
            ->get();

        if ($trips->isEmpty()) return 0;

        $totalDistance = $trips->sum('distance_covered');
        $totalFuel = $trips->sum('fuel_used');

        return $totalFuel > 0 ? round($totalDistance / $totalFuel, 2) : 0;
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

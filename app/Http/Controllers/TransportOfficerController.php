<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransportOfficerController extends Controller
{
    public function index()
    {
        // Trip Management Statistics
        $tripStats = [
            'total_trips_today' => Trip::whereDate('scheduled_start_time', today())->count(),
            'active_trips' => Trip::whereIn('status', ['in_progress', 'scheduled'])->count(),
            'completed_trips_today' => Trip::whereDate('actual_end_time', today())->where('status', 'completed')->count(),
            'pending_assignments' => Trip::where('status', 'scheduled')->whereNull('driver_id')->count(),
        ];

        // Driver Management
        $driverStats = [
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::whereHas('currentStatus', function($q) {
                $q->where('status', 'Active');
            })->count(),
            'drivers_on_trip' => Driver::whereHas('trips', function($q) {
                $q->where('status', 'in_progress');
            })->count(),
            'available_drivers' => Driver::whereHas('currentStatus', function($q) {
                $q->where('status', 'Active');
            })->whereDoesntHave('trips', function($q) {
                $q->whereIn('status', ['in_progress', 'scheduled']);
            })->count(),
        ];

        // Vehicle Utilization
        $vehicleStats = [
            'total_vehicles' => Vehicle::count(),
            'vehicles_in_use' => Vehicle::whereHas('trips', function($q) {
                $q->where('status', 'in_progress');
            })->count(),
            'available_vehicles' => Vehicle::where('asset_condition', 'Active')
                ->whereDoesntHave('trips', function($q) {
                    $q->whereIn('status', ['in_progress', 'scheduled']);
                })->count(),
            'maintenance_due' => Vehicle::whereHas('maintenanceSchedules', function($q) {
                $q->where('status', 'due');
            })->count(),
        ];

        // Today's Schedule
        $todaysTrips = Trip::with(['driver.user', 'vehicle', 'route'])
            ->whereDate('scheduled_start_time', today())
            ->orderBy('scheduled_start_time')
            ->get();

        // Active Trips
        $activeTrips = Trip::with(['driver.user', 'vehicle', 'route'])
            ->where('status', 'in_progress')
            ->orderBy('actual_start_time', 'desc')
            ->get();

        // Pending Assignments
        $pendingAssignments = Trip::with(['vehicle', 'route'])
            ->where('status', 'scheduled')
            ->whereNull('driver_id')
            ->orderBy('scheduled_start_time')
            ->limit(10)
            ->get();

        // Available Drivers
        $availableDrivers = Driver::with(['user', 'currentStatus'])
            ->whereHas('currentStatus', function($q) {
                $q->where('status', 'Active');
            })
            ->whereDoesntHave('trips', function($q) {
                $q->whereIn('status', ['in_progress', 'scheduled']);
            })
            ->limit(10)
            ->get();

        // Recent Activity
        $recentActivity = Trip::with(['driver.user', 'vehicle'])
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();

        // Performance Metrics
        $performanceMetrics = [
            'on_time_percentage' => $this->calculateOnTimePercentage(),
            'trip_completion_rate' => $this->calculateCompletionRate(),
            'average_trip_duration' => $this->calculateAverageTripDuration(),
            'fuel_efficiency' => $this->calculateFleetFuelEfficiency(),
        ];

        // Route Statistics
        $routeStats = Route::withCount(['trips as total_trips', 'trips as completed_trips' => function($q) {
            $q->where('status', 'completed');
        }])
        ->orderBy('total_trips', 'desc')
        ->limit(5)
        ->get();

        // Weekly Trip Trends (last 7 days)
        $weeklyTrends = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyTrends->push([
                'date' => $date->format('M d'),
                'scheduled' => Trip::whereDate('scheduled_start_time', $date)->count(),
                'completed' => Trip::whereDate('actual_end_time', $date)->where('status', 'completed')->count(),
            ]);
        }

        return view('transport_officer.dashboard', compact(
            'tripStats',
            'driverStats',
            'vehicleStats',
            'todaysTrips',
            'activeTrips',
            'pendingAssignments',
            'availableDrivers',
            'recentActivity',
            'performanceMetrics',
            'routeStats',
            'weeklyTrends'
        ));
    }

    private function calculateOnTimePercentage()
    {
        $completedTrips = Trip::where('status', 'completed')->count();
        if ($completedTrips === 0) return 0;

        $onTimeTrips = Trip::where('status', 'completed')
            ->whereRaw('actual_start_time <= scheduled_start_time + interval \'15 minutes\'')
            ->count();

        return round(($onTimeTrips / $completedTrips) * 100, 1);
    }

    private function calculateCompletionRate()
    {
        $totalTrips = Trip::whereIn('status', ['completed', 'cancelled'])->count();
        if ($totalTrips === 0) return 0;

        $completedTrips = Trip::where('status', 'completed')->count();
        return round(($completedTrips / $totalTrips) * 100, 1);
    }

    private function calculateAverageTripDuration()
    {
        $avgDuration = Trip::where('status', 'completed')
            ->whereNotNull('actual_start_time')
            ->whereNotNull('actual_end_time')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (actual_end_time - actual_start_time))/3600) as avg_hours')
            ->value('avg_hours');

        return $avgDuration ? round($avgDuration, 1) : 0;
    }

    private function calculateFleetFuelEfficiency()
    {
        $trips = Trip::where('status', 'completed')
            ->where('distance_covered', '>', 0)
            ->where('fuel_used', '>', 0)
            ->get();

        if ($trips->isEmpty()) return 0;

        $totalDistance = $trips->sum('distance_covered');
        $totalFuel = $trips->sum('fuel_used');

        return $totalFuel > 0 ? round($totalDistance / $totalFuel, 2) : 0;
    }

    public function assignDriver(Request $request, Trip $trip)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id'
        ]);

        $trip->update(['driver_id' => $request->driver_id]);

        return redirect()->back()->with('success', 'Driver assigned successfully');
    }

    public function updateTripStatus(Request $request, Trip $trip)
    {
        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled'
        ]);

        $trip->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Trip status updated successfully');
    }
}

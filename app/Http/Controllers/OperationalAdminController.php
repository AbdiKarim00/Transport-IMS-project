<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\MaintenanceSchedule;
use App\Models\FuelCard;
use App\Models\FuelCardTransaction;
use App\Models\Trip;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperationalAdminController extends Controller
{
    public function index()
    {
        // Fleet Overview
        $fleetStats = [
            'total_vehicles' => Vehicle::count(),
            'active_vehicles' => Vehicle::where('asset_condition', 'Active')->count(),
            'maintenance_vehicles' => Vehicle::where('asset_condition', 'Under Maintenance')->count(),
            'out_of_service' => Vehicle::where('asset_condition', 'Out of Service')->count(),
        ];

        // Maintenance Management
        $maintenanceStats = [
            'scheduled_maintenance' => MaintenanceSchedule::where('status', 'scheduled')->count(),
            'due_maintenance' => MaintenanceSchedule::where('status', 'due')->count(),
            'in_progress' => MaintenanceSchedule::where('status', 'in_progress')->count(),
            'overdue_maintenance' => MaintenanceSchedule::where('status', 'due')
                ->where('scheduled_date', '<', now())->count(),
        ];

        // Fuel Management
        $fuelStats = [
            'active_fuel_cards' => FuelCard::where('status', 'Active')->count(),
            'monthly_fuel_cost' => FuelCardTransaction::whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'monthly_fuel_volume' => FuelCardTransaction::whereMonth('transaction_date', now()->month)
                ->sum('liters'),
            'average_fuel_price' => $this->calculateAverageFuelPrice(),
        ];

        // Cost Management
        $costStats = [
            'monthly_maintenance_cost' => MaintenanceSchedule::whereMonth('created_at', now()->month)
                ->sum('estimated_cost'),
            'monthly_fuel_cost' => $fuelStats['monthly_fuel_cost'],
            'cost_per_km' => $this->calculateCostPerKm(),
            'budget_utilization' => $this->calculateBudgetUtilization(),
        ];

        // Upcoming Maintenance
        $upcomingMaintenance = MaintenanceSchedule::with(['vehicle', 'serviceProvider'])
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date')
            ->limit(10)
            ->get();

        // Overdue Maintenance
        $overdueMaintenance = MaintenanceSchedule::with(['vehicle', 'serviceProvider'])
            ->where('status', 'due')
            ->where('scheduled_date', '<', now())
            ->orderBy('scheduled_date')
            ->limit(10)
            ->get();

        // Vehicle Utilization
        $vehicleUtilization = Vehicle::withCount(['trips as monthly_trips' => function($q) {
            $q->whereMonth('scheduled_start_time', now()->month);
        }])
        ->with(['trips' => function($q) {
            $q->whereMonth('scheduled_start_time', now()->month)
              ->selectRaw('vehicle_id, SUM(distance_covered) as total_distance, SUM(fuel_used) as total_fuel')
              ->groupBy('vehicle_id');
        }])
        ->where('asset_condition', 'Active')
        ->orderBy('monthly_trips', 'desc')
        ->limit(10)
        ->get();

        // Service Provider Performance
        $serviceProviders = ServiceProvider::withCount(['maintenanceSchedules as total_services'])
            ->with(['maintenanceSchedules' => function($q) {
                $q->selectRaw('service_provider_id, AVG(estimated_cost) as avg_cost, COUNT(*) as completed_services')
                  ->where('status', 'completed')
                  ->groupBy('service_provider_id');
            }])
            ->orderBy('total_services', 'desc')
            ->limit(5)
            ->get();

        // Recent Fuel Transactions
        $recentFuelTransactions = FuelCardTransaction::with(['fuelCard'])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Fleet Performance Trends (last 30 days)
        $performanceTrends = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $performanceTrends->push([
                'date' => $date->format('M d'),
                'trips' => Trip::whereDate('scheduled_start_time', $date)->count(),
                'fuel_cost' => FuelCardTransaction::whereDate('transaction_date', $date)->sum('amount'),
                'maintenance_cost' => MaintenanceSchedule::whereDate('created_at', $date)->sum('estimated_cost'),
            ]);
        }

        // Vehicle Condition Distribution
        $vehicleConditions = Vehicle::select('asset_condition', DB::raw('count(*) as count'))
            ->groupBy('asset_condition')
            ->get();

        // Maintenance Cost by Type
        $maintenanceCostByType = MaintenanceSchedule::select('maintenance_type', DB::raw('SUM(estimated_cost) as total_cost'))
            ->whereMonth('created_at', now()->month)
            ->groupBy('maintenance_type')
            ->orderBy('total_cost', 'desc')
            ->get();

        // Fuel Efficiency by Vehicle
        $fuelEfficiencyData = Vehicle::with(['trips' => function($q) {
            $q->where('status', 'completed')
              ->whereMonth('actual_end_time', now()->month)
              ->selectRaw('vehicle_id, SUM(distance_covered) as total_distance, SUM(fuel_used) as total_fuel')
              ->groupBy('vehicle_id');
        }])
        ->get()
        ->map(function($vehicle) {
            $trip = $vehicle->trips->first();
            if ($trip && $trip->total_fuel > 0) {
                return [
                    'vehicle' => $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->license_plate . ')',
                    'efficiency' => round($trip->total_distance / $trip->total_fuel, 2),
                    'distance' => $trip->total_distance,
                    'fuel' => $trip->total_fuel,
                ];
            }
            return null;
        })
        ->filter()
        ->sortByDesc('efficiency')
        ->take(10);

        // Budget Alerts
        $alerts = collect();

        // High maintenance costs
        if ($costStats['monthly_maintenance_cost'] > 50000) {
            $alerts->push([
                'type' => 'warning',
                'message' => 'Monthly maintenance costs are above budget threshold',
                'value' => number_format($costStats['monthly_maintenance_cost'], 2),
            ]);
        }

        // Overdue maintenance
        if ($maintenanceStats['overdue_maintenance'] > 0) {
            $alerts->push([
                'type' => 'danger',
                'message' => 'Vehicles with overdue maintenance',
                'value' => $maintenanceStats['overdue_maintenance'],
            ]);
        }

        return view('operational_admin.dashboard', compact(
            'fleetStats',
            'maintenanceStats',
            'fuelStats',
            'costStats',
            'upcomingMaintenance',
            'overdueMaintenance',
            'vehicleUtilization',
            'serviceProviders',
            'recentFuelTransactions',
            'performanceTrends',
            'vehicleConditions',
            'maintenanceCostByType',
            'fuelEfficiencyData',
            'alerts'
        ));
    }

    private function calculateAverageFuelPrice()
    {
        $avg = FuelCardTransaction::whereMonth('transaction_date', now()->month)
            ->where('liters', '>', 0)
            ->selectRaw('AVG(amount / liters) as avg_price')
            ->value('avg_price');

        return $avg ? round($avg, 2) : 0;
    }

    private function calculateCostPerKm()
    {
        $totalDistance = Trip::whereMonth('actual_end_time', now()->month)
            ->where('status', 'completed')
            ->sum('distance_covered');

        $totalCost = $this->fuelStats['monthly_fuel_cost'] ?? 0 + $this->costStats['monthly_maintenance_cost'] ?? 0;

        return $totalDistance > 0 ? round($totalCost / $totalDistance, 2) : 0;
    }

    private function calculateBudgetUtilization()
    {
        // Placeholder - implement based on your budget system
        $monthlyBudget = 100000; // Example budget
        $currentSpend = ($this->fuelStats['monthly_fuel_cost'] ?? 0) + ($this->costStats['monthly_maintenance_cost'] ?? 0);

        return $monthlyBudget > 0 ? round(($currentSpend / $monthlyBudget) * 100, 1) : 0;
    }

    public function updateMaintenanceStatus(Request $request, MaintenanceSchedule $maintenance)
    {
        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled'
        ]);

        $maintenance->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Maintenance status updated successfully');
    }

    public function scheduleVehicleMaintenance(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'maintenance_type' => 'required|string',
            'scheduled_date' => 'required|date',
            'estimated_cost' => 'nullable|numeric',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'description' => 'nullable|string',
        ]);

        MaintenanceSchedule::create($request->all());

        return redirect()->back()->with('success', 'Maintenance scheduled successfully');
    }
}

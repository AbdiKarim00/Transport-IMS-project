@extends('layouts.operational_admin')

@section('title', 'Operational Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Operational Management Center</h1>
        <p class="text-gray-600 mt-1">Monitor fleet operations, maintenance, and costs</p>
    </div>

    <!-- Alert Section -->
    @if($alerts->count() > 0)
    <div class="mb-6">
        @foreach($alerts as $alert)
        <div class="bg-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') }}-50 border border-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') }}-200 rounded-lg p-4 mb-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-{{ $alert['type'] === 'danger' ? 'exclamation-triangle' : ($alert['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle') }} text-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') }}-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') }}-800">
                        {{ $alert['message'] }}: <strong>{{ $alert['value'] }}</strong>
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Fleet Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-car text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Vehicles</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $fleetStats['active_vehicles'] }}</p>
                    <p class="text-sm text-gray-500">of {{ $fleetStats['total_vehicles'] }} total</p>
                </div>
            </div>
        </div>

        <!-- Maintenance Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-wrench text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Due Maintenance</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $maintenanceStats['due_maintenance'] }}</p>
                    <p class="text-sm text-gray-500">{{ $maintenanceStats['overdue_maintenance'] }} overdue</p>
                </div>
            </div>
        </div>

        <!-- Monthly Fuel Cost -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-gas-pump text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Monthly Fuel Cost</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($fuelStats['monthly_fuel_cost'], 0) }}</p>
                    <p class="text-sm text-gray-500">{{ number_format($fuelStats['monthly_fuel_volume']) }} L</p>
                </div>
            </div>
        </div>

        <!-- Budget Utilization -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-chart-pie text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Budget Used</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $costStats['budget_utilization'] }}%</p>
                    <p class="text-sm text-gray-500">This month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">${{ number_format($costStats['monthly_maintenance_cost'], 0) }}</div>
                <p class="text-sm text-gray-600 mt-1">Maintenance Cost</p>
                <p class="text-xs text-gray-500">This month</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">${{ number_format($costStats['cost_per_km'], 2) }}</div>
                <p class="text-sm text-gray-600 mt-1">Cost per KM</p>
                <p class="text-xs text-gray-500">Fleet average</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">${{ number_format($fuelStats['average_fuel_price'], 2) }}</div>
                <p class="text-sm text-gray-600 mt-1">Avg Fuel Price</p>
                <p class="text-xs text-gray-500">Per liter</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Overdue Maintenance -->
            @if($overdueMaintenance->count() > 0)
            <div class="bg-white rounded-lg shadow border-l-4 border-red-500">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-red-700 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Overdue Maintenance ({{ $overdueMaintenance->count() }})
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($overdueMaintenance as $maintenance)
                        <div class="border border-red-200 bg-red-50 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-red-900">{{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }}</p>
                                    <p class="text-sm text-red-700">{{ $maintenance->vehicle->license_plate }}</p>
                                    <p class="text-sm text-red-600">{{ $maintenance->maintenance_type }}</p>
                                    <p class="text-xs text-red-500 mt-1">Due: {{ $maintenance->scheduled_date->format('M d, Y') }} ({{ $maintenance->scheduled_date->diffForHumans() }})</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-red-900">${{ number_format($maintenance->estimated_cost, 0) }}</p>
                                    <button class="mt-2 bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                                        Schedule Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Upcoming Maintenance -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Upcoming Maintenance</h3>
                </div>
                <div class="p-6">
                    @if($upcomingMaintenance->count() > 0)
                        <div class="space-y-4">
                            @foreach($upcomingMaintenance as $maintenance)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }}</p>
                                        <p class="text-sm text-gray-600">{{ $maintenance->vehicle->license_plate }}</p>
                                        <p class="text-sm text-gray-700">{{ $maintenance->maintenance_type }}</p>
                                        @if($maintenance->serviceProvider)
                                            <p class="text-sm text-gray-500">{{ $maintenance->serviceProvider->name }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">{{ $maintenance->scheduled_date->format('M d, Y g:i A') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">${{ number_format($maintenance->estimated_cost, 0) }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                            {{ ucfirst($maintenance->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-calendar-check text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No upcoming maintenance scheduled</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Vehicle Utilization -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Vehicle Utilization (This Month)</h3>
                </div>
                <div class="p-6">
                    @if($vehicleUtilization->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trips</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fuel</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efficiency</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($vehicleUtilization->take(8) as $vehicle)
                                    @php
                                        $trip = $vehicle->trips->first();
                                        $efficiency = ($trip && $trip->total_fuel > 0) ? round($trip->total_distance / $trip->total_fuel, 2) : 0;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $vehicle->license_plate }}</div>
                                            <div class="text-sm text-gray-500">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $vehicle->monthly_trips }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $trip ? number_format($trip->total_distance, 0) : 0 }} km
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $trip ? number_format($trip->total_fuel, 0) : 0 }} L
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $efficiency }} km/L
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-chart-bar text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No utilization data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Fuel Transactions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Fuel Transactions</h3>
                </div>
                <div class="p-6">
                    @if($recentFuelTransactions->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentFuelTransactions->take(8) as $transaction)
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $transaction->fuelCard->card_number ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-600">{{ number_format($transaction->liters, 1) }} L • {{ $transaction->location ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->transaction_date->format('M d, Y g:i A') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">${{ number_format($transaction->amount, 2) }}</p>
                                    <p class="text-xs text-gray-500">${{ number_format($transaction->amount / $transaction->liters, 2) }}/L</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-gas-pump text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No recent fuel transactions</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column (1/3 width) -->
        <div class="space-y-6">
            <!-- Fleet Status Distribution -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Fleet Status</h3>
                </div>
                <div class="p-6">
                    @if($vehicleConditions->count() > 0)
                        <div class="space-y-4">
                            @foreach($vehicleConditions as $condition)
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full mr-3 
                                        {{ $condition->asset_condition === 'Active' ? 'bg-green-500' : 
                                           ($condition->asset_condition === 'Under Maintenance' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $condition->asset_condition }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $condition->count }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No vehicle data</p>
                    @endif
                </div>
            </div>

            <!-- Service Provider Performance -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Top Service Providers</h3>
                </div>
                <div class="p-6">
                    @if($serviceProviders->count() > 0)
                        <div class="space-y-4">
                            @foreach($serviceProviders as $provider)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $provider->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $provider->total_services }} services</p>
                                </div>
                                <div class="text-right">
                                    @if($provider->maintenanceSchedules->first())
                                        <p class="text-sm text-gray-900">${{ number_format($provider->maintenanceSchedules->first()->avg_cost, 0) }}</p>
                                        <p class="text-xs text-gray-500">avg cost</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No service provider data</p>
                    @endif
                </div>
            </div>

            <!-- Maintenance Cost by Type -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Maintenance Costs</h3>
                    <p class="text-sm text-gray-500">By type this month</p>
                </div>
                <div class="p-6">
                    @if($maintenanceCostByType->count() > 0)
                        <div class="space-y-3">
                            @foreach($maintenanceCostByType as $type)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">{{ $type->maintenance_type }}</span>
                                <span class="text-sm font-medium text-gray-900">${{ number_format($type->total_cost, 0) }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No maintenance data</p>
                    @endif
                </div>
            </div>

            <!-- Fuel Efficiency Leaders -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Fuel Efficiency</h3>
                    <p class="text-sm text-gray-500">Top performers</p>
                </div>
                <div class="p-6">
                    @if($fuelEfficiencyData->count() > 0)
                        <div class="space-y-3">
                            @foreach($fuelEfficiencyData->take(5) as $vehicle)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $vehicle['vehicle'] }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($vehicle['distance']) }} km • {{ number_format($vehicle['fuel']) }} L</p>
                                </div>
                                <span class="text-sm font-medium text-green-600">{{ $vehicle['efficiency'] }} km/L</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No efficiency data</p>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Schedule Maintenance
                    </button>
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-gas-pump mr-2"></i>
                        Fuel Management
                    </button>
                    <button class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-users mr-2"></i>
                        Service Providers
                    </button>
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-chart-line mr-2"></i>
                        Cost Reports
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Scheduling Modal -->
<div id="maintenanceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 text-center">Schedule Maintenance</h3>
            <div class="mt-4">
                <form id="maintenanceForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Vehicle</label>
                        <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select vehicle...</option>
                            @foreach($vehicleUtilization as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->license_plate }} - {{ $vehicle->make }} {{ $vehicle->model }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                        <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select type...</option>
                            <option value="Oil Change">Oil Change</option>
                            <option value="Brake Service">Brake Service</option>
                            <option value="Tire Replacement">Tire Replacement</option>
                            <option value="Engine Service">Engine Service</option>
                            <option value="General Inspection">General Inspection</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                        <input type="datetime-local" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Service Provider</label>
                        <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select provider...</option>
                            @foreach($serviceProviders as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-between">
                        <button type="button" onclick="closeMaintenanceModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openMaintenanceModal() {
        document.getElementById('maintenanceModal').classList.remove('hidden');
    }

    function closeMaintenanceModal() {
        document.getElementById('maintenanceModal').classList.add('hidden');
    }

    // Auto-refresh every 2 minutes for live data
    setInterval(function() {
        location.reload();
    }, 120000);
</script>
@endpush

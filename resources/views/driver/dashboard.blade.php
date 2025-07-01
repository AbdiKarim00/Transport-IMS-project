@extends('layouts.driver')

@section('title', 'Driver Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ auth()->user()->first_name }}!</h1>
        <p class="text-gray-600 mt-1">Here's your driving overview for today</p>
    </div>

    <!-- Alerts Section -->
    @if($alerts->count() > 0)
    <div class="mb-6">
        @foreach($alerts as $alert)
        <div class="bg-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-50 border border-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-200 rounded-lg p-4 mb-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-{{ $alert['type'] === 'warning' ? 'exclamation-triangle text-yellow-600' : 'info-circle text-blue-600' }}"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-800">
                        {{ $alert['message'] }}
                    </p>
                </div>
                @if(isset($alert['action']))
                <div class="ml-auto">
                    <button class="text-sm bg-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-100 hover:bg-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-200 px-3 py-1 rounded">
                        {{ $alert['action'] }}
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-road text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Trips</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_trips']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['completed_trips']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-route text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Distance (km)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_distance'], 1) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">On-Time %</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['on_time_percentage'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Trip -->
            @if($currentTrip)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Current Trip</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $currentTrip->status === 'in_progress' ? 'green' : 'blue' }}-100 text-{{ $currentTrip->status === 'in_progress' ? 'green' : 'blue' }}-800">
                                {{ ucfirst(str_replace('_', ' ', $currentTrip->status)) }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500">
                            Trip #{{ $currentTrip->id }}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Vehicle</p>
                            <p class="text-gray-900">{{ $currentTrip->vehicle->make }} {{ $currentTrip->vehicle->model }}</p>
                            <p class="text-sm text-gray-500">{{ $currentTrip->vehicle->license_plate }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Scheduled Time</p>
                            <p class="text-gray-900">{{ $currentTrip->scheduled_start_time->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $currentTrip->scheduled_start_time->format('g:i A') }}</p>
                        </div>
                    </div>

                    @if($currentTrip->route)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600">Route</p>
                        <p class="text-gray-900">{{ $currentTrip->route->start_location }} → {{ $currentTrip->route->end_location }}</p>
                    </div>
                    @endif

                    @if($currentTrip->status === 'scheduled')
                    <div class="mt-4">
                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Start Trip
                        </button>
                    </div>
                    @elseif($currentTrip->status === 'in_progress')
                    <div class="mt-4">
                        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            End Trip
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Upcoming Trips -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Upcoming Trips</h3>
                </div>
                <div class="p-6">
                    @if($upcomingTrips->count() > 0)
                        <div class="space-y-4">
                            @foreach($upcomingTrips as $trip)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-900">Trip #{{ $trip->id }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Scheduled
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $trip->vehicle->make }} {{ $trip->vehicle->model }} ({{ $trip->vehicle->license_plate }})
                                        </p>
                                        @if($trip->route)
                                        <p class="text-sm text-gray-500">
                                            {{ $trip->route->start_location }} → {{ $trip->route->end_location }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">{{ $trip->scheduled_start_time->format('M d') }}</p>
                                        <p class="text-sm text-gray-500">{{ $trip->scheduled_start_time->format('g:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No upcoming trips scheduled</p>
                    @endif
                </div>
            </div>

            <!-- Recent Trips -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Trips</h3>
                </div>
                <div class="p-6">
                    @if($recentTrips->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentTrips->take(5) as $trip)
                            <div class="flex justify-between items-center py-2">
                                <div>
                                    <p class="font-medium text-gray-900">Trip #{{ $trip->id }}</p>
                                    <p class="text-sm text-gray-600">{{ $trip->vehicle->license_plate }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                        {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($trip->status) }}
                                    </span>
                                    <p class="text-sm text-gray-500 mt-1">{{ $trip->updated_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No recent trips</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Assigned Vehicle -->
            @if($assignedVehicle)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Assigned Vehicle</h3>
                </div>
                <div class="p-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-car text-blue-600 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900">{{ $assignedVehicle->make }} {{ $assignedVehicle->model }}</h4>
                        <p class="text-gray-600">{{ $assignedVehicle->license_plate }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-2">
                            {{ $assignedVehicle->asset_condition }}
                        </span>
                    </div>
                    
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Year</span>
                            <span class="text-sm font-medium">{{ $assignedVehicle->year ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Mileage</span>
                            <span class="text-sm font-medium">{{ number_format($assignedVehicle->current_mileage ?? 0) }} km</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Fuel Type</span>
                            <span class="text-sm font-medium">{{ $assignedVehicle->fuel_type ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Performance Metrics -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Performance</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-600">Safety Score</span>
                            <span class="text-sm font-medium text-gray-900">{{ $performanceMetrics['safety_score'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $performanceMetrics['safety_score'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-600">Punctuality</span>
                            <span class="text-sm font-medium text-gray-900">{{ $performanceMetrics['punctuality_score'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $performanceMetrics['punctuality_score'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-600">Fuel Efficiency</span>
                            <span class="text-sm font-medium text-gray-900">{{ $performanceMetrics['fuel_efficiency'] }} km/L</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Information -->
            @if($license)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">License Status</h3>
                </div>
                <div class="p-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-{{ $daysToExpiry <= 30 ? 'red' : 'green' }}-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-id-card text-{{ $daysToExpiry <= 30 ? 'red' : 'green' }}-600 text-2xl"></i>
                        </div>
                        <p class="text-sm text-gray-600">License Number</p>
                        <p class="font-semibold text-gray-900">{{ $license->license_number }}</p>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Expires on</p>
                            <p class="font-medium text-gray-900">{{ $license->expiry_date->format('M d, Y') }}</p>
                            @if($daysToExpiry !== null)
                                @if($daysToExpiry > 0)
                                    <p class="text-sm text-{{ $daysToExpiry <= 30 ? 'red' : 'green' }}-600">
                                        {{ $daysToExpiry }} days remaining
                                    </p>
                                @else
                                    <p class="text-sm text-red-600 font-medium">Expired</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Vehicle Inspection
                    </button>
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-gas-pump mr-2"></i>
                        Log Fuel
                    </button>
                    <button class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-wrench mr-2"></i>
                        Report Issue
                    </button>
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-chart-line mr-2"></i>
                        View Reports
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any driver dashboard specific JavaScript here
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh every 5 minutes for live updates
        setInterval(function() {
            if (document.querySelector('[data-current-trip]')) {
                location.reload();
            }
        }, 300000);
    });
</script>
@endpush

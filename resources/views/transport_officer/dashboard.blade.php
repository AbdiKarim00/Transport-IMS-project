@extends('layouts.transport_officer')

@section('title', 'Transport Officer Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Transport Operations Center</h1>
        <p class="text-gray-600 mt-1">Monitor and manage fleet operations in real-time</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today's Trips</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tripStats['total_trips_today'] }}</p>
                    <p class="text-sm text-gray-500">{{ $tripStats['completed_trips_today'] }} completed</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-play-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Trips</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tripStats['active_trips'] }}</p>
                    <p class="text-sm text-gray-500">In progress</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Available Drivers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $driverStats['available_drivers'] }}</p>
                    <p class="text-sm text-gray-500">of {{ $driverStats['total_drivers'] }} total</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Assignments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tripStats['pending_assignments'] }}</p>
                    <p class="text-sm text-gray-500">Need attention</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $performanceMetrics['on_time_percentage'] }}%</div>
                <p class="text-sm text-gray-600 mt-1">On-Time Performance</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $performanceMetrics['trip_completion_rate'] }}%</div>
                <p class="text-sm text-gray-600 mt-1">Completion Rate</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ $performanceMetrics['average_trip_duration'] }}h</div>
                <p class="text-sm text-gray-600 mt-1">Avg Trip Duration</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600">{{ $performanceMetrics['fuel_efficiency'] }}</div>
                <p class="text-sm text-gray-600 mt-1">Fleet Efficiency (km/L)</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Active Trips -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Active Trips</h3>
                </div>
                <div class="p-6">
                    @if($activeTrips->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trip</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($activeTrips as $trip)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#{{ $trip->id }}</div>
                                            <div class="text-sm text-gray-500">{{ $trip->actual_start_time->format('g:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($trip->driver)
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-gray-600">{{ substr($trip->driver->user->first_name, 0, 1) }}{{ substr($trip->driver->user->last_name, 0, 1) }}</span>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $trip->driver->user->first_name }} {{ $trip->driver->user->last_name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $trip->driver->employee_id ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $trip->vehicle->license_plate }}</div>
                                            <div class="text-sm text-gray-500">{{ $trip->vehicle->make }} {{ $trip->vehicle->model }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($trip->route)
                                                <div class="text-sm text-gray-900">{{ $trip->route->start_location }}</div>
                                                <div class="text-sm text-gray-500">→ {{ $trip->route->end_location }}</div>
                                            @else
                                                <span class="text-sm text-gray-500">No route</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                In Progress
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-route text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No active trips at the moment</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Today's Schedule</h3>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>New Trip
                    </button>
                </div>
                <div class="p-6">
                    @if($todaysTrips->count() > 0)
                        <div class="space-y-4">
                            @foreach($todaysTrips as $trip)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-900">Trip #{{ $trip->id }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($trip->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                                   ($trip->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $trip->status)) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">
                                            @if($trip->driver)
                                                {{ $trip->driver->user->first_name }} {{ $trip->driver->user->last_name }} • 
                                            @endif
                                            {{ $trip->vehicle->license_plate }}
                                        </p>
                                        @if($trip->route)
                                        <p class="text-sm text-gray-500">
                                            {{ $trip->route->start_location }} → {{ $trip->route->end_location }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">{{ $trip->scheduled_start_time->format('g:i A') }}</p>
                                        @if($trip->status === 'scheduled')
                                            <div class="mt-2 space-x-2">
                                                <button class="text-xs bg-green-100 hover:bg-green-200 text-green-800 px-2 py-1 rounded">Start</button>
                                                <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded">Edit</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No trips scheduled for today</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                </div>
                <div class="p-6">
                    @if($recentActivity->count() > 0)
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($recentActivity->take(8) as $index => $trip)
                                <li>
                                    <div class="relative pb-8">
                                        @if($index < $recentActivity->take(8)->count() - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-{{ $trip->status === 'completed' ? 'green' : 'red' }}-500 flex items-center justify-center ring-8 ring-white">
                                                    <i class="fas fa-{{ $trip->status === 'completed' ? 'check' : 'times' }} text-white text-sm"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        Trip #{{ $trip->id }} 
                                                        <span class="font-medium text-gray-900">{{ $trip->status === 'completed' ? 'completed' : 'cancelled' }}</span>
                                                        @if($trip->driver)
                                                            by {{ $trip->driver->user->first_name }} {{ $trip->driver->user->last_name }}
                                                        @endif
                                                    </p>
                                                    <p class="text-sm text-gray-500">Vehicle: {{ $trip->vehicle->license_plate }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ $trip->updated_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No recent activity</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Pending Assignments -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Pending Assignments</h3>
                </div>
                <div class="p-6">
                    @if($pendingAssignments->count() > 0)
                        <div class="space-y-4">
                            @foreach($pendingAssignments as $assignment)
                            <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">Trip #{{ $assignment->id }}</p>
                                        <p class="text-sm text-gray-600">{{ $assignment->vehicle->license_plate }}</p>
                                        @if($assignment->route)
                                            <p class="text-sm text-gray-500">{{ $assignment->route->start_location }} → {{ $assignment->route->end_location }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">{{ $assignment->scheduled_start_time->format('M d, g:i A') }}</p>
                                    </div>
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                                        Assign Driver
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No pending assignments</p>
                    @endif
                </div>
            </div>

            <!-- Available Drivers -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Available Drivers</h3>
                </div>
                <div class="p-6">
                    @if($availableDrivers->count() > 0)
                        <div class="space-y-3">
                            @foreach($availableDrivers as $driver)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <span class="text-xs font-medium text-green-600">{{ substr($driver->user->first_name, 0, 1) }}{{ substr($driver->user->last_name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $driver->user->first_name }} {{ $driver->user->last_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $driver->employee_id ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Available
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No available drivers</p>
                    @endif
                </div>
            </div>

            <!-- Fleet Status -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Fleet Status</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Total Vehicles</span>
                        <span class="text-sm font-medium text-gray-900">{{ $vehicleStats['total_vehicles'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">In Use</span>
                        <span class="text-sm font-medium text-blue-600">{{ $vehicleStats['vehicles_in_use'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Available</span>
                        <span class="text-sm font-medium text-green-600">{{ $vehicleStats['available_vehicles'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Maintenance Due</span>
                        <span class="text-sm font-medium text-red-600">{{ $vehicleStats['maintenance_due'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Route Performance -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Popular Routes</h3>
                </div>
                <div class="p-6">
                    @if($routeStats->count() > 0)
                        <div class="space-y-3">
                            @foreach($routeStats as $route)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $route->start_location }} → {{ $route->end_location }}</p>
                                    <p class="text-xs text-gray-500">{{ $route->completed_trips }}/{{ $route->total_trips }} completed</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">{{ $route->total_trips }}</p>
                                    <p class="text-xs text-gray-500">trips</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No route data</p>
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
                        <i class="fas fa-plus mr-2"></i>
                        Schedule Trip
                    </button>
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        Assign Driver
                    </button>
                    <button class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                        <i class="fas fa-route mr-2"></i>
                        Manage Routes
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

<!-- Trip Assignment Modal -->
<div id="assignmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900">Assign Driver to Trip</h3>
            <div class="mt-2 px-7 py-3">
                <form id="assignmentForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Select Driver</label>
                        <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Choose a driver...</option>
                            @foreach($availableDrivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->user->first_name }} {{ $driver->user->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-between">
                        <button type="button" onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Assign
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
    function openAssignmentModal(tripId) {
        document.getElementById('assignmentModal').classList.remove('hidden');
        document.getElementById('assignmentForm').setAttribute('data-trip-id', tripId);
    }

    function closeModal() {
        document.getElementById('assignmentModal').classList.add('hidden');
    }

    // Auto-refresh every 30 seconds for live updates
    setInterval(function() {
        location.reload();
    }, 30000);
</script>
@endpush

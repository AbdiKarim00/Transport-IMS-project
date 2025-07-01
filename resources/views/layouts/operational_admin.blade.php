<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Operational Admin Dashboard') - {{ config('app.name', 'Transport IMS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Navigation Links -->
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-cogs text-white"></i>
                                </div>
                                <div class="ml-3">
                                    <h2 class="text-xl font-semibold text-gray-900">{{ config('app.name', 'Transport IMS') }}</h2>
                                    <p class="text-xs text-gray-500">Operations Center</p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('operational_admin.dashboard') }}" 
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('operational_admin.dashboard') ? 'border-orange-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Dashboard
                            </a>
                            <a href="{{ route('operational_admin.fleet') }}" 
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('operational_admin.fleet*') ? 'border-orange-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-car mr-2"></i>
                                Fleet Management
                            </a>
                            <a href="{{ route('operational_admin.maintenance') }}" 
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('operational_admin.maintenance*') ? 'border-orange-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-wrench mr-2"></i>
                                Maintenance
                            </a>
                            <a href="{{ route('operational_admin.fuel') }}" 
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('operational_admin.fuel*') ? 'border-orange-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-gas-pump mr-2"></i>
                                Fuel Management
                            </a>
                            <a href="{{ route('operational_admin.costs') }}" 
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('operational_admin.costs*') ? 'border-orange-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-dollar-sign mr-2"></i>
                                Cost Analysis
                            </a>
                            <a href="{{ route('operational_admin.providers') }}" 
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('operational_admin.providers*') ? 'border-orange-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-handshake mr-2"></i>
                                Service Providers
                            </a>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Budget Status -->
                        <div class="mr-4">
                            <div class="flex items-center space-x-2">
                                <div class="h-2 w-2 bg-orange-400 rounded-full"></div>
                                <span class="text-sm text-gray-600">Budget: 75%</span>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="relative mr-4">
                            <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <span class="sr-only">View notifications</span>
                                <i class="fas fa-bell h-6 w-6"></i>
                            </button>
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative ml-3">
                            <div class="flex items-center">
                                <button type="button" class="bg-white flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" id="user-menu-button" onclick="toggleDropdown()">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-orange-700">{{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-3 text-left">
                                        <p class="text-sm font-medium text-gray-700">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                        <p class="text-xs text-gray-500">Operational Admin</p>
                                    </div>
                                    <i class="fas fa-chevron-down ml-2 text-gray-400"></i>
                                </button>
                            </div>

                            <!-- Dropdown menu -->
                            <div id="dropdown-menu" class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <a href="{{ route('operational_admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Your Profile
                                </a>
                                <a href="{{ route('operational_admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <a href="{{ route('operational_admin.budget') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chart-pie mr-2"></i>Budget Management
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-orange-500" onclick="toggleMobileMenu()">
                            <span class="sr-only">Open main menu</span>
                            <i class="fas fa-bars block h-6 w-6" id="menu-open"></i>
                            <i class="fas fa-times hidden h-6 w-6" id="menu-close"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="sm:hidden hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('operational_admin.dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('operational_admin.dashboard') ? 'bg-orange-50 border-orange-500 text-orange-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="{{ route('operational_admin.fleet') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('operational_admin.fleet*') ? 'bg-orange-50 border-orange-500 text-orange-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                        <i class="fas fa-car mr-2"></i>Fleet Management
                    </a>
                    <a href="{{ route('operational_admin.maintenance') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('operational_admin.maintenance*') ? 'bg-orange-50 border-orange-500 text-orange-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                        <i class="fas fa-wrench mr-2"></i>Maintenance
                    </a>
                    <a href="{{ route('operational_admin.fuel') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('operational_admin.fuel*') ? 'bg-orange-50 border-orange-500 text-orange-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                        <i class="fas fa-gas-pump mr-2"></i>Fuel Management
                    </a>
                    <a href="{{ route('operational_admin.costs') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('operational_admin.costs*') ? 'bg-orange-50 border-orange-500 text-orange-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                        <i class="fas fa-dollar-sign mr-2"></i>Cost Analysis
                    </a>
                    <a href="{{ route('operational_admin.providers') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('operational_admin.providers*') ? 'bg-orange-50 border-orange-500 text-orange-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                        <i class="fas fa-handshake mr-2"></i>Service Providers
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-orange-700">{{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('operational_admin.profile') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Your Profile</a>
                        <a href="{{ route('operational_admin.settings') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Settings</a>
                        <a href="{{ route('operational_admin.budget') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Budget Management</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown-menu');
            dropdown.classList.toggle('hidden');
        }

        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const openIcon = document.getElementById('menu-open');
            const closeIcon = document.getElementById('menu-close');
            
            menu.classList.toggle('hidden');
            openIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdown-menu');
            const button = document.getElementById('user-menu-button');
            
            if (!button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

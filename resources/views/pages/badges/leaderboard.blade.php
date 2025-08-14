<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - Classements</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-xl font-bold text-blue-600">Le Coursier</a>
                    </div>
                    <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/dashboard"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tableau de bord
                        </a>
                        @if (session('subscribed') == true)
                            <a href="/users"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Utilisateurs
                            </a>
                            <a href="/statistics"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Statistiques
                            </a>
                            <a href="/badges"
                                class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Badges
                            </a>
                            <a href="/tasks/history"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Historique des tâches
                            </a>
                        @endif
                        @if (auth()->user()->username == session('tenant_id'))
                            <a href="/billing"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Abonnement
                            </a>
                            @if (session('subscribed') == true)
                                <a href="/tenants/settings"
                                    class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Paramètres
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                            <form action="/reset-session">
                                @csrf
                                <button type="submit"
                                    class="text-sm text-red-600 hover:text-red-500">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Mobile menu button -->
                <div class="flex items-center sm:hidden">
                    <button type="button"
                        class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                        aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Ouvrir le menu</span>
                        <!-- Icon when menu is closed -->
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Icon when menu is open -->
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="hidden sm:hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="/dashboard"
                    class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Tableau de bord
                </a>
                @if (session('subscribed') == true)
                    <a href="/users"
                        class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Utilisateurs
                    </a>
                    <a href="/statistics"
                        class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Statistiques
                    </a>
                    <a href="/badges"
                        class="border-blue-500 bg-blue-50 text-blue-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Badges
                    </a>
                    <a href="/tasks/history"
                        class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Historique des tâches
                    </a>
                @endif
                @if (auth()->user()->username == session('tenant_id'))
                    <a href="/billing"
                        class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Abonnement
                    </a>
                    <a href="/tenants/settings"
                        class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Paramètres
                    </a>
                @endif
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-200">
                            <span
                                class="text-sm font-medium leading-none text-gray-500">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </span>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <form action="/reset-session">
                        @csrf
                        <button type="submit"
                            class="block px-4 py-2 text-base font-medium text-red-600 hover:text-red-800 hover:bg-gray-100 w-full text-left">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="py-10">
        <header>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h1 class="text-3xl font-bold leading-tight text-gray-900 mb-2">
                            <i class="fas fa-trophy text-yellow-500 mr-3"></i>
                            Leaderboard
                        </h1>
                        <p class="text-gray-600">Top performers and achievements</p>
                    </div>
                    <div class="flex flex-wrap gap-3 mt-4 sm:mt-0">
                        <a href="{{ route('badges.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-medal mr-2"></i>
                            Badges
                        </a>
                        <a href="{{ route('badges.user-progress') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-chart-line mr-2"></i>
                            Progress
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Points Leaderboard -->
                <div id="points-leaderboard" class="leaderboard-content">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                Top Users by Points
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Points</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Level</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Badges</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($leaderboards['points'] as $index => $user)
                                        <tr
                                            class="hover:bg-gray-50 {{ $index < 3 ? 'bg-gradient-to-r from-yellow-50 to-orange-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if ($index === 0)
                                                        <i class="fas fa-crown text-yellow-500 text-xl mr-2"></i>
                                                    @elseif($index === 1)
                                                        <i class="fas fa-medal text-gray-400 text-xl mr-2"></i>
                                                    @elseif($index === 2)
                                                        <i class="fas fa-medal text-orange-600 text-xl mr-2"></i>
                                                    @else
                                                        <span
                                                            class="text-lg font-semibold text-gray-600 w-8 text-center">{{ $index + 1 }}</span>
                                                    @endif
                                                    @if ($index < 3)
                                                        <span class="text-lg font-bold">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                            src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0ea5e9&color=fff' }}"
                                                            alt="{{ $user->name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                                                    <span
                                                        class="text-lg font-semibold text-gray-900">{{ number_format($user->stats->total_points ?? 0) }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                    Level {{ $user->stats->level ?? 1 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-sm font-medium text-gray-900">{{ $user->userBadges->count() ?? 0 }}
                                                    badges</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Badges Leaderboard -->
                <div id="badges-leaderboard" class="leaderboard-content hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-medal text-blue-500 mr-2"></i>
                                Top Users by Badges
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Badges</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Points</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Recent Badges</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($leaderboards['badges'] as $index => $user)
                                        <tr
                                            class="hover:bg-gray-50 {{ $index < 3 ? 'bg-gradient-to-r from-blue-50 to-indigo-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if ($index === 0)
                                                        <i class="fas fa-crown text-yellow-500 text-xl mr-2"></i>
                                                    @elseif($index === 1)
                                                        <i class="fas fa-medal text-gray-400 text-xl mr-2"></i>
                                                    @elseif($index === 2)
                                                        <i class="fas fa-medal text-orange-600 text-xl mr-2"></i>
                                                    @else
                                                        <span
                                                            class="text-lg font-semibold text-gray-600 w-8 text-center">{{ $index + 1 }}</span>
                                                    @endif
                                                    @if ($index < 3)
                                                        <span class="text-lg font-bold">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                            src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0ea5e9&color=fff' }}"
                                                            alt="{{ $user->name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-medal text-blue-500 mr-2"></i>
                                                    <span
                                                        class="text-lg font-semibold text-gray-900">{{ $user->badges_count ?? 0 }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-sm text-gray-600">{{ number_format($user->stats->total_points ?? 0) }}
                                                    pts</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($user->userBadges->take(3) as $userBadge)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $userBadge->badge->name }}
                                                        </span>
                                                    @endforeach
                                                    @if ($user->userBadges->count() > 3)
                                                        <span
                                                            class="text-xs text-gray-500">+{{ $user->userBadges->count() - 3 }}
                                                            more</span>
                                                    @endif
                                                    @if ($user->userBadges->count() == 0)
                                                        <span class="text-xs text-gray-400">No badges yet</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tasks Leaderboard -->
                <div id="tasks-leaderboard" class="leaderboard-content hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-tasks text-green-500 mr-2"></i>
                                Top Users by Completed Tasks
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tasks</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Completion Rate</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Points</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($leaderboards['tasks'] as $index => $user)
                                        <tr
                                            class="hover:bg-gray-50 {{ $index < 3 ? 'bg-gradient-to-r from-green-50 to-emerald-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if ($index === 0)
                                                        <i class="fas fa-crown text-yellow-500 text-xl mr-2"></i>
                                                    @elseif($index === 1)
                                                        <i class="fas fa-medal text-gray-400 text-xl mr-2"></i>
                                                    @elseif($index === 2)
                                                        <i class="fas fa-medal text-orange-600 text-xl mr-2"></i>
                                                    @else
                                                        <span
                                                            class="text-lg font-semibold text-gray-600 w-8 text-center">{{ $index + 1 }}</span>
                                                    @endif
                                                    @if ($index < 3)
                                                        <span class="text-lg font-bold">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                            src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0ea5e9&color=fff' }}"
                                                            alt="{{ $user->name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                    <span
                                                        class="text-lg font-semibold text-gray-900">{{ $user->stats->total_tasks_completed ?? 0 }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $total =
                                                        ($user->stats->total_tasks_completed ?? 0) +
                                                        ($user->stats->total_tasks_completed ?? 0);
                                                    $rate =
                                                        $total > 0
                                                            ? round(
                                                                (($user->stats->total_tasks_completed ?? 0) / $total) *
                                                                    100,
                                                            )
                                                            : 0;
                                                @endphp
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-green-500 h-2 rounded-full"
                                                            style="width: {{ $rate }}%"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-600">{{ $rate }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-sm text-gray-600">{{ number_format($user->stats->total_points ?? 0) }}
                                                    pts</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Level Leaderboard -->
                <div id="level-leaderboard" class="leaderboard-content hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-layer-group text-purple-500 mr-2"></i>
                                Top Users by Level
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Level</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Experience</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($leaderboards['level'] as $index => $user)
                                        <tr
                                            class="hover:bg-gray-50 {{ $index < 3 ? 'bg-gradient-to-r from-purple-50 to-pink-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if ($index === 0)
                                                        <i class="fas fa-crown text-yellow-500 text-xl mr-2"></i>
                                                    @elseif($index === 1)
                                                        <i class="fas fa-medal text-gray-400 text-xl mr-2"></i>
                                                    @elseif($index === 2)
                                                        <i class="fas fa-medal text-orange-600 text-xl mr-2"></i>
                                                    @else
                                                        <span
                                                            class="text-lg font-semibold text-gray-600 w-8 text-center">{{ $index + 1 }}</span>
                                                    @endif
                                                    @if ($index < 3)
                                                        <span class="text-lg font-bold">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                            src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0ea5e9&color=fff' }}"
                                                            alt="{{ $user->name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-lg font-bold bg-purple-100 text-purple-800">
                                                    {{ $user->stats->level ?? 1 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-sm font-medium text-gray-900">{{ number_format($user->stats->experience_points ?? 0) }}
                                                    XP</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $currentLevel = $user->stats->level ?? 1;
                                                    $experience = $user->stats->experience_points ?? 0;
                                                    $currentLevelXP = $currentLevel * 1000; // Assuming 1000 XP per level
                                                    $nextLevelXP = ($currentLevel + 1) * 1000;
                                                    $progress =
                                                        $currentLevelXP > 0 ? min(100, ($experience % 1000) / 10) : 0;
                                                @endphp
                                                <div class="flex items-center">
                                                    <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-purple-500 h-2 rounded-full"
                                                            style="width: {{ $progress }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-500">{{ round($progress) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
                    // Mobile menu toggle
                    document.querySelector('.mobile-menu-button').addEventListener('click', function() {
                        const menu = document.getElementById('mobile-menu');
                        menu.classList.toggle('hidden');
                    });

                    function showLeaderboard(type) {
                        // Hide all leaderboard contents
                        document.querySelectorAll('.leaderboard-content').forEach(content => {
                            content.classList.add('hidden');
                        });

                        // Remove active class from all tabs
                        document.querySelectorAll('.leaderboard-tab').forEach(tab => {
                            tab.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
                            tab.classList.add('border-transparent', 'text-gray-500');
                        });

                        // Show selected leaderboard
                        document.getElementById(type + '-leaderboard').classList.remove('hidden');

                        // Add active class to selected tab
                        const activeTab = document.querySelector(`[data-tab="${type}"]`);
                        activeTab.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
                        activeTab.classList.remove('border-transparent', 'text-gray-500'); < /div> < /
                        main >

                            <
                            script >
                            // Mobile menu toggle
                            document.addEventListener('DOMContentLoaded', function() {
                                const mobileMenuButton = document.querySelector('.mobile-menu-button');
                                const mobileMenu = document.getElementById('mobile-menu');

                                if (mobileMenuButton && mobileMenu) {
                                    mobileMenuButton.addEventListener('click', function() {
                                        mobileMenu.classList.toggle('hidden');
                                    });
                                }

                                // Leaderboard type switching
                                const typeButtons = document.querySelectorAll('[data-type]');
                                const periodButtons = document.querySelectorAll('[data-period]');

                                typeButtons.forEach(button => {
                                    button.addEventListener('click', function() {
                                        const type = this.dataset.type;
                                        updateLeaderboard(type, getCurrentPeriod());
                                    });
                                });

                                periodButtons.forEach(button => {
                                    button.addEventListener('click', function() {
                                        const period = this.dataset.period;
                                        updateLeaderboard(getCurrentType(), period);
                                    });
                                });

                                function getCurrentType() {
                                    const activeTypeButton = document.querySelector('[data-type].bg-blue-500');
                                    return activeTypeButton ? activeTypeButton.dataset.type : 'points';
                                }

                                function getCurrentPeriod() {
                                    const activePeriodButton = document.querySelector('[data-period].bg-blue-500');
                                    return activePeriodButton ? activePeriodButton.dataset.period : 'all';
                                }

                                function updateLeaderboard(type, period) {
                                    const url = new URL(window.location);
                                    url.searchParams.set('type', type);
                                    url.searchParams.set('period', period);
                                    window.location.href = url.toString();
                                }
                            });
    </script>
</body>

</html>

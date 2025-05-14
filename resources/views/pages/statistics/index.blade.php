<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - Statistiques</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                                class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Statistiques
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
                        <span class="sr-only">Open main menu</span>
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

        <!-- Mobile menu, show/hide based on menu state -->
        <div class="hidden sm:hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="/dashboard"
                    class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Tableau
                    de bord</a>
                @if (session('subscribed') == true)
                    <a href="/users"
                        class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Utilisateurs</a>
                    <a href="/statistics"
                        class="bg-blue-50 border-blue-500 text-blue-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Statistiques</a>
                    <a href="/tasks/history"
                        class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Historique
                        des tâches</a>
                @endif

                @if (auth()->user()->username == session('tenant_id'))
                    <a href="/billing"
                        class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Abonnement</a>
                    @if (session('subscribed') == true)
                        <a href="/tenants/settings"
                            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Paramètres</a>
                    @endif
                @endif
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                            <span class="text-white font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Page header -->
            <div class="px-4 sm:px-0 mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Statistiques</h1>
                <p class="mt-1 text-sm text-gray-600">Visualisez les performances de vos coursiers</p>
            </div>

            <!-- Date Filter Form -->
            <div class="bg-white shadow rounded-lg mb-8 p-4">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Filtrer par date</h2>
                <form action="{{ route('statistics.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label for="filter_type" class="block text-sm font-medium text-gray-700 mb-1">Type de
                                filtre</label>
                            <select id="filter_type" name="filter_type" onchange="toggleCustomDateInputs()"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="all" {{ $filterType == 'all' ? 'selected' : '' }}>Toutes les périodes
                                </option>
                                <option value="today" {{ $filterType == 'today' ? 'selected' : '' }}>Aujourd'hui
                                </option>
                                <option value="yesterday" {{ $filterType == 'yesterday' ? 'selected' : '' }}>Hier
                                </option>
                                <option value="this_week" {{ $filterType == 'this_week' ? 'selected' : '' }}>Cette
                                    semaine</option>
                                <option value="last_week" {{ $filterType == 'last_week' ? 'selected' : '' }}>Semaine
                                    dernière</option>
                                <option value="this_month" {{ $filterType == 'this_month' ? 'selected' : '' }}>Ce
                                    mois-ci</option>
                                <option value="last_month" {{ $filterType == 'last_month' ? 'selected' : '' }}>Mois
                                    dernier</option>
                                <option value="this_year" {{ $filterType == 'this_year' ? 'selected' : '' }}>Cette
                                    année</option>
                                <option value="last_year" {{ $filterType == 'last_year' ? 'selected' : '' }}>Année
                                    dernière</option>
                                <option value="custom" {{ $filterType == 'custom' ? 'selected' : '' }}>Personnalisé
                                </option>
                            </select>
                        </div>

                        <div id="start_date_container" class="col-span-2 md:col-span-1"
                            style="{{ $filterType !== 'custom' ? 'display: none;' : '' }}">
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Date de
                                début</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div id="end_date_container" class="col-span-2 md:col-span-1"
                            style="{{ $filterType !== 'custom' ? 'display: none;' : '' }}">
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Date de
                                fin</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="col-span-2 md:col-span-1 flex items-end">
                            <button type="submit"
                                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Filtrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if (isset($filterInfo) && $filterInfo['is_filtered'])
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8 rounded-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">Filtrage actif:</span> {{ $filterInfo['description'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Tâches totales</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ $taskStats['total'] }}</div>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Tâches complétées</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ $taskStats['completed'] }}
                                    </div>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Temps moyen de livraison</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        @php
                                            $seconds = $taskTimeStats['avg_completion_seconds'];
                                            $days = floor($seconds / 86400);
                                            $hours = floor(($seconds % 86400) / 3600);
                                            $minutes = floor(($seconds % 3600) / 60);
                                            $secs = $seconds % 60;
                                        @endphp
                                        @if ($days > 0)
                                            {{ $days }} j {{ $hours }} h {{ $minutes }} m
                                        @elseif($hours > 0)
                                            {{ $hours }} h {{ $minutes }} m {{ $secs }} s
                                        @else
                                            {{ $minutes }} m {{ $secs }} s
                                        @endif
                                    </div>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Taux de complétion</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {{ $taskStats['completion_rate'] }}%</div>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Tasks by Priority Chart -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition des tâches par priorité</h3>
                    <div class="h-64">
                        <canvas id="tasksByPriorityChart"></canvas>
                    </div>
                </div>

                <!-- Tasks by Day Chart -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tâches par jour de la semaine</h3>
                    <div class="h-64">
                        <canvas id="tasksByDayChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tasks by Month Chart -->
            <div class="bg-white p-6 rounded-lg shadow mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des tâches sur 12 mois</h3>
                <div class="h-96">
                    <canvas id="tasksByMonthChart"></canvas>
                </div>
            </div>

            <!-- User Performance Table -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg font-medium text-gray-900">Performance des coursiers (Top 5 des plus
                        performants)</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <a href="{{ route('statistics.couriers') }}" class="text-blue-600 hover:text-blue-800">
                            Voir tous les coursiers →
                        </a>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tâches totales
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Complétées
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    En attente
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    En cours
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Taux de complétion
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($usersStats as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span
                                                    class="text-blue-700 font-medium">{{ substr($user['name'], 0, 1) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user['name'] }}
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $user['username'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user['total_tasks'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user['completed_tasks'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user['pending_tasks'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user['in_progress_tasks'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="relative pt-1">
                                            <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                                <div style="width: {{ $user['completion_rate'] }}%"
                                                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                                                </div>
                                            </div>
                                            <div class="text-xs text-right mt-1">{{ $user['completion_rate'] }}%</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if (count($usersStats) === 0)
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucun utilisateur avec des tâches
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Milestone Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiques des milestones</h3>
                    <dl>
                        <div class="mb-4">
                            <dt class="text-sm font-medium text-gray-500">Total des points</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $milestoneStats['total'] }}</dd>
                        </div>
                        <div class="mb-4">
                            <dt class="text-sm font-medium text-gray-500">Points favoris</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $milestoneStats['favorites'] }}
                            </dd>
                        </div>
                        <div class="mb-4">
                            <dt class="text-sm font-medium text-gray-500">Tâches moyennes par point</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900">
                                {{ $milestoneStats['tasks_per_milestone'] }}</dd>
                        </div>
                        @if ($milestoneStats['most_used'])
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Point le plus utilisé</dt>
                                <dd class="mt-1 text-xl font-semibold text-gray-900">
                                    {{ $milestoneStats['most_used']['name'] }}
                                    ({{ $milestoneStats['most_used']['tasks_count'] }} tâches)</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiques de temps d'exécution</h3>
                    <dl>
                        <div class="mb-4">
                            <dt class="text-sm font-medium text-gray-500">Temps moyen de complétion</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900">
                                @php
                                    $seconds = $taskTimeStats['avg_completion_seconds'];
                                    $days = floor($seconds / 86400);
                                    $hours = floor(($seconds % 86400) / 3600);
                                    $minutes = floor(($seconds % 3600) / 60);
                                    $secs = $seconds % 60;
                                @endphp
                                @if ($days > 0)
                                    {{ $days }} j {{ $hours }} h {{ $minutes }} m
                                    {{ $secs }} s
                                @elseif($hours > 0)
                                    {{ $hours }} h {{ $minutes }} m {{ $secs }} s
                                @else
                                    {{ $minutes }} m {{ $secs }} s
                                @endif
                            </dd>
                        </div>
                        <div class="mb-4">
                            <dt class="text-sm font-medium text-gray-500">Temps maximum de complétion</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900">
                                @php
                                    $seconds = $taskTimeStats['max_completion_seconds'];
                                    $days = floor($seconds / 86400);
                                    $hours = floor(($seconds % 86400) / 3600);
                                    $minutes = floor(($seconds % 3600) / 60);
                                    $secs = $seconds % 60;
                                @endphp
                                @if ($days > 0)
                                    {{ $days }} j {{ $hours }} h {{ $minutes }} m
                                    {{ $secs }} s
                                @elseif($hours > 0)
                                    {{ $hours }} h {{ $minutes }} m {{ $secs }} s
                                @else
                                    {{ $minutes }} m {{ $secs }} s
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Temps minimum de complétion</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900">
                                @php
                                    $seconds = $taskTimeStats['min_completion_seconds'];
                                    $days = floor($seconds / 86400);
                                    $hours = floor(($seconds % 86400) / 3600);
                                    $minutes = floor(($seconds % 3600) / 60);
                                    $secs = $seconds % 60;
                                @endphp
                                @if ($days > 0)
                                    {{ $days }} j {{ $hours }} h {{ $minutes }} m
                                    {{ $secs }} s
                                @elseif($hours > 0)
                                    {{ $hours }} h {{ $minutes }} m {{ $secs }} s
                                @else
                                    {{ $minutes }} m {{ $secs }} s
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                    // Toggle icons
                    const icons = mobileMenuButton.querySelectorAll('svg');
                    icons.forEach(icon => icon.classList.toggle('hidden'));
                });
            }

            // Tasks by Priority Chart
            const priorityCtx = document.getElementById('tasksByPriorityChart').getContext('2d');
            const priorityData = @json($tasksByPriority);

            new Chart(priorityCtx, {
                type: 'pie',
                data: {
                    labels: ['Basse', 'Moyenne', 'Haute'],
                    datasets: [{
                        data: [
                            priorityData.low || 0,
                            priorityData.medium || 0,
                            priorityData.high || 0
                        ],
                        backgroundColor: [
                            'rgba(52, 211, 153, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: [
                            'rgb(52, 211, 153)',
                            'rgb(59, 130, 246)',
                            'rgb(239, 68, 68)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });

            // Tasks by Day of Week Chart
            const dayCtx = document.getElementById('tasksByDayChart').getContext('2d');
            const dayData = @json($tasksByDay);

            new Chart(dayCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(dayData),
                    datasets: [{
                        label: 'Nombre de tâches',
                        data: Object.values(dayData),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Tasks by Month Chart
            const monthCtx = document.getElementById('tasksByMonthChart').getContext('2d');
            const monthData = @json($tasksByMonth);

            new Chart(monthCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(monthData),
                    datasets: [{
                            label: 'Tâches créées',
                            data: Object.values(monthData).map(item => item.created),
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1
                        },
                        {
                            label: 'Tâches complétées',
                            data: Object.values(monthData).map(item => item.completed),
                            backgroundColor: 'rgba(52, 211, 153, 0.2)',
                            borderColor: 'rgb(52, 211, 153)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Function to toggle custom date inputs
            function toggleCustomDateInputs() {
                const filterType = document.getElementById('filter_type').value;
                const startDateContainer = document.getElementById('start_date_container');
                const endDateContainer = document.getElementById('end_date_container');

                if (filterType === 'custom') {
                    startDateContainer.style.display = 'block';
                    endDateContainer.style.display = 'block';
                } else {
                    startDateContainer.style.display = 'none';
                    endDateContainer.style.display = 'none';
                }
            }

            // Initialize custom date inputs visibility
            toggleCustomDateInputs();
        });
    </script>
</body>

</html>

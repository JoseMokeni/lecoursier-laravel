<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - Système de badges</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <a href="/tenants/settings"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Paramètres
                            </a>
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
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-3xl font-bold leading-tight text-gray-900">Gestion des badges</h1>
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <a href="{{ route('badges.user-progress') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-chart-line mr-2"></i>
                            Progression
                        </a>
                        <a href="{{ route('badges.leaderboard') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-trophy mr-2"></i>
                            Leaderboard
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-medal text-2xl text-yellow-500"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total des badges</dt>
                                        <dd class="text-2xl font-bold text-gray-900">{{ $totalBadges }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star text-2xl text-green-500"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Badges actifs</dt>
                                        <dd class="text-2xl font-bold text-gray-900">{{ $activeBadges }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-award text-2xl text-purple-500"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Badges obtenus</dt>
                                        <dd class="text-2xl font-bold text-gray-900">{{ $totalBadgesEarned }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users text-2xl text-blue-500"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Utilisateurs avec badges
                                        </dt>
                                        <dd class="text-2xl font-bold text-gray-900">{{ $uniqueUsersWithBadges }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Most Earned Badges Chart -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Badges les plus obtenus</h3>
                        <div class="h-64">
                            <canvas id="mostEarnedChart"></canvas>
                        </div>
                    </div>

                    <!-- Badge Distribution by Rarity -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par rareté</h3>
                        <div class="h-64">
                            <canvas id="rarityChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                            <input type="text" name="search" id="search" value="{{ $search }}"
                                placeholder="Nom du badge..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Catégorie</label>
                            <select name="category" id="category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Toutes les catégories</option>
                                @foreach ($categories as $cat => $count)
                                    <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $cat)) }} ({{ $count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="rarity" class="block text-sm font-medium text-gray-700">Rareté</label>
                            <select name="rarity" id="rarity"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Toutes les raretés</option>
                                <option value="bronze" {{ $rarity == 'bronze' ? 'selected' : '' }}>Bronze</option>
                                <option value="silver" {{ $rarity == 'silver' ? 'selected' : '' }}>Argent</option>
                                <option value="gold" {{ $rarity == 'gold' ? 'selected' : '' }}>Or</option>
                                <option value="platinum" {{ $rarity == 'platinum' ? 'selected' : '' }}>Platine
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Filtrer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Badges Grid -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Liste des badges</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Tous les badges disponibles dans le système avec les utilisateurs qui les ont obtenus.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        @foreach ($badges as $badge)
                            <div
                                class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                                <!-- Badge Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        @if ($badge->icon)
                                            <i
                                                class="{{ $badge->icon }} text-2xl mr-3
                                            @switch($badge->rarity)
                                                @case('bronze') text-orange-600 @break
                                                @case('silver') text-gray-500 @break
                                                @case('gold') text-yellow-500 @break
                                                @case('platinum') text-purple-500 @break
                                                @default text-gray-400
                                            @endswitch"></i>
                                        @else
                                            <div
                                                class="w-8 h-8 rounded-full mr-3 flex items-center justify-center
                                            @switch($badge->rarity)
                                                @case('bronze') bg-orange-100 text-orange-600 @break
                                                @case('silver') bg-gray-100 text-gray-500 @break
                                                @case('gold') bg-yellow-100 text-yellow-500 @break
                                                @case('platinum') bg-purple-100 text-purple-500 @break
                                                @default bg-gray-100 text-gray-400
                                            @endswitch">
                                                <i class="fas fa-medal"></i>
                                            </div>
                                        @endif
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($badge->rarity)
                                            @case('bronze') bg-orange-100 text-orange-800 @break
                                            @case('silver') bg-gray-100 text-gray-800 @break
                                            @case('gold') bg-yellow-100 text-yellow-800 @break
                                            @case('platinum') bg-purple-100 text-purple-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                            {{ ucfirst($badge->rarity) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $badge->points }} pts</span>
                                </div>

                                <!-- Badge Info -->
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $badge->name }}</h4>
                                <p class="text-sm text-gray-600 mb-4">{{ $badge->description }}</p>

                                <!-- Category -->
                                <div class="mb-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $badge->category)) }}
                                    </span>
                                </div>

                                <!-- Stats -->
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <span>{{ $badge->user_badges_count }} utilisateur(s)</span>
                                    <span>{{ $badge->is_active ? 'Actif' : 'Inactif' }}</span>
                                </div>

                                <!-- Recent earners -->
                                @if ($badge->userBadges->count() > 0)
                                    <div class="mb-4">
                                        <p class="text-xs font-medium text-gray-700 mb-2">Récemment obtenus par :</p>
                                        <div class="flex -space-x-2 overflow-hidden">
                                            @foreach ($badge->userBadges->take(3) as $userBadge)
                                                <div class="inline-block h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium"
                                                    title="{{ $userBadge->user->name }} - {{ $userBadge->earned_at->format('d/m/Y') }}">
                                                    {{ substr($userBadge->user->name, 0, 1) }}
                                                </div>
                                            @endforeach
                                            @if ($badge->user_badges_count > 3)
                                                <div
                                                    class="inline-block h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center text-white text-xs font-medium">
                                                    +{{ $badge->user_badges_count - 3 }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- View Details -->
                                <a href="{{ route('badges.show', $badge) }}"
                                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-500">
                                    Voir les détails
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $badges->appends(request()->query())->links() }}
                    </div>
                </div>

                <!-- Recent Achievements -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Achievements récents</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Les 10 derniers badges obtenus par les utilisateurs.
                        </p>
                    </div>

                    <ul class="divide-y divide-gray-200">
                        @foreach ($recentAchievements as $achievement)
                            <li class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span
                                                class="text-blue-700 font-medium">{{ substr($achievement->user->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $achievement->user->name }} a obtenu le badge
                                                <span
                                                    class="font-semibold text-blue-600">{{ $achievement->badge->name }}</span>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $achievement->earned_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($achievement->badge->rarity)
                                            @case('bronze') bg-orange-100 text-orange-800 @break
                                            @case('silver') bg-gray-100 text-gray-800 @break
                                            @case('gold') bg-yellow-100 text-yellow-800 @break
                                            @case('platinum') bg-purple-100 text-purple-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                            {{ $achievement->badge->points }} pts
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </main>

        <script>
            // Most earned badges chart
            const mostEarnedCtx = document.getElementById('mostEarnedChart').getContext('2d');
            const mostEarnedData = @json(
                $mostEarnedBadges->map(function ($badge) {
                    return ['name' => $badge->name, 'count' => $badge->user_badges_count];
                }));

            new Chart(mostEarnedCtx, {
                type: 'bar',
                data: {
                    labels: mostEarnedData.map(item => item.name),
                    datasets: [{
                        label: 'Nombre d\'utilisateurs',
                        data: mostEarnedData.map(item => item.count),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Rarity distribution chart
            const rarityCtx = document.getElementById('rarityChart').getContext('2d');
            const rarityData = @json($rarityDistribution);

            new Chart(rarityCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(rarityData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                    datasets: [{
                        data: Object.values(rarityData),
                        backgroundColor: [
                            'rgba(251, 146, 60, 0.8)', // Bronze
                            'rgba(156, 163, 175, 0.8)', // Silver
                            'rgba(251, 191, 36, 0.8)', // Gold
                            'rgba(147, 51, 234, 0.8)' // Platinum
                        ],
                        borderColor: [
                            'rgba(251, 146, 60, 1)',
                            'rgba(156, 163, 175, 1)',
                            'rgba(251, 191, 36, 1)',
                            'rgba(147, 51, 234, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Mobile menu toggle
            document.querySelector('.mobile-menu-button').addEventListener('click', function() {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            });
        </script>
</body>

</html>

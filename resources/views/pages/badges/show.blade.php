<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - {{ $badge->name }}</title>
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
                <div class="flex items-center">
                    <a href="{{ route('badges.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex items-center">
                        @if ($badge->icon)
                            <i
                                class="{{ $badge->icon }} text-4xl mr-4
                                @switch($badge->rarity)
                                    @case('bronze') text-orange-600 @break
                                    @case('silver') text-gray-500 @break
                                    @case('gold') text-yellow-500 @break
                                    @case('platinum') text-purple-500 @break
                                    @default text-gray-400
                                @endswitch"></i>
                        @else
                            <div
                                class="w-16 h-16 rounded-full mr-4 flex items-center justify-center
                                @switch($badge->rarity)
                                    @case('bronze') bg-orange-100 text-orange-600 @break
                                    @case('silver') bg-gray-100 text-gray-500 @break
                                    @case('gold') bg-yellow-100 text-yellow-500 @break
                                    @case('platinum') bg-purple-100 text-purple-500 @break
                                    @default bg-gray-100 text-gray-400
                                @endswitch">
                                <i class="fas fa-medal text-2xl"></i>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-3xl font-bold leading-tight text-gray-900">{{ $badge->name }}</h1>
                            <div class="flex items-center mt-2 space-x-4">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @switch($badge->rarity)
                                    @case('bronze') bg-orange-100 text-orange-800 @break
                                    @case('silver') bg-gray-100 text-gray-800 @break
                                    @case('gold') bg-yellow-100 text-yellow-800 @break
                                    @case('platinum') bg-purple-100 text-purple-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                    {{ ucfirst($badge->rarity) }}
                                </span>
                                <span class="text-sm text-gray-500">{{ $badge->points }} points</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucwords(str_replace('_', ' ', $badge->category)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Badge Overview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Badge Details -->
                    <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                        <p class="text-gray-600 mb-6">{{ $badge->description }}</p>

                        <h3 class="text-lg font-medium text-gray-900 mb-4">Critères d'obtention</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @if ($badge->criteria)
                                @foreach ($badge->criteria as $key => $value)
                                    <div class="mb-2">
                                        <span
                                            class="font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="text-gray-600">{{ $value }}</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500">Aucun critère spécifié</p>
                            @endif
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                Statut:
                                <span class="font-medium {{ $badge->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $badge->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </span>
                            <span class="text-sm text-gray-500">
                                Créé le {{ $badge->created_at->format('d/m/Y à H:i') }}
                            </span>
                        </div>
                    </div>

                    <!-- Badge Statistics -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Statistiques</h2>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Total obtenu</span>
                                <span class="text-lg font-bold text-gray-900">{{ $stats['total_earned'] }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Utilisateurs uniques</span>
                                <span class="text-lg font-bold text-gray-900">{{ $stats['unique_users'] }}</span>
                            </div>

                            @if ($stats['first_earned'])
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Premier obtenu</span>
                                    <span
                                        class="text-sm text-gray-600">{{ $stats['first_earned']->format('d/m/Y') }}</span>
                                </div>
                            @endif

                            @if ($stats['last_earned'])
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Dernier obtenu</span>
                                    <span
                                        class="text-sm text-gray-600">{{ $stats['last_earned']->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Users with Badge -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Utilisateurs ayant obtenu ce badge ({{ $usersWithBadge->total() }})
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Liste des utilisateurs qui ont obtenu ce badge, classés par date d'obtention.
                        </p>
                    </div>

                    @if ($usersWithBadge->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach ($usersWithBadge as $userBadge)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span
                                                    class="text-blue-700 font-medium">{{ substr($userBadge->user->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $userBadge->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $userBadge->user->email }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-900">
                                                {{ $userBadge->earned_at->format('d/m/Y') }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $userBadge->earned_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $usersWithBadge->links() }}
                        </div>
                    @else
                        <div class="px-6 py-4">
                            <p class="text-center text-gray-500">Aucun utilisateur n'a encore obtenu ce badge.</p>
                        </div>
                    @endif
                </div>

                <!-- Users Progress Towards Badge -->
                @if ($usersWithProgress->count() > 0)
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Progression des utilisateurs</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Utilisateurs les plus proches d'obtenir ce badge.
                            </p>
                        </div>

                        <ul class="divide-y divide-gray-200">
                            @foreach ($usersWithProgress->take(10) as $userProgress)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                <span
                                                    class="text-gray-700 font-medium">{{ substr($userProgress['user']->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $userProgress['user']->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $userProgress['user']->email }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div class="text-right">
                                                <div class="text-sm text-gray-900">
                                                    {{ $userProgress['progress']['current'] }} /
                                                    {{ $userProgress['progress']['required'] }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $userProgress['progress']['percentage'] }}%</div>
                                            </div>
                                            <div class="w-24">
                                                <div class="bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full"
                                                        style="width: {{ $userProgress['progress']['percentage'] }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </main>

        <script>
            // Mobile menu toggle
            document.querySelector('.mobile-menu-button').addEventListener('click', function() {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            });
        </script>
</body>

</html>

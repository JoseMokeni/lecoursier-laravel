<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - Historique des tâches</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                            <a href="/tasks/history"
                                class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
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
                    <a href="/tasks/history"
                        class="border-blue-500 bg-blue-50 text-blue-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
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

    <!-- Flash Messages -->
    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                <p class="font-bold">Erreur</p>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                <p class="font-bold">Succès</p>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="py-10">
        <header>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold leading-tight text-gray-900">Historique des tâches</h1>
            </div>
        </header>
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div
                        class="px-4 py-5 border-b border-gray-200 sm:px-6 flex flex-col sm:flex-row sm:justify-between sm:items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 sm:mb-0">Liste des tâches</h3>
                        <form action="{{ route('tasks.history') }}" method="GET"
                            class="w-full sm:w-64 mb-2 sm:mb-0 sm:order-2">
                            <div class="relative flex">
                                <input type="text" name="search" placeholder="Rechercher..."
                                    value="{{ request('search') }}"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-l-md px-4 py-2">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-2 border border-l-0 border-blue-600 text-sm font-medium rounded-r-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Rechercher
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Filter controls -->
                    <form action="{{ route('tasks.history') }}" method="GET" id="filterForm">
                        <input type="hidden" name="search" value="{{ request()->search }}">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 sm:px-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div
                                    class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                                    <div class="w-full sm:w-auto">
                                        <label for="statusFilter"
                                            class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                        <select id="statusFilter" name="status"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-auto sm:text-sm border-gray-300 rounded-md px-3 py-1.5"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="" {{ request()->status == '' ? 'selected' : '' }}>
                                                Tous</option>
                                            <option value="pending"
                                                {{ request()->status == 'pending' ? 'selected' : '' }}>En attente
                                            </option>
                                            <option value="in_progress"
                                                {{ request()->status == 'in_progress' ? 'selected' : '' }}>En cours
                                            </option>
                                            <option value="completed"
                                                {{ request()->status == 'completed' ? 'selected' : '' }}>Terminé
                                            </option>
                                        </select>
                                    </div>
                                    <div class="w-full sm:w-auto">
                                        <label for="priorityFilter"
                                            class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                                        <select id="priorityFilter" name="priority"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-auto sm:text-sm border-gray-300 rounded-md px-3 py-1.5"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="" {{ request()->priority == '' ? 'selected' : '' }}>
                                                Toutes</option>
                                            <option value="high"
                                                {{ request()->priority == 'high' ? 'selected' : '' }}>Haute</option>
                                            <option value="medium"
                                                {{ request()->priority == 'medium' ? 'selected' : '' }}>Moyenne
                                            </option>
                                            <option value="low"
                                                {{ request()->priority == 'low' ? 'selected' : '' }}>Basse</option>
                                        </select>
                                    </div>
                                    <div class="w-full sm:w-auto">
                                        <label for="dateFilter"
                                            class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                                        <select id="dateFilter" name="date_filter"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-auto sm:text-sm border-gray-300 rounded-md px-3 py-1.5"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value=""
                                                {{ request()->date_filter == '' ? 'selected' : '' }}>Toute période
                                            </option>
                                            <option value="today"
                                                {{ request()->date_filter == 'today' ? 'selected' : '' }}>Aujourd'hui
                                            </option>
                                            <option value="week"
                                                {{ request()->date_filter == 'week' ? 'selected' : '' }}>Cette semaine
                                            </option>
                                            <option value="month"
                                                {{ request()->date_filter == 'month' ? 'selected' : '' }}>Ce mois
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4 sm:mt-0">
                                    <a href="{{ route('tasks.history') }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Réinitialiser les filtres
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nom</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Statut</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Priorité</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Utilisateur</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        Date prévue</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Durée</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Créé le</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($tasks as $task)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $task->name }}</div>
                                            <!-- Mobile-only info -->
                                            <div class="text-xs text-gray-500 mt-1 md:hidden">
                                                Utilisateur: {{ $task->user ? $task->user->name : 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $task->status == 'completed'
                                                ? 'bg-green-100 text-green-800'
                                                : ($task->status == 'in_progress'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $task->status == 'completed' ? 'Terminé' : ($task->status == 'in_progress' ? 'En cours' : 'En attente') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $task->priority == 'high'
                                                ? 'bg-red-100 text-red-800'
                                                : ($task->priority == 'normal'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : 'bg-gray-100 text-gray-800') }}">
                                                {{ $task->priority == 'high' ? 'Haute' : ($task->priority == 'medium' ? 'Moyenne' : 'Basse') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                            <div class="text-sm text-gray-500">
                                                {{ $task->user ? $task->user->name : 'N/A' }}</div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                            {{ $task->due_date ? $task->due_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if ($task->status == 'completed' && $task->completed_at && $task->created_at)
                                                @php
                                                    $seconds = $task->created_at->diffInSeconds($task->completed_at);
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
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $task->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (count($tasks) == 0)
                            <div class="px-6 py-4 text-center text-sm text-gray-500">
                                Aucune tâche trouvée
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $tasks->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            // Toggle icons
            const icons = mobileMenuButton.querySelectorAll('svg');
            icons.forEach(icon => icon.classList.toggle('hidden'));
        });
    </script>
</body>

</html>

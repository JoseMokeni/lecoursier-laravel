<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - Gestion des utilisateurs</title>
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
                        <a href="/users"
                            class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Utilisateurs
                        </a>
                        <a href="/billing"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Abonnement
                        </a>
                        <a href="/tenants/settings"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Paramètres
                        </a>
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
                <a href="/users"
                    class="border-blue-500 bg-blue-50 text-blue-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Utilisateurs
                </a>
                <a href="/billing"
                    class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Abonnement
                </a>
                <a href="/tenants/settings"
                    class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Paramètres
                </a>
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
                <h1 class="text-3xl font-bold leading-tight text-gray-900">Gestion des utilisateurs</h1>
            </div>
        </header>
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                        role="alert">
                        <strong class="font-bold">Succès!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                        role="alert">
                        <strong class="font-bold">Erreur!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Trial Days Notification -->
                @if (session()->has('remaining_days'))
                    <div class="mb-6 bg-blue-100 border-l-4 border-blue-500 text-blue-700 px-4 py-3 rounded"
                        role="alert">
                        <p class="font-bold">Période d'essai</p>
                        <p>Il vous reste {{ session('remaining_days') }} jour(s) d'essai. <a
                                href="{{ route('billing.plans') }}" class="underline">Abonnez-vous maintenant</a> pour
                            continuer à utiliser ce service après la période d'essai.</p>
                    </div>
                @endif

                <!-- Users list -->
                <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div
                        class="px-4 py-5 border-b border-gray-200 sm:px-6 flex flex-col sm:flex-row sm:justify-between sm:items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 sm:mb-0">Utilisateurs</h3>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
                            <!-- Search input -->
                            <div class="relative w-full sm:w-auto">
                                <input type="text" id="userSearch" placeholder="Rechercher..."
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-2"
                                    onkeyup="filterUsers()">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>

                            <a href="/users/create"
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 w-full sm:w-auto">
                                Ajouter un utilisateur
                            </a>
                        </div>
                    </div>

                    <!-- Filter controls -->
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 sm:px-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div
                                class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                                <div class="w-full sm:w-auto">
                                    <label for="roleFilter"
                                        class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                                    <select id="roleFilter"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-auto sm:text-sm border-gray-300 rounded-md px-3 py-1.5"
                                        onchange="filterUsers()">
                                        <option value="all">Tous</option>
                                        <option value="admin">Administrateur</option>
                                        <option value="user">Utilisateur</option>
                                    </select>
                                </div>
                                <div class="w-full sm:w-auto">
                                    <label for="statusFilter"
                                        class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                    <select id="statusFilter"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-auto sm:text-sm border-gray-300 rounded-md px-3 py-1.5"
                                        onchange="filterUsers()">
                                        <option value="all">Tous</option>
                                        <option value="active">Actif</option>
                                        <option value="inactive">Inactif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <button type="button" onclick="resetFilters()"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Réinitialiser les filtres
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nom</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        Nom d'utilisateur</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Email</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rôle</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Statut</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        Créé le</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        Mis à jour le</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="userTableBody">
                                @foreach ($users as $user)
                                    <tr class="user-row">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <!-- Mobile-only info -->
                                            <div class="text-xs text-gray-500 mt-1 sm:hidden">
                                                {{ $user->username ?? 'N/A' }} <br>
                                                {{ $user->email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                            <div class="text-sm text-gray-500">{{ $user->username ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->status == 'active' ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                            {{ $user->created_at->format('d/m/Y') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                            {{ $user->updated_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                                                @if ($user->role == 'admin' && $user->id != auth()->id() && auth()->user()->username !== session('tenant_id'))
                                                    <!-- Other admins can't be edited by non-main admin -->
                                                    <span class="text-gray-400">Modifier</span>
                                                @else
                                                    <a href="/users/{{ $user->id }}/edit"
                                                        class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                                @endif

                                                @if (
                                                    $user->username !== session('tenant_id') &&
                                                        !($user->role == 'admin' && auth()->user()->username !== session('tenant_id')))
                                                    <form class="inline-block" method="POST"
                                                        action="/users/{{ $user->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div id="noResults" class="px-6 py-4 text-center text-sm text-gray-500 hidden">
                            Aucun utilisateur trouvé pour cette recherche
                        </div>
                        @if (count($users) == 0)
                            <div class="px-6 py-4 text-center text-sm text-gray-500">
                                Aucun utilisateur trouvé
                            </div>
                        @endif
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

        function filterUsers() {
            // Get filter values
            const query = document.getElementById('userSearch').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            // Get all user rows
            const rows = document.getElementsByClassName('user-row');
            let foundCount = 0;

            // Loop through all rows and hide/show based on filters
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const name = row.cells[0].textContent.toLowerCase();
                const username = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                const role = row.cells[3].textContent.trim().toLowerCase();
                const status = row.cells[4].textContent.trim().toLowerCase();

                // Check if row matches search query
                const matchesSearch = name.includes(query) ||
                    username.includes(query) ||
                    email.includes(query) ||
                    role.includes(query) ||
                    status.includes(query);

                // Check if row matches role filter
                const matchesRole = roleFilter === 'all' ||
                    (roleFilter === 'admin' && role.includes('admin')) ||
                    (roleFilter === 'user' && role.includes('utilisateur'));

                // Check if row matches status filter
                const matchesStatus = statusFilter === 'all' ||
                    (statusFilter === 'active' && status.includes('actif')) ||
                    (statusFilter === 'inactive' && status.includes('inactif'));

                // Show row only if it matches all filters
                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                    foundCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            // Show or hide the "No results" message
            const noResults = document.getElementById('noResults');
            if (foundCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        function resetFilters() {
            // Reset all filter controls
            document.getElementById('userSearch').value = '';
            document.getElementById('roleFilter').value = 'all';
            document.getElementById('statusFilter').value = 'all';

            // Re-filter the table (which will show all rows)
            filterUsers();
        }
    </script>
</body>

</html>

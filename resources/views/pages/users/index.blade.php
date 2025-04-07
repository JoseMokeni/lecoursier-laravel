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
                        <h1 class="text-xl font-bold text-blue-600">Le Coursier</h1>
                    </div>
                    <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/dashboard"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tableau de bord
                        </a>
                        <a href="#"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Livraisons
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

                <!-- Users list -->
                <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Utilisateurs</h3>
                        <div class="flex space-x-3">
                            <!-- Search input -->
                            <div class="relative">
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
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                Ajouter un utilisateur
                            </a>
                        </div>
                    </div>

                    <!-- Filter controls -->
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 sm:px-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                                <div>
                                    <label for="roleFilter"
                                        class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                                    <select id="roleFilter"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block sm:text-sm border-gray-300 rounded-md px-3 py-1.5"
                                        onchange="filterUsers()">
                                        <option value="all">Tous</option>
                                        <option value="admin">Administrateur</option>
                                        <option value="user">Utilisateur</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="statusFilter"
                                        class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                    <select id="statusFilter"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block sm:text-sm border-gray-300 rounded-md px-3 py-1.5"
                                        onchange="filterUsers()">
                                        <option value="all">Tous</option>
                                        <option value="active">Actif</option>
                                        <option value="inactive">Inactif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2 sm:mt-0">
                                <button type="button" onclick="resetFilters()"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nom d'utilisateur</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rôle</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Statut</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Créé le</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $user->username ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->updated_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if ($user->role == 'admin' && $user->id != auth()->id() && auth()->user()->username !== session('tenant_id'))
                                                <!-- Other admins can't be edited by non-main admin -->
                                                <span class="text-gray-400">Modifier</span>
                                            @else
                                                <a href="/users/{{ $user->id }}/edit"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</a>
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

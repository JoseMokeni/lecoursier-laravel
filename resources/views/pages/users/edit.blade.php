<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - Modifier un utilisateur</title>
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
                <h1 class="text-3xl font-bold leading-tight text-gray-900">Modifier un utilisateur</h1>
            </div>
        </header>
        <main>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Informations utilisateur</h3>
                        <a href="/users"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                            Retour
                        </a>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <!-- Display validation errors -->
                        @if ($errors->any())
                            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                                role="alert">
                                <strong class="font-bold">Erreur!</strong>
                                <ul class="mt-1 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="/users/{{ $user->id }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" name="name" id="name"
                                        value="{{ old('name', $user->name) }}" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email"
                                        value="{{ old('email', $user->email) }}" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe
                                        (laisser vide pour ne pas modifier)</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <input type="password" name="password" id="password"
                                            class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <button type="button" id="generate-password"
                                            class="ml-3 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Générer
                                        </button>
                                    </div>
                                    <div id="password-strength" class="mt-1 text-xs hidden">
                                        <span id="password-feedback"></span>
                                    </div>
                                </div>

                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700">Confirmation du mot de
                                        passe</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
                                    <select name="role" id="role" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        {{ $user->username === session('tenant_id') || auth()->user()->username != session('tenant_id') ? 'disabled' : '' }}>
                                        <option value="admin"
                                            {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                            Administrateur</option>
                                        <option value="user"
                                            {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>
                                            Utilisateur
                                        </option>
                                    </select>
                                    @if ($user->username === session('tenant_id'))
                                        <p class="mt-1 text-sm text-gray-500">Le rôle de l'administrateur principal ne
                                            peut pas être modifié.</p>
                                        <input type="hidden" name="role" value="{{ $user->role }}">
                                    @elseif (auth()->user()->username != session('tenant_id'))
                                        <p class="mt-1 text-sm text-gray-500">Seul l'administrateur principal peut
                                            modifier le rôle d'un utilisateur.</p>
                                        <input type="hidden" name="role" value="{{ $user->role }}">
                                    @endif
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select name="status" id="status" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        {{ ($user->role == 'admin' && auth()->user()->username != session('tenant_id')) || $user->username === session('tenant_id') ? 'disabled' : '' }}>
                                        <option value="active"
                                            {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>
                                            Actif</option>
                                        <option value="inactive"
                                            {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>
                                            Inactif
                                        </option>
                                    </select>
                                    @if ($user->username === session('tenant_id'))
                                        <p class="mt-1 text-sm text-gray-500">Le statut de l'administrateur principal
                                            ne
                                            peut pas être modifié.</p>
                                        <input type="hidden" name="status" value="{{ $user->status }}">
                                    @elseif ($user->role == 'admin' && auth()->user()->username != session('tenant_id'))
                                        <p class="mt-1 text-sm text-gray-500">Seul l'administrateur principal peut
                                            modifier le statut d'un administrateur.</p>
                                        <input type="hidden" name="status" value="{{ $user->status }}">
                                    @endif
                                </div>
                            </div>

                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="submit"
                                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Mettre à jour l'utilisateur
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        input:checked~.dot {
            transform: translateX(100%);
        }

        input:checked~.block {
            background-color: #3b82f6;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateBtn = document.getElementById('generate-password');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('password_confirmation');
            const passwordStrength = document.getElementById('password-strength');
            const passwordFeedback = document.getElementById('password-feedback');

            generateBtn.addEventListener('click', function() {
                // Generate a random password with 12 characters including special chars, numbers, uppercase and lowercase
                const length = 12;
                const charset =
                    "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+=";
                let password = "";

                // Make sure password has at least one char from each character set
                password += getRandomChar("ABCDEFGHIJKLMNOPQRSTUVWXYZ"); // uppercase
                password += getRandomChar("abcdefghijklmnopqrstuvwxyz"); // lowercase
                password += getRandomChar("0123456789"); // number
                password += getRandomChar("!@#$%^&*()_-+="); // special char

                // Fill the rest randomly
                for (let i = 4; i < length; i++) {
                    password += charset.charAt(Math.floor(Math.random() * charset.length));
                }

                // Shuffle the password
                password = shuffleString(password);

                // Set the generated password to both fields
                passwordField.type = "text"; // Show the password temporarily
                passwordField.value = password;
                confirmPasswordField.value = password;

                // Show feedback
                passwordStrength.classList.remove('hidden');
                passwordFeedback.textContent = "Mot de passe fort généré";
                passwordFeedback.className = "text-green-600";

                // Hide the password after 3 seconds
                setTimeout(() => {
                    passwordField.type = "password";
                }, 3000);
            });

            function getRandomChar(charset) {
                return charset.charAt(Math.floor(Math.random() * charset.length));
            }

            function shuffleString(string) {
                const array = string.split('');
                for (let i = array.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [array[i], array[j]] = [array[j], array[i]];
                }
                return array.join('');
            }
        });
    </script>
</body>

</html>

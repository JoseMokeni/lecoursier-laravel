<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Coursier - Plans d'abonnement</title>
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
                        @endif
                        <a href="/billing"
                            class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Abonnement
                        </a>
                        @if (session('subscribed') == true)
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
                @endif
                <a href="/billing"
                    class="border-blue-500 bg-blue-50 text-blue-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Abonnement
                </a>
                @if (session('subscribed') == true)
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
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                <p class="font-bold">Succès</p>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                <p class="font-bold">Erreur</p>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="py-10">
        <header>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold leading-tight text-gray-900">Plans d'abonnement</h1>
                <p class="mt-2 text-sm text-gray-600">Choisissez le plan qui convient le mieux à votre entreprise</p>
            </div>
        </header>
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Monthly Plan -->
                    <div class="bg-white overflow-hidden shadow rounded-lg relative h-full">
                        <div class="px-4 py-5 sm:p-6 h-full flex flex-col">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Plan Mensuel</h3>
                            <div class="mt-4 flex items-baseline">
                                <span class="text-5xl font-extrabold text-gray-900">$20</span>
                                <span class="ml-1 text-xl font-semibold text-gray-500">/mois</span>
                            </div>
                            <p class="mt-5 text-gray-500">Parfait pour tester notre service</p>

                            <ul class="mt-6 space-y-4 flex-grow">
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-700">Accès à toutes les fonctionnalités</p>
                                </li>
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-700">Support par email</p>
                                </li>
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-700">Paiement mensuel flexible</p>
                                </li>
                            </ul>

                            <div class="mt-8">
                                <a href="/billing/checkout"
                                    class="block text-center w-full bg-blue-600 border border-transparent rounded-md py-3 px-4 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    S'abonner maintenant
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Yearly Plan -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border-2 border-blue-500 relative h-full">
                        <div class="px-4 py-5 sm:p-6 h-full flex flex-col">
                            <!-- Recommended badge properly positioned -->
                            <div class="absolute top-5 inset-x-0 flex justify-center">
                                <span
                                    class="inline-flex px-4 py-1 rounded-full text-sm font-semibold tracking-wider uppercase bg-blue-100 text-blue-600 transform -translate-y-1/2">
                                    Recommandé
                                </span>
                            </div>

                            <h3 class="text-lg font-medium leading-6 text-gray-900">Plan Annuel</h3>
                            <div class="mt-4 flex items-baseline">
                                <span class="text-5xl font-extrabold text-gray-900">$180</span>
                                <span class="ml-1 text-xl font-semibold text-gray-500">/an</span>
                            </div>
                            <p class="mt-5 text-gray-500">
                                <span class="text-green-600 font-medium">Économisez $60 par an</span>
                                <br>Le meilleur rapport qualité-prix
                            </p>

                            <ul class="mt-6 space-y-4 flex-grow">
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-700">Accès à toutes les fonctionnalités</p>
                                </li>
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-700">Support prioritaire</p>
                                </li>
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-700">Économisez 25% par rapport au plan mensuel
                                    </p>
                                </li>
                            </ul>

                            <div class="mt-8">
                                <a href="/billing/checkout/yearly"
                                    class="block text-center w-full bg-blue-600 border border-transparent rounded-md py-3 px-4 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    S'abonner maintenant
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('tenant.settings') }}"
                        class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        &larr; Retour aux paramètres
                    </a>
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

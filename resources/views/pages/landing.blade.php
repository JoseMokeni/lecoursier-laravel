<x-layout>
    <nav class="bg-white shadow-md fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="#"
                            class="text-xl sm:text-2xl font-bold text-blue-600 hover:text-blue-800 truncate max-w-[150px] sm:max-w-none">Le
                            Coursier</a>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-4 lg:space-x-8">
                        <a href="#features"
                            class="nav-link border-transparent text-gray-500 hover:border-blue-500 hover:text-blue-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            data-section="features">
                            Fonctionnalités
                        </a>
                        <a href="#for-who"
                            class="nav-link border-transparent text-gray-500 hover:border-blue-500 hover:text-blue-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            data-section="for-who">
                            Pour qui
                        </a>
                        <a href="#offers"
                            class="nav-link border-transparent text-gray-500 hover:border-blue-500 hover:text-blue-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            data-section="offers">
                            Nos offres
                        </a>
                        <a href="#demo"
                            class="nav-link border-transparent text-gray-500 hover:border-blue-500 hover:text-blue-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            data-section="demo">
                            Démonstration
                        </a>
                        <a href="#contact"
                            class="nav-link border-transparent text-gray-500 hover:border-blue-500 hover:text-blue-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            data-section="contact">
                            Contact
                        </a>
                    </div>
                </div>
                <div class="hidden md:ml-6 md:flex md:items-center space-x-3">
                    @if (session('tenant_id') && \App\Models\Tenant::find(session('tenant_id')))
                        <form action="{{ route('reset.session') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-gray-600 hover:text-red-600 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-sign-out-alt mr-1"></i> Déconnexion
                            </button>
                        </form>
                    @endif
                    <a href="/dashboard"
                        class="border border-blue-600 hover:bg-blue-50 text-blue-600 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Espace Admin
                    </a>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Essai gratuit
                    </button>
                </div>
                <div class="-mr-2 flex items-center md:hidden">
                    <button type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                        aria-controls="mobile-menu" aria-expanded="false" id="mobile-menu-button">
                        <span class="sr-only">Ouvrir le menu</span>
                        <i class="fas fa-bars hamburger-icon"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="#features"
                    class="mobile-nav-link text-gray-600 hover:bg-blue-50 hover:text-blue-700 block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium"
                    data-section="features">
                    Fonctionnalités
                </a>
                <a href="#for-who"
                    class="mobile-nav-link text-gray-600 hover:bg-blue-50 hover:text-blue-700 block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium"
                    data-section="for-who">
                    Pour qui
                </a>
                <a href="#offers"
                    class="mobile-nav-link text-gray-600 hover:bg-blue-50 hover:text-blue-700 block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium"
                    data-section="offers">
                    Nos offres
                </a>
                <a href="#demo"
                    class="mobile-nav-link text-gray-600 hover:bg-blue-50 hover:text-blue-700 block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium"
                    data-section="demo">
                    Démonstration
                </a>
                <a href="#contact"
                    class="mobile-nav-link text-gray-600 hover:bg-blue-50 hover:text-blue-700 block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium"
                    data-section="contact">
                    Contact
                </a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex flex-col px-4 space-y-2">
                    @if (session('tenant_id') && \App\Models\Tenant::find(session('tenant_id')))
                        <form action="{{ route('reset.session') }}" class="w-full">
                            @csrf
                            <button type="submit"
                                class="w-full border border-red-500 hover:bg-red-50 text-red-600 px-4 py-2 rounded-md text-sm font-medium text-center">
                                <i class="fas fa-sign-out-alt mr-1"></i> Déconnexion
                            </button>
                        </form>
                    @endif
                    <a href="/dashboard"
                        class="block border border-blue-600 hover:bg-blue-50 text-blue-600 px-4 py-2 rounded-md text-sm font-medium text-center">
                        Espace Admin
                    </a>
                    <button
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center">
                        Essai gratuit
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-blue-600 pt-24 pb-20 overflow-hidden">
        <div class="absolute right-0 top-0 w-1/2 h-full opacity-10">
            <img src="/api/placeholder/800/600" alt="Background pattern" class="w-full h-full object-cover">
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-11">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                        <span class="block">Optimisez vos</span>
                        <span class="block text-yellow-300">livraisons</span>
                    </h1>
                    <p class="mt-3 text-base text-white sm:mt-5 sm:text-xl lg:text-lg xl:text-xl">
                        Le Coursier est une solution SaaS complète qui facilite la gestion de vos coursiers et améliore
                        leur productivité avec des outils numériques performants.
                    </p>
                    <div class="mt-8 sm:max-w-lg sm:mx-auto sm:text-center lg:text-left">
                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                            <a href="#demo"
                                class="block w-full sm:w-auto rounded-md shadow bg-yellow-300 text-blue-700 font-bold px-8 py-3 text-center">
                                Démonstration
                            </a>
                            <a href="#contact"
                                class="block w-full sm:w-auto rounded-md border border-white text-white font-bold px-8 py-3 text-center">
                                Nous contacter
                            </a>
                        </div>
                    </div>
                </div>
                <div
                    class="mt-12 relative sm:max-w-lg sm:mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-span-6 lg:flex lg:items-center">
                    <div class="relative mx-auto w-full rounded-lg shadow-lg lg:max-w-md">
                        <img class="w-full rounded-lg"
                            src="https://static.vecteezy.com/system/resources/previews/007/784/631/non_2x/parcel-delivery-service-illustration-concept-flat-illustration-isolated-on-white-background-vector.jpg"
                            alt="Application mobile Le Coursier">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Fonctionnalités Principales
                </h2>
                <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                    Notre plateforme offre tout ce dont vous avez besoin pour optimiser vos opérations de livraison
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1: Route Optimization -->
                <div
                    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Optimisation des itinéraires</h3>
                    <p class="text-gray-600">Calculez les itinéraires les plus efficaces pour vos coursiers, réduisant
                        ainsi le temps de livraison et la consommation de carburant.</p>
                </div>

                <!-- Feature 2: Real-time Tracking -->
                <div
                    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Suivi en temps réel</h3>
                    <p class="text-gray-600">Suivez vos coursiers et vos courses en temps réel sur une carte
                        interactive,
                        offrant une visibilité complète sur vos opérations.</p>
                </div>

                <!-- Feature 3: Delivery Management -->
                <div
                    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Gestion des tâches</h3>
                    <p class="text-gray-600">Planifiez, attribuez et suivez les tâches facilement avec notre système
                        intuitif de gestion des tâches.</p>
                </div>

                <!-- Feature 4: Analytics -->
                <div
                    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Analytique et rapports</h3>
                    <p class="text-gray-600">Accédez à des tableaux de bord détaillés et des rapports de performance
                        pour identifier les tendances et améliorer l'efficacité.</p>
                </div>

                <!-- Feature 5: Mobile App -->
                <div
                    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Application mobile</h3>
                    <p class="text-gray-600">Équipez vos coursiers d'une application mobile puissante pour traquer les
                        tâches
                        qui leur sont attribuées et mettre à jour l'état des livraisons.</p>
                </div>

                <!-- Feature 6: Customer Notifications -->
                <div
                    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Notifications Push</h3>
                    <p class="text-gray-600">Restez informés avec des mises à jour automatiques par e-mail
                        ou SMS sur l'état des courses.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- For Who Section -->
    <section id="for-who" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Pour qui est Le Coursier?
                </h2>
                <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                    Notre solution s'adapte aux besoins de différents secteurs et types de services de course
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- E-commerce -->
                <div class="bg-blue-50 rounded-lg p-8 transition-all hover:shadow-lg">
                    <div
                        class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mb-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">E-commerce</h3>
                    <p class="text-gray-600 text-center">
                        Pour les entreprises de commerce électronique cherchant à optimiser leurs services de livraison
                        et offrir une expérience client exceptionnelle.
                    </p>
                </div>

                <!-- Food Services -->
                <div class="bg-blue-50 rounded-lg p-8 transition-all hover:shadow-lg">
                    <div
                        class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mb-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Services alimentaires</h3>
                    <p class="text-gray-600 text-center">
                        Pour les restaurants et traiteurs qui ont besoin d'un service de course efficace et ponctuel
                        pour la livraison de repas et fournitures.
                    </p>
                </div>

                <!-- Logistics Companies -->
                <div class="bg-blue-50 rounded-lg p-8 transition-all hover:shadow-lg">
                    <div
                        class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mb-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Entreprises logistiques</h3>
                    <p class="text-gray-600 text-center">
                        Pour les sociétés de logistique qui veulent gérer efficacement leur flotte de véhicules et
                        optimiser tous types de missions de course.
                    </p>
                </div>

                <!-- Business Services -->
                <div class="bg-blue-50 rounded-lg p-8 transition-all hover:shadow-lg">
                    <div
                        class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mb-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Services aux entreprises</h3>
                    <p class="text-gray-600 text-center">
                        Pour les entreprises qui ont besoin de transporter des documents, échantillons ou matériel entre
                        différents sites ou vers des clients.
                    </p>
                </div>

                <!-- Healthcare -->
                <div class="bg-blue-50 rounded-lg p-8 transition-all hover:shadow-lg">
                    <div
                        class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mb-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Secteur médical</h3>
                    <p class="text-gray-600 text-center">
                        Pour les établissements médicaux et pharmacies qui ont besoin d'un transport rapide et sécurisé
                        d'échantillons, médicaments ou équipements.
                    </p>
                </div>

                <!-- On-demand Services -->
                <div class="bg-blue-50 rounded-lg p-8 transition-all hover:shadow-lg">
                    <div
                        class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mb-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Services à la demande</h3>
                    <p class="text-gray-600 text-center">
                        Pour les entreprises offrant des services de coursier à la demande qui nécessitent une
                        plateforme technologique fiable et flexible.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Offers Section -->
    <section id="offers" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Nos Offres
                </h2>
                <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                    Choisissez la formule qui correspond le mieux à vos besoins et commencez à optimiser vos livraisons
                    dès aujourd'hui
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-12">
                <!-- Free Trial Plan -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-all hover:shadow-xl">
                    <div class="bg-blue-600 p-6 text-center">
                        <h3 class="text-2xl font-bold text-white">Essai Gratuit</h3>
                        <div class="mt-4">
                            <span class="text-4xl font-extrabold text-white">0€</span>
                            <span class="text-xl text-blue-100">/mois</span>
                        </div>
                        <p class="mt-2 text-blue-100">Pour 14 jours</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Accès au tableau de bord d'administration</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Jusqu'à 5 coursiers</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Suivi des livraisons en temps réel</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Application mobile pour coursiers</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Rapports de base</span>
                            </li>
                            <li class="flex items-start opacity-50">
                                <svg class="h-6 w-6 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="ml-3 text-gray-500">Espace dédié (tenant)</span>
                            </li>
                        </ul>

                        <div class="pt-4">
                            <a href="#"
                                class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md text-center transition-colors">
                                Commencer l'essai gratuit
                            </a>
                        </div>
                        <p class="text-sm text-gray-500 text-center">Aucune carte de crédit requise</p>
                    </div>
                </div>

                <!-- Pro Subscription Plan -->
                <div
                    class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-yellow-400 relative transition-all hover:shadow-xl">
                    <div class="absolute top-0 right-0 mt-4 mr-4">
                        <span
                            class="bg-yellow-400 text-blue-800 text-xs font-bold px-3 py-1 rounded-full uppercase">Recommandé</span>
                    </div>
                    <div class="bg-blue-800 p-6 text-center">
                        <h3 class="text-2xl font-bold text-white">Abonnement Pro</h3>
                        <div class="mt-4">
                            <span class="text-4xl font-extrabold text-white">199€</span>
                            <span class="text-xl text-blue-200">/mois</span>
                        </div>
                        <p class="mt-2 text-blue-200">Facturation mensuelle</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700"><strong>Votre propre espace dédié</strong>
                                    (tenant)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700"><strong>Coursiers illimités</strong></span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Personnalisation avancée</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Rapports avancés et analytiques</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">API pour intégration avec vos systèmes</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-700">Support prioritaire</span>
                            </li>
                        </ul>

                        <div class="pt-4">
                            <a href="#"
                                class="block w-full text-white font-bold py-3 px-4 rounded-md text-center transition-colors bg-yellow-400 hover:bg-yellow-500">
                                S'abonner
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div class="mb-10 lg:mb-0">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl mb-6">
                        Voyez notre application en action
                    </h2>
                    <p class="text-lg text-gray-600 mb-8">
                        Découvrez comment Le Coursier peut transformer votre gestion de livraisons au quotidien.
                        Notre vidéo de démonstration vous présente les fonctionnalités clés de notre plateforme
                        et comment elles peuvent optimiser vos opérations.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="ml-3 text-gray-700">Interface intuitive pour la création et l'attribution des
                                courses</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="ml-3 text-gray-700">Suivi en temps réel des coursiers sur la carte interactive
                            </p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="ml-3 text-gray-700">Application mobile pour vos coursiers sur le terrain</p>
                        </div>
                    </div>
                    <div class="mt-10">
                        <a href="#contact"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            Demander une démo personnalisée
                        </a>
                    </div>
                </div>
                <div class="rounded-xl overflow-hidden shadow-xl">
                    <div class="relative">
                        <!-- Fixed video height with absolute sizing -->
                        <div class="w-full" style="height: 300px;">
                            <iframe class="w-full h-full" src="https://www.youtube.com/embed/Tz7Gq9sS5ts"
                                title="Le Coursier - Démonstration" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 text-center text-sm text-gray-500">
                        Cette vidéo vous montre les principales fonctionnalités de notre plateforme
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Contactez-nous
                </h2>
                <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                    Vous avez des questions ou besoin d'informations supplémentaires ? N'hésitez pas à nous contacter.
                </p>
            </div>

            <div class="lg:grid lg:grid-cols-2 lg:gap-8">
                <div class="mb-12 lg:mb-0">
                    <div class="prose prose-lg max-w-none">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Nous sommes à votre écoute</h3>
                        <p class="text-gray-600 mb-8">
                            Que vous soyez une petite entreprise ou une grande organisation, nous sommes là pour vous
                            aider à optimiser vos services de livraison avec notre solution Le Coursier.
                        </p>

                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-base text-gray-700">
                                    <p>+216 00 000 000</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-base text-gray-700">
                                    <p>contact@lecoursier.app</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-base text-gray-700">
                                    <p>123 Avenue Habib Bourguiba<br>8000 Nabeul, Tunisie</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                            role="alert">
                            <strong class="font-bold">Merci !</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                            role="alert">
                            <strong class="font-bold">Erreur !</strong>
                            <span class="block sm:inline">Veuillez corriger les erreurs ci-dessous.</span>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                            <div class="mt-1">
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    required>
                            </div>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="mt-1">
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    required>
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <div class="mt-1">
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Sujet</label>
                            <div class="mt-1">
                                <select id="subject" name="subject"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="demo">Demande de démonstration</option>
                                    <option value="pricing">Informations sur les tarifs</option>
                                    <option value="support">Support technique</option>
                                    <option value="other">Autre demande</option>
                                </select>
                            </div>
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <div class="mt-1">
                                <textarea id="message" name="message" rows="4"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    required>{{ old('message') }}</textarea>
                            </div>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <input id="privacy" name="privacy" type="checkbox"
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" required>
                            </div>
                            <div class="ml-3">
                                <label for="privacy" class="text-sm text-gray-500">
                                    J'accepte la <a href="{{ route('privacy.policy') }}"
                                        class="text-blue-600 hover:underline">politique de
                                        confidentialité</a>
                                </label>
                            </div>
                        </div>
                        @error('privacy')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div>
                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

</x-layout>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Le Coursier</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- Tailwind cdn --}}
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@2"></script>
    @endif

    <style>
        /* Fallback icon for mobile menu if Font Awesome fails to load */
        .mobile-menu-icon {
            display: block;
            width: 24px;
            height: 18px;
            position: relative;
        }

        .mobile-menu-icon span {
            display: block;
            position: absolute;
            height: 2px;
            width: 100%;
            background: #4b5563;
            border-radius: 2px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
        }

        .mobile-menu-icon span:nth-child(1) {
            top: 0px;
        }

        .mobile-menu-icon span:nth-child(2) {
            top: 8px;
        }

        .mobile-menu-icon span:nth-child(3) {
            top: 16px;
        }

        /* Fix for hamburger icon size */
        .hamburger-icon {
            font-size: 1.25rem;
            width: 1.25rem;
            height: 1.25rem;
            line-height: 1.25rem;
            text-align: center;
        }

        /* Animation styles for mobile menu */
        #mobile-menu {
            transition: all 0.3s ease-in-out;
        }

        #mobile-menu.hidden {
            display: none;
        }

        #mobile-menu.menu-open {
            display: block;
        }

        /* Smooth transitions for menu items */
        .mobile-nav-link,
        .nav-link {
            transition: all 0.2s ease;
        }

        /* Smooth transition for button hover effects */
        button {
            transition: background-color 0.2s ease;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    <nav class="bg-white shadow-md fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="#"
                            class="text-xl sm:text-2xl font-bold text-blue-600 hover:text-blue-800 truncate max-w-[150px] sm:max-w-none">Le
                            Coursier</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-4 md:space-x-8">
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
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Essai gratuit
                    </button>
                </div>
                <div class="-mr-2 flex items-center sm:hidden">
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
        <div class="sm:hidden hidden" id="mobile-menu">
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
                <div class="flex items-center px-4">
                    <button
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Essai gratuit
                    </button>
                </div>
            </div>
        </div>
    </nav>

    {{ $slot }}

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1">
                    <h3 class="text-lg font-bold text-blue-600 mb-4">Le Coursier</h3>
                    <p class="text-gray-600 text-sm">
                        Solution innovante de livraison pour optimiser vos opérations logistiques et améliorer
                        l'expérience de vos clients.
                    </p>
                </div>

                <!-- Links -->
                <div class="col-span-1">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4">Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">Livraison express</a>
                        </li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">Suivi en temps réel</a>
                        </li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">Logistique sur
                                mesure</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">API d'intégration</a>
                        </li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="col-span-1">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4">Entreprise</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">À propos</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">Carrières</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">Mentions légales</a>
                        </li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm">Confidentialité</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="col-span-1">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4">Restez informé</h3>
                    <p class="text-sm text-gray-600 mb-4">Abonnez-vous à notre newsletter pour recevoir nos actualités
                    </p>
                    <form class="flex">
                        <input type="email" placeholder="Votre email"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 flex-grow">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md text-sm font-medium">
                            S'inscrire
                        </button>
                    </form>
                </div>
            </div>

            <!-- Social Media & Copyright -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <div class="flex justify-between items-center flex-col md:flex-row">
                    <div class="flex space-x-6 mb-4 md:mb-0">
                        <a href="#" class="text-gray-500 hover:text-blue-600">
                            <span class="sr-only">Facebook</span>
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-blue-600">
                            <span class="sr-only">Twitter</span>
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-blue-600">
                            <span class="sr-only">Instagram</span>
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-blue-600">
                            <span class="sr-only">LinkedIn</span>
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                    <p class="text-gray-500 text-sm">
                        &copy; {{ date('Y') }} Le Coursier. Tous droits réservés.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle with simple animation
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const fontAwesomeIcon = mobileMenuButton.querySelector('.fas');
            const fallbackIcon = mobileMenuButton.querySelector('.mobile-menu-icon');

            // Check if Font Awesome is loaded properly
            if (getComputedStyle(fontAwesomeIcon).fontFamily.indexOf('Font Awesome') === -1) {
                fontAwesomeIcon.classList.add('hidden');
                fallbackIcon.classList.remove('hidden');
            }

            // Simple toggle function
            mobileMenuButton.addEventListener('click', function() {
                // Just toggle the hidden class
                mobileMenu.classList.toggle('hidden');
            });

            // Handle section highlighting
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

            // Add the logo link to smooth scroll behavior
            const logoLink = document.querySelector('.flex-shrink-0 a');
            if (logoLink) {
                logoLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }

            // Active class styles
            const activeDesktopClass = 'border-blue-500 text-blue-700';
            const activeDesktopRemoveClass = 'border-transparent text-gray-500';
            const activeMobileClass = 'bg-blue-50 text-blue-700 border-blue-500';
            const activeMobileRemoveClass = 'text-gray-600 border-transparent';

            // Intersection Observer to detect which section is in view
            const observerOptions = {
                root: null,
                rootMargin: '-20% 0px -80% 0px', // Adjust this to change when the section is considered "active"
                threshold: 0
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const activeSection = entry.target.getAttribute('id');

                        // Update desktop navigation
                        navLinks.forEach(link => {
                            const section = link.getAttribute('data-section');
                            if (section === activeSection) {
                                link.classList.remove(...activeDesktopRemoveClass.split(
                                    ' '));
                                link.classList.add(...activeDesktopClass.split(' '));
                            } else {
                                link.classList.remove(...activeDesktopClass.split(' '));
                                link.classList.add(...activeDesktopRemoveClass.split(' '));
                            }
                        });

                        // Update mobile navigation
                        mobileNavLinks.forEach(link => {
                            const section = link.getAttribute('data-section');
                            if (section === activeSection) {
                                link.classList.remove(...activeMobileRemoveClass.split(
                                    ' '));
                                link.classList.add(...activeMobileClass.split(' '));
                            } else {
                                link.classList.remove(...activeMobileClass.split(' '));
                                link.classList.add(...activeMobileRemoveClass.split(' '));
                            }
                        });
                    }
                });
            }, observerOptions);

            // Observe all sections
            sections.forEach(section => {
                observer.observe(section);
            });

            // Handle clicks on nav links for smooth scrolling
            function handleNavClick(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    const yOffset = -80; // Adjust for fixed header
                    const y = targetElement.getBoundingClientRect().top + window.pageYOffset + yOffset;

                    window.scrollTo({
                        top: y,
                        behavior: 'smooth'
                    });

                    // Close mobile menu if open
                    mobileMenu.classList.add('hidden');
                }
            }

            // Add click event listeners
            navLinks.forEach(link => link.addEventListener('click', handleNavClick));
            mobileNavLinks.forEach(link => link.addEventListener('click', handleNavClick));
        });
    </script>
</body>

</html>

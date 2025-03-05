<x-layout>
    <!-- Header with a Lecoursier label -->
    <div class="bg-white shadow-md w-full py-4 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('landing') }}"
                        class="text-xl sm:text-2xl font-bold text-blue-600 hover:text-blue-800 truncate max-w-[150px] sm:max-w-none">
                        Le Coursier
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="py-12 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900">Politique de Confidentialité</h1>
                    <p class="mt-2 text-gray-600">Dernière mise à jour : {{ date('d/m/Y') }}</p>
                </div>

                <div class="prose prose-blue mx-auto">
                    <p class="text-gray-600">
                        Chez Le Coursier, nous accordons une grande importance à la confidentialité de vos données.
                        Cette politique de confidentialité décrit comment nous collectons, utilisons et protégeons vos
                        informations personnelles lorsque vous utilisez notre plateforme.
                    </p>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">1. Informations que nous collectons</h2>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">1.1 Informations fournies par vous</h3>
                    <p class="text-gray-600 mb-4">
                        Nous collectons les informations que vous nous fournissez lorsque vous :
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-4">
                        <li>Créez un compte et configurez votre profil</li>
                        <li>Utilisez nos services de gestion de livraison</li>
                        <li>Communiquez avec notre équipe de support</li>
                        <li>Remplissez des formulaires sur notre site</li>
                        <li>Souscrivez à nos services</li>
                    </ul>
                    <p class="text-gray-600 mb-4">
                        Ces informations peuvent inclure votre nom, adresse email, numéro de téléphone, adresse postale,
                        informations de facturation et informations sur votre entreprise.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">1.2 Informations collectées
                        automatiquement</h3>
                    <p class="text-gray-600 mb-4">
                        Lorsque vous utilisez notre plateforme, nous collectons automatiquement certaines informations,
                        notamment :
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-4">
                        <li>Données de connexion et d'utilisation</li>
                        <li>Informations sur l'appareil (type d'appareil, système d'exploitation, navigateur)</li>
                        <li>Adresse IP et données de localisation (pour les fonctionnalités de suivi de livraison)</li>
                        <li>Cookies et technologies similaires (voir notre section sur les cookies)</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">2. Comment nous utilisons vos informations
                    </h2>
                    <p class="text-gray-600 mb-4">
                        Nous utilisons les informations collectées pour :
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-4">
                        <li>Fournir, maintenir et améliorer nos services de gestion de livraison</li>
                        <li>Traiter les transactions et gérer votre compte</li>
                        <li>Permettre le suivi des livraisons en temps réel</li>
                        <li>Communiquer avec vous concernant votre compte ou nos services</li>
                        <li>Vous envoyer des informations techniques, des mises à jour et des alertes de sécurité</li>
                        <li>Répondre à vos demandes et fournir une assistance client</li>
                        <li>Analyser l'utilisation de notre plateforme et améliorer nos services</li>
                        <li>Détecter, prévenir et résoudre les problèmes techniques ou de sécurité</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">3. Partage des informations</h2>
                    <p class="text-gray-600 mb-4">
                        Nous ne vendons pas vos données personnelles à des tiers. Nous pouvons partager vos informations
                        dans les circonstances suivantes :
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-4">
                        <li>Avec les fournisseurs de services qui nous aident à exploiter notre plateforme (hébergement,
                            traitement des paiements, etc.)</li>
                        <li>Pour se conformer à la loi, aux procédures judiciaires ou aux demandes gouvernementales</li>
                        <li>Pour protéger nos droits, notre propriété ou notre sécurité, ou ceux de nos utilisateurs
                        </li>
                        <li>Lors d'une fusion, acquisition ou vente d'actifs (avec notification préalable)</li>
                        <li>Avec votre consentement ou selon vos instructions</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">4. Sécurité des données</h2>
                    <p class="text-gray-600 mb-4">
                        Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour
                        protéger vos données personnelles contre la perte, l'accès non autorisé, la divulgation,
                        l'altération et la destruction. Ces mesures comprennent :
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-4">
                        <li>Chiffrement des données en transit et au repos</li>
                        <li>Contrôles d'accès stricts pour nos systèmes et bases de données</li>
                        <li>Surveillance régulière des systèmes pour détecter d'éventuelles vulnérabilités</li>
                        <li>Formation de notre personnel sur les pratiques de sécurité des données</li>
                    </ul>
                    <p class="text-gray-600 mb-4">
                        Cependant, aucune méthode de transmission sur Internet ou de stockage électronique n'est
                        totalement sécurisée, et nous ne pouvons garantir une sécurité absolue.
                    </p>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">5. Cookies et technologies similaires</h2>
                    <p class="text-gray-600 mb-4">
                        Nous utilisons des cookies et des technologies de suivi similaires pour collecter et suivre des
                        informations sur votre utilisation de notre plateforme. Vous pouvez configurer votre navigateur
                        pour refuser tous les cookies ou pour vous avertir lorsqu'un cookie est envoyé. Cependant,
                        certaines fonctionnalités de notre service peuvent ne pas fonctionner correctement sans cookies.
                    </p>
                    <p class="text-gray-600 mb-4">
                        Nous utilisons différents types de cookies :
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-4">
                        <li><strong>Cookies essentiels :</strong> Nécessaires au fonctionnement de la plateforme</li>
                        <li><strong>Cookies de performance :</strong> Pour analyser comment les visiteurs utilisent
                            notre site</li>
                        <li><strong>Cookies de fonctionnalité :</strong> Pour mémoriser vos préférences</li>
                        <li><strong>Cookies de ciblage :</strong> Pour afficher des contenus pertinents selon vos
                            intérêts</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">6. Vos droits</h2>
                    <p class="text-gray-600 mb-4">
                        Selon votre lieu de résidence, vous pouvez avoir certains droits concernant vos données
                        personnelles, notamment :
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-4">
                        <li>Accéder à vos données personnelles</li>
                        <li>Rectifier des données inexactes</li>
                        <li>Supprimer vos données</li>
                        <li>Limiter ou vous opposer au traitement</li>
                        <li>Exporter vos données (portabilité)</li>
                        <li>Retirer votre consentement</li>
                    </ul>
                    <p class="text-gray-600 mb-4">
                        Pour exercer ces droits, veuillez nous contacter à <a href="mailto:privacy@lecoursier.app"
                            class="text-blue-600 hover:underline">privacy@lecoursier.app</a>. Nous répondrons à votre
                        demande conformément aux lois applicables.
                    </p>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">7. Conservation des données</h2>
                    <p class="text-gray-600 mb-4">
                        Nous conservons vos données personnelles aussi longtemps que nécessaire pour fournir nos
                        services et respecter nos obligations légales. Lorsque nous n'avons plus besoin de vos données,
                        nous les supprimons ou les anonymisons de manière sécurisée.
                    </p>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">8. Transferts internationaux de données</h2>
                    <p class="text-gray-600 mb-4">
                        Nous pouvons traiter et stocker vos informations dans des pays autres que votre pays de
                        résidence. Ces pays peuvent avoir des lois sur la protection des données différentes des lois de
                        votre pays. Nous prenons des mesures pour garantir que vos données bénéficient d'une protection
                        adéquate lorsqu'elles sont transférées à l'international.
                    </p>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">9. Protection des données des enfants</h2>
                    <p class="text-gray-600 mb-4">
                        Nos services ne s'adressent pas aux personnes de moins de 18 ans. Nous ne collectons pas
                        sciemment des données personnelles auprès d'enfants. Si vous êtes parent ou tuteur et que vous
                        pensez que votre enfant nous a fourni des informations personnelles, veuillez nous contacter
                        pour que nous puissions prendre les mesures nécessaires.
                    </p>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">10. Modifications de cette politique</h2>
                    <p class="text-gray-600 mb-4">
                        Nous pouvons mettre à jour cette politique de confidentialité de temps à autre pour refléter les
                        changements dans nos pratiques ou pour d'autres raisons opérationnelles, légales ou
                        réglementaires. Nous vous informerons de tout changement important en publiant la nouvelle
                        politique sur cette page et en mettant à jour la date de "dernière mise à jour".
                    </p>
                    <p class="text-gray-600 mb-4">
                        Nous vous encourageons à consulter régulièrement cette politique pour rester informé de la façon
                        dont nous protégeons vos informations.
                    </p>

                    <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">11. Nous contacter</h2>
                    <p class="text-gray-600 mb-4">
                        Si vous avez des questions concernant cette politique de confidentialité ou nos pratiques en
                        matière de données, veuillez nous contacter à :
                    </p>
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <p class="text-gray-700 mb-1"><strong>Le Coursier</strong></p>
                        <p class="text-gray-700 mb-1">123 Avenue Habib Bourguiba</p>
                        <p class="text-gray-700 mb-1">8000 Nabeul, Tunisie</p>
                        <p class="text-gray-700 mb-1">Email: <a href="mailto:privacy@lecoursier.app"
                                class="text-blue-600 hover:underline">privacy@lecoursier.app</a></p>
                        <p class="text-gray-700">Téléphone: +216 00 000 000</p>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('landing') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>

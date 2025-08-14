# Documentation de la Fonctionnalité Statistiques - Le Coursier

## Vue d'ensemble

Le système de statistiques de Le Coursier offre une vue d'ensemble complète des performances et de l'activité de l'application. Cette fonctionnalité permet aux administrateurs de visualiser des métriques détaillées sur les tâches, les coursiers, et les milestones à travers différentes périodes.

## Architecture

### Structure des Fichiers

-   **Controller** : `app/Http/Controllers/Web/StatisticsController.php`
-   **Service** : `app/Services/StatisticsService.php`
-   **Vues** :
    -   `resources/views/pages/statistics/index.blade.php` (tableau de bord principal)
    -   `resources/views/pages/statistics/couriers.blade.php` (performance détaillée des coursiers)
-   **Routes** : Définies dans `routes/web.php`

### Pattern Architecture

L'implémentation suit le pattern **Controller-Service-Repository** :

-   Le **Controller** gère les requêtes HTTP et la logique de présentation
-   Le **Service** contient toute la logique métier et les calculs statistiques
-   Les **Models** Eloquent servent de couche d'accès aux données

## Fonctionnalités Principales

### 1. Tableau de Bord Principal (`/statistics`)

#### Statistiques Affichées :

**Métriques Globales des Tâches :**

-   Nombre total de tâches
-   Tâches en attente (`pending`)
-   Tâches en cours (`in_progress`)
-   Tâches terminées (`completed`)
-   Tâches annulées (`canceled`)
-   Taux de complétion (pourcentage)

**Statistiques de Temps :**

-   Temps moyen de complétion
-   Temps maximum de complétion
-   Temps minimum de complétion

**Répartition par Priorité :**

-   Tâches par niveau de priorité (`low`, `medium`, `high`)

**Répartition Temporelle :**

-   Tâches par jour de la semaine
-   Évolution sur les 12 derniers mois (créées vs terminées)

**Top 5 des Coursiers :**

-   Classement des 5 meilleurs coursiers par taux de complétion

**Statistiques des Milestones :**

-   Nombre total de milestones
-   Milestones favoris
-   Moyenne de tâches par milestone
-   Milestone le plus utilisé

### 2. Page des Coursiers (`/statistics/couriers`)

#### Fonctionnalités :

-   Liste paginée de tous les coursiers (15 par page)
-   Performance détaillée par coursier :
    -   Nombre total de tâches assignées
    -   Tâches terminées
    -   Tâches en attente
    -   Tâches en cours
    -   Taux de complétion

## Système de Filtrage

### Filtres Prédéfinis

Le système propose plusieurs filtres temporels prédéfinis :

-   **Aujourd'hui** (`today`)
-   **Hier** (`yesterday`)
-   **Cette semaine** (`this_week`)
-   **Semaine dernière** (`last_week`)
-   **Ce mois** (`this_month`)
-   **Mois dernier** (`last_month`)
-   **Cette année** (`this_year`)
-   **Année dernière** (`last_year`)
-   **Tout** (`all`) - Aucun filtrage

### Filtre Personnalisé

Un filtre `custom` permet de sélectionner une plage de dates spécifique avec :

-   Date de début
-   Date de fin

### Implémentation du Filtrage

```php
private function getDateRangeFromFilterType($filterType)
{
    switch ($filterType) {
        case 'today':
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
            break;
        // ... autres cas
    }
    return [$startDate, $endDate];
}
```

## Système de Cache

### Stratégie de Cache

Pour optimiser les performances, un système de cache intelligent est implémenté :

-   **Durée** : 30 minutes (1800 secondes)
-   **Clé de cache** : Basée sur un hash MD5 des paramètres de filtre
-   **Granularité** : Cache séparé pour le tableau de bord et la page coursiers

### Exemple d'Implémentation

```php
$cacheKey = 'statistics.dashboard.' . md5(json_encode([
    'startDate' => $startDate,
    'endDate' => $endDate,
    'filterType' => $filterType,
]));

$stats = Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate) {
    $this->statisticsService->setDateRange($startDate, $endDate);
    return $this->statisticsService->getAllStats();
});
```

## Service de Statistiques Détaillé

### Méthodes Principales

#### `setDateRange($startDate, $endDate)`

Configure la plage de dates pour filtrer toutes les statistiques.

#### `getTaskStats()`

Retourne les statistiques globales des tâches :

```php
[
    'total' => 150,
    'pending' => 45,
    'in_progress' => 30,
    'completed' => 70,
    'canceled' => 5,
    'completion_rate' => 46.67
]
```

#### `getTaskTimeStats()`

Calcule les statistiques de temps de complétion :

```php
[
    'avg_completion_time' => 2.5,  // en heures
    'max_completion_time' => 8.0,
    'min_completion_time' => 0.5,
    'avg_completion_seconds' => 9000,
    'max_completion_seconds' => 28800,
    'min_completion_seconds' => 1800
]
```

#### `getTasksByPriority()`

Répartition des tâches par priorité :

```php
[
    'low' => 20,
    'medium' => 100,
    'high' => 30
]
```

#### `getTasksByDayOfWeek()`

Répartition par jour de la semaine :

```php
[
    'Lundi' => 25,
    'Mardi' => 30,
    // ...
    'Dimanche' => 15
]
```

#### `getTasksByMonth()`

Évolution sur 12 mois :

```php
[
    'Janvier 2025' => [
        'created' => 45,
        'completed' => 38
    ],
    // ...
]
```

#### `getTasksByUserPaginated($page, $perPage)`

Liste paginée des performances utilisateurs avec calcul du taux de complétion.

#### `getMilestoneStats()`

Statistiques des milestones :

```php
[
    'total' => 12,
    'favorites' => 3,
    'tasks_per_milestone' => 12.5,
    'most_used' => [
        'id' => 5,
        'name' => 'Centre Ville',
        'tasks_count' => 45
    ]
]
```

### Gestion des Filtres de Date

#### `applyDateFilters($query, $dateField)`

Applique les filtres de date à une requête Eloquent :

```php
if ($this->startDate) {
    $query->where($dateField, '>=', $this->startDate);
}
if ($this->endDate) {
    $query->where($dateField, '<=', $this->endDate);
}
```

#### `getCurrentFilterInfo()`

Fournit une description textuelle du filtre actuel :

```php
[
    'is_filtered' => true,
    'description' => 'Du 01/01/2025 au 31/01/2025'
]
```

## Interface Utilisateur

### Technologies Utilisées

-   **Framework CSS** : Tailwind CSS
-   **Graphiques** : Chart.js
-   **Responsive Design** : Mobile-first approach

### Composants Visuels

1. **Cartes de Métriques** : Affichage des KPIs principaux
2. **Graphiques** :
    - Graphique en secteurs pour les priorités
    - Graphique en barres pour les jours de la semaine
    - Graphique linéaire pour l'évolution mensuelle
3. **Tableaux** : Performance des coursiers avec pagination
4. **Formulaires de Filtre** : Interface intuitive pour sélectionner les périodes

### Responsive Design

L'interface s'adapte aux différentes tailles d'écran :

-   **Desktop** : Grille à 4 colonnes pour les métriques
-   **Tablet** : Grille à 2 colonnes
-   **Mobile** : Affichage en colonne unique

## Sécurité et Autorisations

### Contrôle d'Accès

-   **Middleware** : `tenant.auth` vérifie l'authentification du tenant
-   **Accès Restreint** : Fonctionnalité réservée aux utilisateurs abonnés (`session('subscribed') == true`)
-   **Isolation Tenant** : Toutes les données sont filtrées par tenant automatiquement

### Validation des Données

-   Validation des paramètres de date
-   Sanitisation des entrées utilisateur
-   Protection contre les injections SQL via Eloquent ORM

## Optimisations Performances

### Base de Données

1. **Index** : Index sur les colonnes `created_at`, `completed_at`, `status`, `priority`
2. **Requêtes Optimisées** : Utilisation de `withCount()` pour éviter les N+1 queries
3. **Pagination** : Limitation des résultats pour la page coursiers

### Requêtes SQL

Exemples de requêtes optimisées :

```sql
-- Statistiques par jour de la semaine (PostgreSQL)
SELECT EXTRACT(DOW FROM created_at) as day, count(*) as count
FROM tasks
WHERE created_at >= ? AND created_at <= ?
GROUP BY day

-- Top coursiers avec calcul de taux
SELECT users.*,
       count(tasks.id) as total_tasks,
       sum(case when tasks.status = 'completed' then 1 else 0 end) as completed_tasks
FROM users
LEFT JOIN tasks ON users.id = tasks.user_id
WHERE users.role != 'admin'
GROUP BY users.id
```

## Tests

### Coverage des Tests

-   **Tests Unitaires** : `tests/Unit/Services/StatisticsServiceTest.php`
-   **Tests de Politique** : Vérification des autorisations
-   **Tests d'Intégration** : Tests des contrôleurs et vues

### Cas de Tests Principaux

1. **Calculs Statistiques** : Vérification de la précision des calculs
2. **Filtrage par Date** : Test des différents filtres temporels
3. **Pagination** : Test de la pagination des coursiers
4. **Autorisations** : Vérification des contrôles d'accès

### Migration et Données

Les statistiques s'appuient sur les tables existantes :

-   `tasks` : Données principales des tâches
-   `users` : Informations des coursiers
-   `milestones` : Données des milestones

## Conclusion

Le système de statistiques de Le Coursier offre une solution complète et performante pour l'analyse des données opérationnelles. Son architecture modulaire, son système de cache intelligent, et son interface responsive en font un outil puissant pour le pilotage de l'activité des coursiers.

La séparation claire des responsabilités entre le contrôleur, le service, et les modèles facilite la maintenance et l'évolution du système. Le système de filtrage flexible et le cache optimisé garantissent une expérience utilisateur fluide même avec de gros volumes de données.

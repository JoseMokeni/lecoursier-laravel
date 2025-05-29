# Documentation du Système de Badges et Leaderboard - Le Coursier

## Vue d'ensemble

Le système de badges de Le Coursier est un système de gamification complet qui récompense les utilisateurs pour leurs performances et leur engagement. Il comprend un système de badges automatiques, des statistiques détaillées, un système de niveaux d'expérience, et des classements compétitifs.

## Architecture du Système

### Composants Principaux

1. **Système Événementiel** : Les événements de completion de tâches déclenchent la mise à jour des statistiques et l'attribution automatique des badges
2. **Statistiques Utilisateur** : Suivi complet des métriques de performance
3. **Système de Badges** : Attribution automatique basée sur des critères prédéfinis
4. **Expérience & Niveaux** : Système de progression basé sur les points d'expérience
5. **Classements** : Système de ranking pour l'engagement compétitif

### Flux de Données

```
Completion Tâche → Événement TaskCompleted → TaskCompletedListener → UserStatsService → BadgeService
                                                                     ↓
                                               Mise à jour des stats & Attribution des badges
```

## Modèles de Données

### Badge

-   **Champs** : name, description, icon, category, criteria, points, rarity, is_active
-   **Catégories** : task_completion, speed, consistency, milestone, special, points, streak
-   **Niveaux de rareté** : bronze, silver, gold, platinum
-   **Critères** : JSON définissant les conditions d'obtention

### UserBadge

-   **Champs** : user_id, badge_id, earned_at, progress
-   **Relations** : Pivot entre User et Badge avec données supplémentaires

### UserStats

-   **Métriques** :
    -   Tâches : total_tasks_completed, monthly_tasks_completed, weekly_tasks_completed
    -   Points : total_points, monthly_points, weekly_points, experience_points
    -   Performance : avg_completion_time, fastest_completion_time
    -   Consistance : current_streak, longest_streak, last_task_date
    -   Progression : level, total_distance_km

## Système de Points

### Calcul des Points de Tâches

1. **Points de Base** : 10 points pour toute tâche complétée
2. **Bonus de Priorité** :
    - Faible : +0 points
    - Moyenne : +5 points
    - Haute : +10 points
    - Urgente : +15 points
3. **Bonus de Vitesse** : +5 points pour les tâches complétées en moins de 30 minutes

**Exemple** : Tâche haute priorité complétée en 25 minutes = 10 + 10 + 5 = 25 points

### Points de Badges

Chaque badge attribue des points supplémentaires lors de son obtention (varie selon la difficulté).

### Expérience & Niveaux

-   Points d'expérience = Total des points gagnés
-   Calcul du niveau : `niveau = floor(points_experience / 1000) + 1`
-   XP pour le niveau suivant : `niveau_actuel * 1000`

## Badges Disponibles

### Badges de Completion de Tâches

-   **First Delivery** (10 pts, Bronze) : Compléter 1 tâche
-   **Getting Started** (20 pts, Bronze) : Compléter 5 tâches
-   **Task Master** (50 pts, Silver) : Compléter 25 tâches
-   **Marathon Runner** (100 pts, Gold) : Compléter 50 tâches
-   **Legendary Courier** (200 pts, Platinum) : Compléter 100 tâches

### Badges de Vitesse

-   **Speed Demon** (30 pts, Silver) : 5 tâches en moins de 30 minutes
-   **Lightning Fast** (50 pts, Gold) : 10 tâches en moins de 20 minutes
-   **Efficiency Expert** (40 pts, Gold) : Maintenir un temps moyen < 30 minutes

### Badges de Consistance

-   **Streak Starter** (20 pts, Bronze) : 3 jours consécutifs
-   **Consistent Performer** (35 pts, Silver) : 7 jours consécutifs
-   **Unstoppable** (100 pts, Platinum) : 30 jours consécutifs

### Badges de Points

-   **Point Collector** (25 pts, Bronze) : 100 points totaux
-   **High Achiever** (50 pts, Gold) : 500 points totaux

### Badges Spéciaux

-   **Early Bird** (25 pts, Silver) : Première tâche avant 6h du matin
-   **Night Owl** (25 pts, Silver) : Tâche après 22h
-   **Perfect Month** (150 pts, Platinum) : Au moins une tâche par jour pendant un mois

## API Endpoints

### 1. Obtenir toutes les statistiques utilisateur

```
GET /api/user/stats
Headers: Authorization: Bearer {token}, x-tenant-id: {tenant}
```

### 2. Obtenir tous les badges avec progression

```
GET /api/badges?category={category}&earned={true|false}
Headers: Authorization: Bearer {token}, x-tenant-id: {tenant}
```

### 3. Obtenir les badges gagnés

```
GET /api/user/badges?category={category}&limit={number}
Headers: Authorization: Bearer {token}, x-tenant-id: {tenant}
```

### 4. Obtenir le classement

```
GET /api/leaderboard?limit={number}&period={week|month|all}
Headers: Authorization: Bearer {token}, x-tenant-id: {tenant}
```

## Interface Web

### Pages Disponibles

1. **Dashboard Badges** (`/badges`) : Vue d'ensemble avec statistiques et graphiques
2. **Détail Badge** (`/badges/{id}`) : Informations détaillées d'un badge
3. **Classements** (`/badges/leaderboard`) : Rankings par points, badges, tâches, niveaux
4. **Progression Utilisateurs** (`/badges/user-progress`) : Vue admin de tous les utilisateurs

### Fonctionnalités Web

-   **Graphiques interactifs** : Badges les plus gagnés, distribution par rareté
-   **Filtres avancés** : Par catégorie, rareté, statut d'obtention
-   **Pagination** : Pour les grandes listes
-   **Recherche** : Par nom d'utilisateur dans les classements
-   **Tri dynamique** : Par différents critères (points, badges, niveau, tâches)

## Logique d'Attribution des Badges

### Processus d'Évaluation

1. La completion d'une tâche déclenche l'événement `TaskCompleted`
2. Le `TaskCompletedListener` met à jour les statistiques utilisateur
3. Le `BadgeService` évalue tous les critères de badges
4. Les nouveaux badges sont automatiquement attribués
5. Les événements de badges peuvent déclencher des notifications

### Critères d'Exemple

```php
// Badges de Réalisation
"First Delivery" => "total_tasks_completed >= 1"
"Getting Started" => "total_tasks_completed >= 5"
"Task Master" => "total_tasks_completed >= 25"

// Badges de Performance
"Speed Demon" => "fast_completions >= 5"
"Efficiency Expert" => "avg_completion_time <= 30"

// Badges de Consistance
"Streak Starter" => "current_streak >= 3"
"Consistent Performer" => "current_streak >= 7"

// Badges de Points
"Point Collector" => "total_points >= 100"
"High Achiever" => "total_points >= 500"
```

## Suivi des Statistiques

### Métriques Principales

-   **Tâches** : Compteurs total, mensuel, hebdomadaire
-   **Points** : Total gagné, répartition mensuelle/hebdomadaire
-   **Performance** : Temps de completion (plus rapide, plus lent, moyenne)
-   **Consistance** : Série actuelle, plus longue série, date dernière tâche
-   **Distance** : Kilomètres totaux parcourus (si disponible)

### Métriques Calculées

-   **Taux de Completion** : Actuellement 100% (suppose toutes les tâches assignées complétées)
-   **Score de Performance** : Moyenne pondérée de vitesse, consistance et activité
-   **Progression vers Niveau Suivant** : Pourcentage vers le prochain niveau d'expérience

## Considérations de Performance

### 1. Stratégie de Cache

-   Cache des stats utilisateur pendant 5 minutes
-   Cache de la progression des badges pendant 10 minutes
-   Cache des classements pendant 15 minutes

### 2. Opérations par Lot

-   Groupement des évaluations de badges pour réduire les requêtes
-   Utilisation de transactions pour les mises à jour de stats
-   Implémentation de queues pour les calculs lourds

### 3. Pagination

Les classements supportent la pagination :

```
GET /api/leaderboard?limit=20&offset=20
```

## Mise à jour en Temps Réel

### Événements WebSocket (si implémenté)

-   `badge.earned` : Quand un utilisateur gagne un badge
-   `level.up` : Quand un utilisateur atteint un nouveau niveau
-   `leaderboard.updated` : Quand les classements changent

### Notifications Push

L'obtention de badges peut déclencher des notifications FCM :

```json
{
    "title": "Badge Gagné!",
    "body": "Félicitations! Vous avez gagné le badge 'Speed Demon'",
    "data": {
        "type": "badge_earned",
        "badge_id": 2,
        "points": 25
    }
}
```

## Évolutions Futures

### Fonctionnalités Prévues

1. **Badges Personnalisés** : Permettre aux admins de créer des badges personnalisés
2. **Badges d'Équipe** : Badges pour les réalisations d'équipe
3. **Événements Saisonniers** : Badges temporaires à durée limitée
4. **Échange de Badges** : Permettre aux utilisateurs d'échanger certains badges
5. **Fonctionnalités Sociales** : Partager les réalisations sur les réseaux sociaux
6. **Analytics Avancées** : Insights détaillés de performance

### Seeders

-   `BadgeSeeder` : 20+ badges prédéfinis avec critères complets
-   Badges par default seeded à la création d'un tenant

## Application mobile

Le coursier peut voir sur sa page profil les badges obtenus et sa progression.

## Résumé

Le système de badges de Le Coursier offre une expérience de gamification complète et robuste qui motive les utilisateurs à travers :

-   **Attribution automatique** de badges basée sur des critères précis
-   **Suivi détaillé** des performances et statistiques
-   **Système de progression** avec niveaux et expérience
-   **Classements compétitifs** encourageant l'engagement
-   **Interface moderne** avec graphiques et filtres avancés
-   **API complète** pour intégration mobile
-   **Architecture extensible** pour futures améliorations

Le système est entièrement implémenté, testé, et prêt pour la production avec une base solide pour les évolutions futures.

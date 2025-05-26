# Badge System Integration Guide

## Overview

The badge system is a gamification feature that rewards users for completing tasks and achieving milestones. It includes user statistics tracking, badge awarding, experience points, levels, and leaderboards.

## System Architecture

### Core Components

1. **Event-Driven System**: Task completion triggers events that update user stats and award badges
2. **User Statistics**: Comprehensive tracking of user performance metrics
3. **Badge System**: Automatic badge awarding based on predefined criteria
4. **Experience & Levels**: Progression system based on experience points
5. **Leaderboards**: Ranking system for competitive engagement

### Data Flow

```
Task Completion → TaskCompleted Event → TaskCompletedListener → UserStatsService → BadgeService
                                                               ↓
                                                         Update Stats & Award Badges
```

## API Endpoints

### Base URL

All API endpoints require:

-   **Authorization**: `Bearer {token}`
-   **Headers**: `x-tenant-id: {tenant_id}`

### 1. Complete Task

Triggers the badge system when a task is completed.

**Endpoint**: `POST /api/tasks/{id}/complete`

**Headers**:

```
Authorization: Bearer {access_token}
x-tenant-id: {tenant_id}
Content-Type: application/json
```

**Response** (200 OK):

```json
{
    "message": "Task completed successfully",
    "data": {
        "id": 1,
        "name": "Deliver package to downtown",
        "description": "High priority delivery",
        "priority": "high",
        "status": "completed",
        "dueDate": "2025-05-25T15:00:00.000000Z",
        "completedAt": "2025-05-25T14:30:00.000000Z",
        "userId": 2,
        "user": {
            "id": 2,
            "name": "John Doe",
            "username": "johndoe",
            "email": "john@example.com"
        },
        "milestoneId": 1,
        "milestone": {
            "id": 1,
            "name": "Downtown Route",
            "description": "Main downtown delivery route"
        },
        "createdAt": "2025-05-25T10:00:00.000000Z",
        "updatedAt": "2025-05-25T14:30:00.000000Z"
    },
    "points_earned": 15,
    "badges_earned": [
        {
            "id": 1,
            "name": "First Delivery",
            "description": "Complete your first task",
            "category": "achievement",
            "points": 10,
            "criteria": "total_tasks_completed >= 1"
        }
    ]
}
```

### 2. Get User Statistics

Retrieve comprehensive user performance statistics.

**Endpoint**: `GET /api/user/stats`

**Headers**:

```
Authorization: Bearer {access_token}
x-tenant-id: {tenant_id}
```

**Response** (200 OK):

```json
{
    "data": {
        "userId": 2,
        "level": 2,
        "experiencePoints": 125,
        "experienceToNextLevel": 875,
        "progressToNextLevel": 12.5,

        "totalTasksCompleted": 5,
        "tasksThisMonth": 3,
        "tasksThisWeek": 2,

        "totalPoints": 75,
        "pointsThisMonth": 45,
        "pointsThisWeek": 30,

        "totalDistanceKm": 25.5,

        "fastestCompletionTime": 15.5,
        "slowestCompletionTime": 120.0,
        "avgCompletionTime": 45.75,

        "currentStreak": 3,
        "longestStreak": 5,
        "lastTaskDate": "2025-05-25",

        "badgesCount": 3,
        "completionRate": 100.0,
        "performanceScore": 85.5,
        "createdAt": "2025-05-20T10:00:00.000000Z",
        "updatedAt": "2025-05-25T14:30:00.000000Z"
    }
}
```

### 3. Get User Badges

Retrieve all badges earned by the user.

**Endpoint**: `GET /api/user/badges`

**Headers**:

```
Authorization: Bearer {access_token}
x-tenant-id: {tenant_id}
```

**Response** (200 OK):

```json
{
    "data": [
        {
            "id": 1,
            "name": "First Delivery",
            "description": "Complete your first task",
            "category": "achievement",
            "points": 10,
            "criteria": "total_tasks_completed >= 1",
            "earnedAt": "2025-05-20T12:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Speed Demon",
            "description": "Complete 5 tasks in under 30 minutes",
            "category": "performance",
            "points": 25,
            "criteria": "fast_completions >= 5",
            "earnedAt": "2025-05-22T16:30:00.000000Z"
        }
    ]
}
```

### 4. Get All Available Badges

Retrieve all badges with user progress.

**Endpoint**: `GET /api/badges`

**Headers**:

```
Authorization: Bearer {access_token}
x-tenant-id: {tenant_id}
```

**Response** (200 OK):

```json
{
    "data": [
        {
            "id": 1,
            "name": "First Delivery",
            "description": "Complete your first task",
            "category": "achievement",
            "points": 10,
            "criteria": "total_tasks_completed >= 1",
            "earned": true,
            "earnedAt": "2025-05-20T12:00:00.000000Z",
            "progress": 100,
            "currentValue": 5,
            "targetValue": 1
        },
        {
            "id": 3,
            "name": "Marathon Runner",
            "description": "Complete 50 tasks",
            "category": "achievement",
            "points": 100,
            "criteria": "total_tasks_completed >= 50",
            "earned": false,
            "earnedAt": null,
            "progress": 10,
            "currentValue": 5,
            "targetValue": 50
        }
    ]
}
```

### 5. Get Leaderboard

Retrieve user rankings based on total points.

**Endpoint**: `GET /api/leaderboard`

**Headers**:

```
Authorization: Bearer {access_token}
x-tenant-id: {tenant_id}
```

**Query Parameters**:

-   `limit` (optional): Number of users to return (default: 10)
-   `period` (optional): `week`, `month`, or `all` (default: `all`)

**Response** (200 OK):

```json
{
    "data": [
        {
            "rank": 1,
            "user": {
                "id": 1,
                "name": "Alice Johnson",
                "username": "alice"
            },
            "totalPoints": 250,
            "totalTasksCompleted": 15,
            "badgesCount": 8
        },
        {
            "rank": 2,
            "user": {
                "id": 2,
                "name": "John Doe",
                "username": "johndoe"
            },
            "totalPoints": 75,
            "totalTasksCompleted": 5,
            "badgesCount": 3
        }
    ]
}
```

### 6. Get Badge Categories

Retrieve available badge categories.

**Endpoint**: `GET /api/badges/categories`

**Headers**:

```
Authorization: Bearer {access_token}
x-tenant-id: {tenant_id}
```

**Response** (200 OK):

```json
{
    "data": ["achievement", "performance", "consistency", "distance", "speed"]
}
```

## Points System

### Task Completion Points

Points are calculated based on multiple factors:

1. **Base Points**: 10 points for any completed task
2. **Priority Bonus**:
    - Low: +0 points
    - Medium: +5 points
    - High: +10 points
    - Urgent: +15 points
3. **Speed Bonus**: +5 points for tasks completed under 30 minutes

**Example**: High priority task completed in 25 minutes = 10 + 10 + 5 = 25 points

### Badge Points

Each badge awards additional points when earned (varies by badge difficulty).

### Experience & Levels

-   Experience points = Total points earned
-   Level calculation: `level = floor(experience_points / 1000) + 1`
-   Next level XP: `current_level * 1000`

## Badge System Logic

### Badge Criteria Examples

```php
// Achievement Badges
"First Delivery" => "total_tasks_completed >= 1"
"Getting Started" => "total_tasks_completed >= 5"
"Task Master" => "total_tasks_completed >= 25"
"Marathon Runner" => "total_tasks_completed >= 50"

// Performance Badges
"Speed Demon" => "fast_completions >= 5"
"Efficiency Expert" => "avg_completion_time <= 30"

// Consistency Badges
"Streak Starter" => "current_streak >= 3"
"Consistent Performer" => "current_streak >= 7"
"Unstoppable" => "longest_streak >= 30"

// Point Badges
"Point Collector" => "total_points >= 100"
"High Achiever" => "total_points >= 500"
```

### Badge Evaluation Process

1. Task completion triggers `TaskCompleted` event
2. `TaskCompletedListener` updates user stats
3. `BadgeService` evaluates all badge criteria against updated stats
4. New badges are automatically awarded and saved
5. Badge earned events can trigger notifications

## Statistics Tracking

### Core Metrics

-   **Tasks**: Total completed, monthly, weekly counters
-   **Points**: Total earned, monthly, weekly breakdown
-   **Performance**: Completion times (fastest, slowest, average)
-   **Consistency**: Current streak, longest streak, last task date
-   **Distance**: Total kilometers traveled (if available)

### Calculated Metrics

-   **Completion Rate**: Currently 100% (assumes all assigned tasks completed)
-   **Performance Score**: Weighted average of speed, consistency, and activity
-   **Progress to Next Level**: Percentage toward next experience level

## Real-time Updates

### WebSocket Events (if implemented)

-   `badge.earned`: When user earns a new badge
-   `level.up`: When user reaches new level
-   `leaderboard.updated`: When leaderboard rankings change

### Push Notifications

Badge earning can trigger FCM notifications:

```json
{
    "title": "Badge Earned!",
    "body": "Congratulations! You earned the 'Speed Demon' badge",
    "data": {
        "type": "badge_earned",
        "badge_id": 2,
        "points": 25
    }
}
```

## Mobile App Implementation Tips

### 1. Local Caching

Cache user stats and badges locally to provide instant feedback:

```javascript
// Example structure
const userStats = {
  level: 2,
  experiencePoints: 125,
  totalPoints: 75,
  badges: [...],
  // ... other stats
}
```

### 2. Progress Indicators

Use the progress data to show badge completion:

```javascript
const badgeProgress = {
    id: 3,
    name: "Marathon Runner",
    progress: 10, // 10%
    currentValue: 5,
    targetValue: 50,
};
```

### 3. Animations

Implement smooth animations for:

-   XP bar progression
-   Badge earning celebrations
-   Level up effects
-   Leaderboard rank changes

### 4. Offline Support

Store badge progress locally and sync when online:

-   Queue completed tasks for badge evaluation
-   Show estimated progress during offline periods
-   Sync and update when connection restored

## Error Handling

### Common Error Responses

**401 Unauthorized**:

```json
{
    "message": "Unauthenticated"
}
```

**403 Forbidden**:

```json
{
    "message": "This action is unauthorized"
}
```

**404 Not Found**:

```json
{
    "message": "Task not found"
}
```

**422 Validation Error**:

```json
{
    "message": "The given data was invalid",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Performance Considerations

### 1. Caching Strategy

-   Cache user stats for 5 minutes
-   Cache badge progress for 10 minutes
-   Cache leaderboard for 15 minutes

### 2. Batch Operations

-   Group badge evaluations to reduce database queries
-   Use database transactions for stat updates
-   Implement queue for heavy badge calculations

### 3. Pagination

Leaderboard supports pagination:

```
GET /api/leaderboard?limit=20&offset=20
```

## Testing

### Badge System Tests

The system includes comprehensive tests covering:

-   Event firing and listener execution
-   User stats creation and updates
-   Badge awarding logic
-   API endpoint responses
-   Edge cases and error conditions

Run tests:

```bash
docker compose exec app php artisan test --filter=BadgeSystemTest
```

## Security Notes

1. **Tenant Isolation**: All badge data is isolated per tenant
2. **User Authorization**: Users can only access their own stats and badges
3. **Admin Access**: Only admins can view all user statistics
4. **Rate Limiting**: Consider implementing rate limits on stat endpoints
5. **Data Validation**: All inputs are validated before processing

## Future Enhancements

### Planned Features

1. **Custom Badges**: Allow admins to create custom badges
2. **Team Badges**: Badges for team achievements
3. **Seasonal Events**: Time-limited special badges
4. **Badge Trading**: Allow users to trade certain badges
5. **Social Features**: Share achievements on social platforms
6. **Advanced Analytics**: Detailed performance insights

### API Versioning

Current version: `v1`
Future versions will maintain backward compatibility.

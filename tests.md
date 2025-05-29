# Le Coursier Application Test Documentation

This document provides a comprehensive overview of the test suite for the Le Coursier Laravel application. The tests are organized by type and functionality, covering authentication, user management, contact forms, and basic page loading.

## Table of Contents

-   Overview
-   Feature Tests
    -   Authentication Tests
    -   API Authentication Tests
    -   User Management Tests
    -   Middleware Tests
    -   Controller Tests
    -   API Controller Tests
    -   Page Load Tests (Pest)
    -   Task History Controller Tests
    -   Dashboard Controller Tests
    -   Badge System Tests
-   Unit Tests
    -   Contact Form Tests
    -   Task Policy Tests
    -   FCM Service Tests
    -   Tenant Service Tests
    -   Statistics Service Unit Tests
-   Browser Tests

## Overview

The application follows a multi-tenancy architecture using the `stancl/tenancy` package. Most tests are built using PHPUnit with some newer tests using Pest PHP syntax. The test suite is divided into:

-   **Feature Tests**: Integration tests for authentication flows, user management, API endpoints, and basic page loading.
-   **Unit Tests**: Focused tests for specific components like the contact form.
-   **Browser Tests**: Basic page loading tests (Note: `PageLoadTest.php` is currently under Feature Tests).

The custom `DatabaseRefresh` trait is used to manage test databases, particularly for tenant databases in the SQLite-based testing environment.

## Feature Tests

### Authentication Tests

#### Login Tests (`tests/Feature/Auth/LoginTest.php`)

| Test Case                                                | Description                                                     | Assertions                                                                         |
| -------------------------------------------------------- | --------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| `test_login_page_loads`                                  | Verifies the login page renders correctly                       | Page loads with status 200 and correct view                                        |
| `test_successful_login_for_admin`                        | Tests successful admin login flow                               | User is authenticated, redirected to dashboard, and tenant ID is stored in session |
| `test_login_fails_with_invalid_company_code`             | Tests login with non-existent company code                      | Login fails with appropriate error message                                         |
| `test_login_fails_with_invalid_credentials`              | Tests login with wrong password                                 | Login fails with appropriate error message                                         |
| `test_login_fails_with_nonexistent_username`             | Tests login with username that doesn't exist                    | Login fails with appropriate error message                                         |
| `test_non_admin_users_cannot_login`                      | Tests that regular users cannot log in to admin area            | Login fails with role-related error message                                        |
| `test_inactive_users_cannot_login`                       | Tests that inactive admin users cannot log in                   | Login fails with status-related error message                                      |
| `test_login_validation_errors`                           | Tests validation errors for empty form submission               | Appropriate validation errors are shown for each required field                    |
| `test_authenticated_user_redirected_from_login_page`     | Tests redirection for already authenticated users               | User is redirected to dashboard                                                    |
| `test_login_page_with_invalid_tenant_id_in_session`      | Tests handling of invalid tenant ID in session                  | Invalid tenant ID is cleared from session                                          |
| `test_login_validation_error_messages`                   | Tests specific error message content                            | Error messages match expected language text                                        |
| `test_login_with_tenant_id_in_session_but_not_logged_in` | Tests behavior with tenant ID in session but user not logged in | Login page is shown with tenant ID preserved                                       |

#### Registration Tests (`tests/Feature/Auth/RegisterTest.php`)

| Test Case                                     | Description                                                  | Assertions                                                                                       |
| --------------------------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------------------------------------------ |
| `test_registration_page_loads`                | Verifies the registration page renders correctly             | Page loads with status 200 and correct view                                                      |
| `test_successful_registration`                | Tests complete registration flow                             | Tenant is created, company is created, user is created, authenticated, and welcome email is sent |
| `test_registration_fails_with_duplicate_code` | Tests registration with already used company code            | Registration fails with appropriate error message                                                |
| `test_registration_fails_with_invalid_input`  | Tests validation for invalid registration data               | Registration fails with appropriate validation errors                                            |
| `test_registration_completes_when_mail_fails` | Tests that registration succeeds even if email sending fails | Registration completes successfully despite mail failure                                         |
| `test_validation_error_messages`              | Tests specific error message content                         | Error messages match expected language text                                                      |

### API Authentication Tests (`tests/Feature/Api/Auth/ApiLoginTest.php`)

| Test Case                                       | Description                                                 | Assertions                                                  |
| ----------------------------------------------- | ----------------------------------------------------------- | ----------------------------------------------------------- |
| `test_successful_login`                         | Tests API login with valid credentials                      | Returns 200 status, token, and correct user data            |
| `test_login_fails_with_incorrect_password`      | Tests API login with wrong password                         | Returns 401 status with invalid credentials error           |
| `test_login_fails_with_nonexistent_username`    | Tests API login with username that doesn't exist            | Returns 401 status with invalid credentials error           |
| `test_login_fails_for_inactive_user`            | Tests API login attempt by inactive user                    | Returns 403 status with user inactive error                 |
| `test_login_validation_errors`                  | Tests validation errors for missing API login fields        | Returns 422 status with validation errors                   |
| `test_token_revocation_on_login`                | Tests that previous tokens are revoked on new login         | Previous token is removed from database on new login        |
| `test_tenant_context_middleware`                | Tests tenant context middleware validation                  | Returns 403 status for missing or invalid tenant ID         |
| `test_admin_only_middleware_regular_user`       | Tests admin middleware blocks regular users                 | Returns 403 status for non-admin users                      |
| `test_admin_only_middleware_admin_user`         | Tests admin middleware allows admin users                   | Returns 200 status for admin users                          |
| `test_unauthenticated_access`                   | Tests access to protected routes without authentication     | Returns 401 status for unauthenticated requests             |
| `test_auth_middleware_with_expired_token`       | Tests auth middleware rejects invalid tokens                | Returns 401 status for invalid tokens                       |
| `test_auth_middleware_with_inactive_user_token` | Tests token rejection for users marked inactive after login | Returns 403 status for tokens from now-inactive users       |
| `test_admin_only_middleware_super_admin_user`   | Tests admin middleware with non-standard admin role         | Returns 403 status because role is not exactly 'admin'      |
| `test_middleware_across_different_tenants`      | Tests token validity across different tenants               | Returns 401 status when using token from a different tenant |

### User Management Tests

#### User Controller Tests (`tests/Feature/Web/UserControllerTest.php`)

| Test Case                                                         | Description                                                         | Assertions                                                        |
| ----------------------------------------------------------------- | ------------------------------------------------------------------- | ----------------------------------------------------------------- |
| `test_index_displays_all_users`                                   | Tests user list view                                                | Page loads with correct view and contains all users               |
| `test_create_displays_user_creation_form`                         | Tests user creation form view                                       | Page loads with correct view                                      |
| `test_store_creates_new_user`                                     | Tests user creation process                                         | User is created with correct attributes and welcome email is sent |
| `test_store_validates_required_fields`                            | Tests validation for required fields in user creation               | Appropriate validation errors are shown                           |
| `test_store_validates_unique_email_and_username`                  | Tests validation for duplicate email/username                       | Validation errors for duplicate fields                            |
| `test_main_admin_can_create_admin_user`                           | Tests that main admin can create other admin users                  | Admin user is created with admin role                             |
| `test_regular_admin_cannot_create_admin_user`                     | Tests that regular admins cannot create admin users                 | User is created but with role downgraded to 'user'                |
| `test_email_failure_doesnt_prevent_user_creation`                 | Tests user creation succeeds despite email failure                  | User is still created when email fails                            |
| `test_edit_displays_user_edit_form`                               | Tests user edit form view                                           | Page loads with correct view and user data                        |
| `test_edit_returns_error_for_nonexistent_user`                    | Tests edit view for non-existent user                               | Redirects with error message                                      |
| `test_main_admin_can_edit_any_user`                               | Tests main admin permission to edit any user                        | Main admin can edit both regular users and other admins           |
| `test_regular_admin_cannot_edit_other_admin`                      | Tests regular admin cannot edit other admins                        | Regular admin is denied access with error message                 |
| `test_regular_admin_can_edit_themselves`                          | Tests regular admin can edit their own profile                      | Regular admin can access their own edit form                      |
| `test_update_user_information`                                    | Tests user information update                                       | User information is updated correctly                             |
| `test_update_validates_required_fields`                           | Tests validation for user update                                    | Required fields are validated                                     |
| `test_update_validates_unique_email`                              | Tests unique email validation on update                             | Duplicate email is rejected                                       |
| `test_update_user_with_password`                                  | Tests updating user's password                                      | Password is updated and notification email is sent                |
| `test_update_password_validation`                                 | Tests password validation rules                                     | Password validation rules are enforced                            |
| `test_self_password_update_doesnt_include_password_in_email`      | Tests password not included in email when user updates own password | Email is sent without including the password                      |
| `test_email_failure_during_password_update_doesnt_prevent_update` | Tests update succeeds despite email failure                         | User is still updated when email fails                            |
| `test_cannot_update_main_admin_role`                              | Tests main admin role cannot be downgraded                          | Main admin role remains unchanged                                 |
| `test_update_returns_error_for_nonexistent_user`                  | Tests update for non-existent user                                  | Redirects with error message                                      |
| `test_regular_admin_cannot_upgrade_user_to_admin`                 | Tests regular admin cannot promote users to admin                   | User role remains unchanged                                       |
| `test_regular_admin_cannot_update_another_admin`                  | Tests regular admin cannot update other admins                      | Update is denied with error message                               |
| `test_delete_user`                                                | Tests user deletion                                                 | User is successfully deleted                                      |
| `test_delete_returns_error_for_nonexistent_user`                  | Tests deletion of non-existent user                                 | Redirects with error message                                      |
| `test_cannot_delete_main_admin`                                   | Tests main admin cannot be deleted                                  | Main admin deletion is prevented                                  |
| `test_regular_admin_cannot_delete_other_admin`                    | Tests regular admin cannot delete other admins                      | Deletion is denied with error message                             |
| `test_regular_admin_can_delete_regular_user`                      | Tests regular admin can delete regular users                        | Regular user is successfully deleted                              |

### Middleware Tests

#### API ActiveTenantMiddleware Tests (`tests/Feature/Middleware/Api/ActiveTenantMiddlewareTest.php`)

| Test Case                                       | Description                                                     | Assertions                                                           |
| ----------------------------------------------- | --------------------------------------------------------------- | -------------------------------------------------------------------- |
| `test_middleware_rejects_missing_tenant_header` | Tests that middleware rejects requests without tenant ID header | Returns 403 status with error message about missing tenant ID header |
| `test_middleware_rejects_inactive_tenant`       | Tests that middleware rejects inactive tenants                  | Returns 403 status with message about inactive tenant                |
| `test_middleware_allows_active_tenant`          | Tests that middleware allows requests with active tenant        | Request passes through middleware (results in 401 for login test)    |
| `test_middleware_rejects_nonexistent_tenant`    | Tests that middleware rejects non-existent tenant IDs           | Returns 403 status with error message about invalid tenant ID        |

#### ActiveTenantMiddleware Tests (`tests/Feature/Middleware/ActiveTenantMiddlewareTest.php`)

| Test Case                                      | Description                                              | Assertions                                                    |
| ---------------------------------------------- | -------------------------------------------------------- | ------------------------------------------------------------- |
| `test_middleware_redirects_missing_tenant_id`  | Tests redirection when tenant ID is missing from session | Returns 200 (login page) when no tenant ID in session         |
| `test_middleware_redirects_inactive_tenant`    | Tests redirection for inactive tenants                   | User is redirected when tenant is inactive                    |
| `test_middleware_allows_active_tenant`         | Tests that middleware allows active tenants              | Request passes through middleware with active tenant (200 OK) |
| `test_middleware_redirects_nonexistent_tenant` | Tests redirection for non-existent tenant IDs            | User is redirected when tenant ID doesn't exist               |

#### ActiveTenantExceptAdminMiddleware Tests (`tests/Feature/Middleware/ActiveTenantExceptAdminMiddlewareTest.php`)

| Test Case                                                          | Description                                                    | Assertions                                                     |
| ------------------------------------------------------------------ | -------------------------------------------------------------- | -------------------------------------------------------------- |
| `test_middleware_redirects_missing_tenant_id`                      | Tests redirection when tenant ID is missing from session       | User is redirected when no tenant ID is present                |
| `test_middleware_redirects_inactive_tenant_for_regular_user`       | Tests that regular users are redirected for inactive tenants   | Regular users are redirected when tenant is inactive           |
| `test_middleware_allows_admin_access_with_inactive_tenant`         | Tests admin bypass for inactive tenants                        | Main admin (username=tenant_id) bypasses inactive tenant check |
| `test_middleware_redirects_inactive_tenant_for_non_matching_admin` | Tests that non-main admins are redirected for inactive tenants | Non-main admins are redirected when tenant is inactive         |

#### MainAdminOnlyMiddleware Tests (`tests/Feature/Middleware/MainAdminOnlyMiddlewareTest.php`)

| Test Case                                  | Description                                       | Assertions                                                    |
| ------------------------------------------ | ------------------------------------------------- | ------------------------------------------------------------- |
| `test_middleware_allows_main_admin_access` | Tests that main admin can access protected routes | Main admin (username=tenant_id) can access protected routes   |
| `test_middleware_redirects_non_main_admin` | Tests that non-main admins are redirected         | Non-main admins are redirected with appropriate error message |

#### AdminOnlyMiddleware Tests (`tests/Feature/Middleware/AdminOnlyMiddlewareTest.php`)

| Test Case                                       | Description                                              | Assertions                                                       |
| ----------------------------------------------- | -------------------------------------------------------- | ---------------------------------------------------------------- |
| `admin_can_access_admin_routes`                 | Tests that admin users can access admin-only routes      | Admin users can access admin routes successfully                 |
| `regular_users_cannot_access_admin_routes`      | Tests that regular users cannot access admin-only routes | Regular users are redirected with appropriate error message      |
| `unauthenticated_users_can_access_admin_routes` | Tests how middleware handles unauthenticated users       | Middleware allows unauthenticated users to proceed to auth check |

### Controller Tests

#### Tenant Controller Tests (`tests/Feature/Web/TenantControllerTest.php`)

| Test Case                                              | Description                                           | Assertions                                               |
| ------------------------------------------------------ | ----------------------------------------------------- | -------------------------------------------------------- |
| `test_tenant_settings_page_loads`                      | Tests tenant settings page loads correctly            | Page loads with status 200 and includes tenant data      |
| `test_tenant_activation`                               | Tests tenant activation process                       | Tenant is successfully activated with success message    |
| `test_tenant_deactivation`                             | Tests tenant deactivation process                     | Tenant is successfully deactivated with success message  |
| `test_tenant_settings_not_accessible_to_regular_admin` | Tests that only main admin can access tenant settings | Regular admins are redirected with access denied message |

### API Controller Tests

#### Task Controller Tests (`tests/Feature/Api/TaskControllerTest.php`)

| Test Case                                                 | Description                                                 | Assertions                                                               |
| --------------------------------------------------------- | ----------------------------------------------------------- | ------------------------------------------------------------------------ |
| `users_can_get_all_tasks`                                 | Tests that authenticated users can retrieve a list of tasks | Returns 200 status with a list of tasks                                  |
| `admin_can_create_task`                                   | Tests that admin users can create a new task                | Returns 201 status, task created in DB, correct task data in response    |
| `regular_users_cannot_create_task`                        | Tests that regular users are forbidden from creating tasks  | Returns 403 status                                                       |
| `users_can_update_task`                                   | Tests that authenticated users can update an existing task  | Returns 200 status, task updated in DB, correct updated data in response |
| `admin_can_delete_task`                                   | Tests that admin users can delete a task                    | Returns 200 status, task removed from DB                                 |
| `regular_users_cannot_delete_task`                        | Tests that regular users are forbidden from deleting tasks  | Returns 403 status                                                       |
| `validation_error_when_creating_task_with_missing_fields` | Tests validation rules when creating a task                 | Returns 422 status with validation errors for required fields            |
| `unauthenticated_users_cannot_access_task_endpoints`      | Tests that unauthenticated users cannot access task routes  | Returns 401 status for GET, POST, PUT, DELETE requests                   |
| `users_can_get_single_task`                               | Tests that authenticated users can retrieve a specific task | Returns 200 status with the correct task data                            |
| `users_can_complete_tasks`                                | Tests updating a task's status to 'completed'               | Returns 200 status, status is 'completed', completedAt is set            |

#### Milestone Controller Tests (`tests/Feature/Api/MilestoneControllerTest.php`)

| Test Case                                                      | Description                                                        | Assertions                                                                    |
| -------------------------------------------------------------- | ------------------------------------------------------------------ | ----------------------------------------------------------------------------- |
| `admin_can_get_all_milestones`                                 | Tests that admin users can retrieve a list of milestones           | Returns 200 status with a list of milestones                                  |
| `regular_users_cannot_get_milestones`                          | Tests that regular users are forbidden from retrieving milestones  | Returns 403 status                                                            |
| `admin_can_create_milestone`                                   | Tests that admin users can create a new milestone                  | Returns 201 status, milestone created in DB, correct data in response         |
| `regular_users_cannot_create_milestone`                        | Tests that regular users are forbidden from creating milestones    | Returns 403 status                                                            |
| `admin_can_update_milestone`                                   | Tests that admin users can update an existing milestone            | Returns 200 status, milestone updated in DB, correct updated data in response |
| `regular_users_cannot_update_milestone`                        | Tests that regular users are forbidden from updating milestones    | Returns 403 status                                                            |
| `admin_can_delete_milestone`                                   | Tests that admin users can delete a milestone                      | Returns 200 status, milestone removed from DB                                 |
| `regular_users_cannot_delete_milestone`                        | Tests that regular users are forbidden from deleting milestones    | Returns 403 status                                                            |
| `validation_error_when_creating_milestone_with_missing_fields` | Tests validation rules when creating a milestone                   | Returns 422 status with validation errors for required fields                 |
| `unauthenticated_users_cannot_access_milestone_endpoints`      | Tests that unauthenticated users cannot access milestone routes    | Returns 401 status for GET, POST, PUT, DELETE requests                        |
| `admin_can_get_single_milestone`                               | Tests that admin users can retrieve a specific milestone           | Returns 200 status with the correct milestone data                            |
| `regular_users_cannot_get_single_milestone`                    | Tests that regular users are forbidden from retrieving a milestone | Returns 403 status                                                            |
| `admin_can_get_favorite_milestones`                            | Tests that admin users can filter milestones by 'favorite' status  | Returns 200 status with a list of favorite milestones (or empty list)         |
| `regular_users_cannot_get_favorite_milestones`                 | Tests that regular users are forbidden from filtering milestones   | Returns 403 status                                                            |

#### FCM Controller Tests (`tests/Feature/Api/FcmControllerTest.php`)

| Test Case                                   | Description                                              | Assertions                                                   |
| ------------------------------------------- | -------------------------------------------------------- | ------------------------------------------------------------ |
| `update_device_token`                       | Tests updating FCM device token for a user               | Returns 200 status, token is updated in database             |
| `update_device_token_validation`            | Tests validation of FCM token when updating              | Returns 422 status with validation errors when token missing |
| `unauthenticated_users_cannot_update_token` | Tests that only authenticated users can update FCM token | Returns 401 status for unauthenticated access                |

### Page Load Tests (Pest) (`tests/Feature/PageLoadTest.php`)

| Test Case                              | Description                                | Assertions                                  |
| -------------------------------------- | ------------------------------------------ | ------------------------------------------- |
| `loads the home page`                  | Tests the home/landing page loads          | Page loads with status 200 and correct view |
| `loads the privacy page`               | Tests the privacy policy page loads        | Page loads with status 200 and correct view |
| `loads the login page`                 | Tests the login page loads                 | Page loads with status 200 and correct view |
| `loads the register page`              | Tests the registration page loads          | Page loads with status 200 and correct view |
| `loads the tenant inactive error page` | Tests the tenant inactive error page loads | Page loads with status 200 and correct view |
| `loads the tenant required error page` | Tests the tenant required error page loads | Page loads with status 200 and correct view |

### Task History Controller Tests (`tests/Feature/Web/TaskHistoryControllerTest.php`)

| Test Case                                       | Description                                        | Assertions                                      |
| ----------------------------------------------- | -------------------------------------------------- | ----------------------------------------------- |
| `test_history_displays_tasks`                   | Verifies the history page displays a list of tasks | Page loads, correct view, tasks present         |
| `test_history_filters_by_status`                | Filters tasks by status (e.g., completed)          | All returned tasks have the requested status    |
| `test_history_filters_by_priority`              | Filters tasks by priority (e.g., high)             | All returned tasks have the requested priority  |
| `test_history_searches_by_name_and_description` | Searches tasks by name or description              | Returned tasks match the search query           |
| `test_history_filters_by_date_today`            | Filters tasks created today                        | All returned tasks have today's date            |
| `test_history_filters_by_date_week`             | Filters tasks created this week                    | All returned tasks are within the current week  |
| `test_history_filters_by_date_month`            | Filters tasks created this month                   | All returned tasks are within the current month |
| `test_history_pagination`                       | Ensures pagination works (max 10 per page)         | Returned tasks count is <= 10                   |

### Dashboard Controller Tests (`tests/Feature/Web/DashboardControllerTest.php`)

| Test Case                                 | Description                                      | Assertions                                 |
| ----------------------------------------- | ------------------------------------------------ | ------------------------------------------ |
| `test_dashboard_displays_task_statistics` | Verifies dashboard shows correct task statistics | View loads, statistics match created tasks |

### Badge System Tests

The Badge System tests comprehensively cover the gamification features of the application, including badge awarding, user statistics tracking, leaderboards, and API endpoints.

#### API Badge Controller Tests (`tests/Feature/Api/BadgeControllerTest.php`)

| Test Case                              | Description                                                | Assertions                                                               |
| -------------------------------------- | ---------------------------------------------------------- | ------------------------------------------------------------------------ |
| `can_get_all_badges_with_progress`     | Tests retrieving all badges with user progress             | Returns 200, only active badges shown, correct structure with progress   |
| `can_filter_badges_by_category`        | Tests filtering badges by category (e.g., task_completion) | Returns 200, all badges match requested category                         |
| `can_filter_badges_by_earned_status`   | Tests filtering badges by earned status (true/false)       | Returns 200, earned filter correctly separates earned vs unearned badges |
| `can_get_earned_badges`                | Tests retrieving badges earned by authenticated user       | Returns 200, correct earned badges structure, ordered by latest first    |
| `can_filter_earned_badges_by_category` | Tests filtering earned badges by category                  | Returns 200, only earned badges in specified category                    |
| `can_limit_earned_badges`              | Tests limiting number of earned badges returned            | Returns 200, respects limit parameter, correct count                     |
| `can_get_recent_badges`                | Tests retrieving badges earned within last 7 days          | Returns 200, only badges earned within time window                       |
| `can_get_user_stats`                   | Tests retrieving authenticated user's statistics           | Returns 200, complete user stats structure (level, points, tasks, etc.)  |
| `returns_null_when_user_has_no_stats`  | Tests API response when user has no statistics             | Returns 200 with null data and appropriate message                       |
| `can_get_badge_categories`             | Tests retrieving available badge categories                | Returns 200, categories with names and badge counts                      |
| `can_get_leaderboard_all_time`         | Tests retrieving all-time leaderboard                      | Returns 200, ranked users with stats, correct leaderboard structure      |
| `can_get_leaderboard_monthly`          | Tests retrieving monthly leaderboard                       | Returns 200, filtered by month period                                    |
| `can_get_leaderboard_weekly`           | Tests retrieving weekly leaderboard                        | Returns 200, filtered by week period                                     |
| `can_limit_leaderboard_results`        | Tests limiting leaderboard results                         | Returns 200, respects limit parameter                                    |
| `can_show_specific_badge`              | Tests retrieving details for a specific badge              | Returns 200, badge details with earned status and statistics             |
| `shows_earned_badge_details`           | Tests showing badge details when user has earned it        | Returns 200, includes earned status and progress information             |
| `returns_404_for_nonexistent_badge`    | Tests requesting non-existent badge                        | Returns 404 status                                                       |
| `requires_authentication`              | Tests that badge endpoints require authentication          | Returns 401 for unauthenticated requests                                 |

#### API Badge System Integration Tests (`tests/Feature/Api/BadgeSystemTest.php`)

| Test Case                                       | Description                                            | Assertions                                                               |
| ----------------------------------------------- | ------------------------------------------------------ | ------------------------------------------------------------------------ |
| `task_completion_fires_event_and_creates_stats` | Tests task completion event triggers stats creation    | Task completed, user stats created, event listener executed              |
| `user_stats_are_created_when_completing_task`   | Tests user stats creation on task completion           | Task completed, stats created with correct values, includes badge points |
| `badge_is_awarded_when_criteria_met`            | Tests automatic badge awarding when criteria satisfied | Badge awarded automatically, user has badge in collection                |
| `can_get_user_badges_via_api`                   | Tests retrieving user badges through API               | Returns 200, correct badge structure with earned information             |
| `can_get_user_stats_via_api`                    | Tests retrieving user statistics through API           | Returns 200, complete stats including level, points, tasks, streaks      |
| `can_get_all_badges_with_progress`              | Tests retrieving all badges with user progress info    | Returns 200, badges with progress indicators, earned status              |
| `can_get_leaderboard`                           | Tests leaderboard functionality with multiple users    | Returns 200, properly ranked users, correct statistics                   |

#### Web Badge Controller Tests (`tests/Feature/Web/BadgeControllerTest.php`)

| Test Case                                                 | Description                                           | Assertions                                             |
| --------------------------------------------------------- | ----------------------------------------------------- | ------------------------------------------------------ |
| `index_displays_badges_dashboard_for_authenticated_user`  | Tests badge dashboard loads for regular users         | Page loads with correct view and all required data     |
| `index_displays_badges_dashboard_for_authenticated_admin` | Tests badge dashboard loads for admin users           | Page loads with correct view for admin perspective     |
| `index_redirects_unauthenticated_users`                   | Tests unauthenticated access redirects to login       | Redirects to login page                                |
| `index_filters_badges_by_category`                        | Tests category filtering on badge index               | Filters applied correctly, view has category parameter |
| `index_filters_badges_by_rarity`                          | Tests rarity filtering on badge index                 | Filters applied correctly, view has rarity parameter   |
| `index_searches_badges_by_name`                           | Tests search functionality on badge index             | Search applied correctly, view has search parameter    |
| `index_applies_multiple_filters`                          | Tests combining multiple filters on badge index       | All filters applied simultaneously                     |
| `show_displays_badge_details_for_authenticated_user`      | Tests individual badge detail page for users          | Page loads with badge details and related statistics   |
| `show_displays_badge_details_for_authenticated_admin`     | Tests individual badge detail page for admins         | Page loads with full admin badge details               |
| `show_redirects_unauthenticated_users`                    | Tests unauthenticated access to badge details         | Redirects to login                                     |
| `show_returns_404_for_nonexistent_badge`                  | Tests accessing non-existent badge                    | Returns 404 status                                     |
| `user_progress_displays_user_progression_dashboard`       | Tests user progression dashboard                      | Page loads with user stats and progression data        |
| `user_progress_redirects_unauthenticated_users`           | Tests unauthenticated access to user progress         | Redirects to login                                     |
| `user_progress_searches_users_by_name`                    | Tests searching users by name in progression view     | Search parameter applied correctly                     |
| `user_progress_searches_users_by_email`                   | Tests searching users by email in progression view    | Search parameter applied correctly                     |
| `user_progress_searches_users_by_username`                | Tests searching users by username in progression view | Search parameter applied correctly                     |
| `user_progress_sorts_by_badges_count`                     | Tests sorting user progression by badge count         | Sort parameter applied correctly                       |
| `user_progress_sorts_by_points`                           | Tests sorting user progression by points              | Sort parameter applied correctly                       |
| `user_progress_sorts_by_level`                            | Tests sorting user progression by level               | Sort parameter applied correctly                       |
| `user_progress_sorts_by_tasks`                            | Tests sorting user progression by task count          | Sort parameter applied correctly                       |
| `user_progress_defaults_to_badges_count_sorting`          | Tests default sorting for user progression            | Default sort by badges count applied                   |
| `leaderboard_displays_leaderboard_dashboard`              | Tests leaderboard dashboard                           | Page loads with leaderboard data and filters           |
| `leaderboard_redirects_unauthenticated_users`             | Tests unauthenticated access to leaderboard           | Redirects to login                                     |
| `leaderboard_filters_by_period_all`                       | Tests filtering leaderboard by all-time period        | Period filter applied correctly                        |
| `leaderboard_filters_by_period_month`                     | Tests filtering leaderboard by monthly period         | Period filter applied correctly                        |
| `leaderboard_filters_by_period_week`                      | Tests filtering leaderboard by weekly period          | Period filter applied correctly                        |
| `leaderboard_filters_by_type_points`                      | Tests filtering leaderboard by points type            | Type filter applied correctly                          |
| `leaderboard_filters_by_type_badges`                      | Tests filtering leaderboard by badges type            | Type filter applied correctly                          |
| `leaderboard_filters_by_type_tasks`                       | Tests filtering leaderboard by tasks type             | Type filter applied correctly                          |
| `leaderboard_defaults_to_all_period_and_points_type`      | Tests default leaderboard filters                     | Default period (all) and type (points) applied         |
| `leaderboard_combines_period_and_type_filters`            | Tests combining period and type filters               | Both filters applied simultaneously                    |

**Badge System Features Tested:**

1. **Badge Management**: Creation, filtering, categorization, and rarity levels
2. **User Statistics**: Points, levels, experience, streaks, completion rates
3. **Badge Awarding**: Automatic awarding based on criteria, progress tracking
4. **Leaderboards**: All-time, monthly, weekly rankings by points/badges/tasks
5. **API Endpoints**: Complete REST API for mobile app integration
6. **Web Interface**: Dashboard views for users and admins
7. **Authentication**: Proper access control and tenant isolation
8. **Real-time Updates**: Event-driven badge awarding on task completion
9. **Performance Tracking**: User progression analytics and statistics
10. **Search & Filtering**: Advanced filtering and search capabilities

**Test Coverage Summary:**

-   **18 API test methods** covering all badge endpoints and functionality
-   **7 integration tests** verifying the complete badge awarding workflow
-   **27 web interface tests** ensuring proper UI behavior and access control
-   **Total: 52 comprehensive badge system tests**

The Badge System tests ensure complete coverage of the gamification features, from basic CRUD operations to complex business logic like automatic badge awarding, leaderboard calculations, and user progression tracking. All tests use proper tenant isolation and authentication verification.

## Unit Tests

### Contact Form Tests

#### Contact Form Test (`tests/Unit/ContactFormTest.php`)

| Test Case                                        | Description                                 | Assertions                                     |
| ------------------------------------------------ | ------------------------------------------- | ---------------------------------------------- |
| `test_contact_form_submits_successfully`         | Tests successful contact form submission    | Form submits successfully and email is sent    |
| `test_contact_form_validates_required_fields`    | Tests validation for empty form submission  | Required fields are validated                  |
| `test_contact_form_validates_email_format`       | Tests email format validation               | Invalid email format is rejected               |
| `test_contact_form_validates_message_length`     | Tests message length validation             | Short messages are rejected                    |
| `test_contact_form_handles_mail_exceptions`      | Tests form handling when mail sending fails | Form handles mail exceptions gracefully (500)  |
| `test_contact_form_validates_min_name_length`    | Tests minimum name length validation        | Names that are too short are rejected          |
| `test_contact_form_validates_max_name_length`    | Tests maximum name length validation        | Names that are too long are rejected           |
| `test_contact_form_validates_max_email_length`   | Tests maximum email length validation       | Emails that are too long are rejected          |
| `test_contact_form_validates_max_phone_length`   | Tests maximum phone length validation       | Phone numbers that are too long are rejected   |
| `test_contact_form_allows_null_phone`            | Tests that phone field is optional          | Form submits successfully without phone number |
| `test_contact_form_validates_max_message_length` | Tests maximum message length validation     | Messages that are too long are rejected        |

### Task Policy Tests (`tests/Unit/TaskPolicyTest.php`)

| Test Case                            | Description                                        | Assertions                                         |
| ------------------------------------ | -------------------------------------------------- | -------------------------------------------------- |
| `any_user_can_view_any_tasks`        | Tests that any user can view task listings         | Both admin and regular users can view tasks        |
| `any_user_can_view_task`             | Tests that any user can view a specific task       | Any user can view any task regardless of ownership |
| `only_admin_can_create_task`         | Tests that only admin users can create tasks       | Admin can create tasks, regular users cannot       |
| `admin_can_update_any_task`          | Tests that admin users can update any task         | Admin users can update any task                    |
| `regular_users_can_update_own_tasks` | Tests that regular users can only update own tasks | User can update own tasks but not others' tasks    |
| `only_admin_can_delete_task`         | Tests that only admin users can delete tasks       | Admin can delete tasks, regular users cannot       |

### FCM Service Tests (`tests/Unit/FcmServiceTest.php`)

| Test Case                                  | Description                                           | Assertions                                      |
| ------------------------------------------ | ----------------------------------------------------- | ----------------------------------------------- |
| `sending_notification_fails_without_token` | Tests FCM notification failure when user has no token | Method returns false when user has no FCM token |
| `successful_notification_returns_true`     | Tests successful FCM notification                     | Method returns true for successful notification |

### Tenant Service Tests (`tests/Unit/TenantServiceTest.php`)

| Test Case                       | Description                              | Assertions                                         |
| ------------------------------- | ---------------------------------------- | -------------------------------------------------- |
| `get_tenant_by_id`              | Tests retrieving a tenant by ID          | Correct tenant is retrieved with proper attributes |
| `get_nonexistent_tenant`        | Tests retrieving a non-existent tenant   | Null is returned for non-existent tenant ID        |
| `activate_tenant`               | Tests activating an inactive tenant      | Tenant status changes to 'active'                  |
| `activate_nonexistent_tenant`   | Tests activating a non-existent tenant   | Null is returned for non-existent tenant ID        |
| `deactivate_tenant`             | Tests deactivating an active tenant      | Tenant status changes to 'inactive'                |
| `deactivate_nonexistent_tenant` | Tests deactivating a non-existent tenant | Null is returned for non-existent tenant ID        |

### Statistics Service Unit Tests (`tests/Unit/Services/StatisticsServiceTest.php`)

| Test Case                                           | Description                                      | Assertions                                                   |
| --------------------------------------------------- | ------------------------------------------------ | ------------------------------------------------------------ |
| `test_set_date_range`                               | Tests setting a date range for statistics        | Returns chainable instance, filter info is correct           |
| `test_get_task_stats_with_empty_data`               | Tests task stats with no tasks                   | All stats are zero                                           |
| `test_get_task_stats_with_data`                     | Tests task stats with various statuses           | Stats match created tasks, completion rate is correct        |
| `test_get_task_time_stats`                          | Tests task time statistics (mocked)              | Returns expected time stats                                  |
| `test_get_current_filter_info_with_no_filters`      | Tests filter info with no date range             | is_filtered is false, description is "Toutes les périodes"   |
| `test_get_current_filter_info_with_start_date_only` | Tests filter info with only start date           | is_filtered is true, description matches start date          |
| `test_get_current_filter_info_with_end_date_only`   | Tests filter info with only end date             | is_filtered is true, description matches end date            |
| `test_get_current_filter_info_with_same_day`        | Tests filter info with same start and end date   | is_filtered is true, description matches single day          |
| `test_get_tasks_by_user_paginated`                  | Tests paginated user task stats                  | Returns correct stats for assigned user                      |
| `test_get_all_stats`                                | Tests getting all statistics at once             | Structure contains all expected keys                         |
| `test_get_tasks_by_priority`                        | Tests stats by task priority                     | Counts by priority match created tasks                       |
| `test_get_milestone_stats`                          | Tests milestone statistics                       | Stats for milestones, favorites, most used, etc. are correct |
| `test_get_tasks_by_month`                           | Tests monthly task stats                         | Returns array of 12 months, each with created/completed keys |
| `test_get_user_stats_top_5`                         | Tests top 5 users by task stats                  | Returns up to 5 users, ordered by total tasks                |
| `test_date_range_filters_are_applied`               | Tests that date range filters affect filter info | Filter info changes according to set date range              |

## Browser Tests

_(Note: The `PageLoadTest.php` file is currently located in `tests/Feature` and uses Pest syntax. It is documented under Feature Tests.)_

<!-- Example structure if Browser tests were added later -->
<!--
### Example Browser Test (`tests/Browser/ExampleTest.php`)

| Test Case                 | Description                         | Assertions                                  |
| ------------------------- | ----------------------------------- | ------------------------------------------- |
| `example test`            | Example description                 | Example assertion                           |
-->

---

## Testing Utilities

### Database Refresh (`tests/Utilities/DatabaseRefresh.php`)

A utility trait used to clean tenant databases between tests. It:

-   Finds all tenant database files based on configured prefix
-   Deletes these files to ensure a clean slate for each test

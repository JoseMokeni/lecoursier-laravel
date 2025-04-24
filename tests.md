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
-   Unit Tests
    -   Contact Form Tests
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

### Page Load Tests (Pest) (`tests/Feature/PageLoadTest.php`)

| Test Case                              | Description                                | Assertions                                  |
| -------------------------------------- | ------------------------------------------ | ------------------------------------------- |
| `loads the home page`                  | Tests the home/landing page loads          | Page loads with status 200 and correct view |
| `loads the privacy page`               | Tests the privacy policy page loads        | Page loads with status 200 and correct view |
| `loads the login page`                 | Tests the login page loads                 | Page loads with status 200 and correct view |
| `loads the register page`              | Tests the registration page loads          | Page loads with status 200 and correct view |
| `loads the tenant inactive error page` | Tests the tenant inactive error page loads | Page loads with status 200 and correct view |
| `loads the tenant required error page` | Tests the tenant required error page loads | Page loads with status 200 and correct view |

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

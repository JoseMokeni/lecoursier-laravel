Collecting workspace information# Le Coursier Application Test Documentation

This document provides a comprehensive overview of the test suite for the Le Coursier Laravel application. The tests are organized by type and functionality, covering authentication, user management, contact forms, and basic page loading.

## Table of Contents

-   Overview
-   Feature Tests
    -   Authentication Tests
    -   User Management Tests
-   Unit Tests
    -   Contact Form Tests
-   Browser Tests

## Overview

The application follows a multi-tenancy architecture using the `stancl/tenancy` package. Most tests are built using PHPUnit with some newer tests using Pest PHP syntax. The test suite is divided into:

-   **Feature Tests**: Integration tests for authentication flows and user management
-   **Unit Tests**: Focused tests for specific components like the contact form
-   **Browser Tests**: Basic page loading tests

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

#### API Authentication Tests (`tests/Feature/Api/Auth/ApiLoginTest.php`)

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

## Unit Tests

### Contact Form Tests

#### Contact Form Test (`tests/Unit/ContactFormTest.php`)

| Test Case                                        | Description                                 | Assertions                                     |
| ------------------------------------------------ | ------------------------------------------- | ---------------------------------------------- |
| `test_contact_form_submits_successfully`         | Tests successful contact form submission    | Form submits successfully and email is sent    |
| `test_contact_form_validates_required_fields`    | Tests validation for empty form submission  | Required fields are validated                  |
| `test_contact_form_validates_email_format`       | Tests email format validation               | Invalid email format is rejected               |
| `test_contact_form_validates_message_length`     | Tests message length validation             | Short messages are rejected                    |
| `test_contact_form_handles_mail_exceptions`      | Tests form handling when mail sending fails | Form handles mail exceptions gracefully        |
| `test_contact_form_validates_min_name_length`    | Tests minimum name length validation        | Names that are too short are rejected          |
| `test_contact_form_validates_max_name_length`    | Tests maximum name length validation        | Names that are too long are rejected           |
| `test_contact_form_validates_max_email_length`   | Tests maximum email length validation       | Emails that are too long are rejected          |
| `test_contact_form_validates_max_phone_length`   | Tests maximum phone length validation       | Phone numbers that are too long are rejected   |
| `test_contact_form_allows_null_phone`            | Tests that phone field is optional          | Form submits successfully without phone number |
| `test_contact_form_validates_max_message_length` | Tests maximum message length validation     | Messages that are too long are rejected        |

## Browser Tests

### Page Load Tests (`tests/Browser/PageLoadTest.php`)

| Test Case                 | Description                         | Assertions                                  |
| ------------------------- | ----------------------------------- | ------------------------------------------- |
| `loads the home page`     | Tests the home/landing page loads   | Page loads with status 200 and correct view |
| `loads the privacy page`  | Tests the privacy policy page loads | Page loads with status 200 and correct view |
| `loads the login page`    | Tests the login page loads          | Page loads with status 200 and correct view |
| `loads the register page` | Tests the registration page loads   | Page loads with status 200 and correct view |

---

## Testing Utilities

### Database Refresh (`tests/Utilities/DatabaseRefresh.php`)

A utility trait used to clean tenant databases between tests. It:

-   Finds all tenant database files based on configured prefix
-   Deletes these files to ensure a clean slate for each test

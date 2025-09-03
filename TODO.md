# Project TODO

This document outlines the current progress and remaining tasks for the "Skilled Account" feature.

## Completed Tasks:

-   **Landing Page Modification:**
    -   Added a "Skilled Account" link to `/workspaces/agency/app/views/controllers/_/landing.php`.
-   **Skilled Account Controller (`Skilled.php`):**
    -   Created the controller at `/workspaces/agency/app/views/controllers/_/Skilled.php`.
    -   Implemented `index()` method to load the login/registration view.
    -   Implemented `register()` method to handle user registration (adapted from `skilled/register.php`).
    -   Implemented `login()` method to handle user login (adapted from `skilled/login_process.php`).
    -   Implemented `profile()` method to display the user's profile.
    -   Implemented `logout()` method to destroy the user session.
    -   Loaded necessary models (`m_applicant`, `m_country`, `m_position`, `cyd_currency`, `Custom_Fields`, `Cyd_Applicants_Alphatomo`) in the constructor.
-   **Skilled Account Views:**
    -   Created `skilled/index.php` (at `/workspaces/agency/app/views/skilled/index.php`) with combined login and registration forms.
    -   Created `skilled/profile.php` (at `/workspaces/agency/app/views/skilled/profile.php`) by copying content from `app/views/admin/applicants/review.php`.
    -   **Cleaned up `skilled/profile.php`:**
        -   Removed the top right stats section (agent and employer information).
        -   Removed the "Delete applicant record" and "Back to Available" buttons.
        -   Removed all `show_customField()` function calls.
        -   Removed the "Requirements", "Processing", and "Softcopy Documents" tab links.
        -   Removed the content divs for "Certifications", "Requirements", and "Softcopy Documents".
        -   Removed all admin-specific PHP session checks and `$_SESSION` variables.
        -   Removed all admin-specific links and actions (e.g., "Visible to employer" checkboxes).
        -   Removed admin-only custom fields from the "Work Experiences" section.
-   **Skilled Applicant Model (`m_skilled_applicant.php`):**
    -   Created a new model for skilled applicants at `/workspaces/agency/app/models/m_skilled_applicant.php` by copying `m_applicant.php`.
-   **Refactor Skilled Applicant Model (`m_skilled_applicant.php`):**
    -   Renamed the class from `m_applicant` to `m_skilled_applicant`.
    -   Modified the `updateApplicantProfile()` method to be suitable for skilled users:
        -   Replaced all instances of `$_SESSION['admin']['user']['user_id']` with `$_SESSION['user_id']`.
        -   Removed the line that updates `applicant_source`, as this is an admin-only field.
        -   Removed logic related to extra experience fields that are not present in the user-facing form.
-   **Update Skilled Controller (`Skilled.php`):**
    -   Loaded the new `m_skilled_applicant` model.
    -   Used `m_skilled_applicant` instead of `m_applicant` in the `update_profile()` method.

## Remaining Tasks:

-   **Testing and Verification:**
    -   Thoroughly test the entire user flow:
        -   Access the landing page and navigate to "Skilled Account".
        -   Register a new skilled worker account.
        -   Log in with the newly created account.
        -   Verify that the profile page displays correctly with the registered user's data.
        -   Attempt to edit and update various sections of the profile (basic info, work experience, etc.).
        -   Verify that changes are saved correctly in the database.
        -   Test the logout functionality.
    -   Check for any PHP errors or warnings in the application logs during testing.
    -   Ensure proper styling and responsiveness of the new pages.

-   **Security Review (Post-Development):**
    -   Review all new and modified code for potential security vulnerabilities (e.g., SQL injection, cross-site scripting, improper session handling).
-   **Password Hashing:**
    -   Implemented password hashing for user registration and login.

## Notes:

-   The project has an unconventional file structure, with controllers located in the `app/views/controllers/_/` directory. This has been accommodated in the current implementation.
-   The `m_applicant` model directly accesses `$_POST` for updates, which is not ideal for maintainability and security but has been followed to maintain consistency with existing code.
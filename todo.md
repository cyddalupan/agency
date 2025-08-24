# Refactoring `update_profile.php` and `update_handlers`

This document outlines the steps to refactor the profile update functionality for improved modularity and testability.

## To-Do List

### Phase 1: Setup and Core Utilities

1.  **Create `src/` directory:**
    *   **Action:** Create a new directory named `src` at the project root (`/var/www/helloworld/src`). This will house our new modular PHP files.
    *   **Purpose:** To organize the new, refactored code and separate it from the existing legacy structure.

2.  **Create `src/Database.php`:**
    *   **Action:** Create a file `src/Database.php` inside the `src/` directory.
    *   **Content:** This file will contain a class (e.g., `DatabaseConnection`) with a static method (e.g., `getConnection()`) that returns a PDO database connection instance. This method should use the existing `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` constants from `config.php`.
    *   **Purpose:** To centralize and standardize the database connection logic, making it reusable and easier to manage.

3.  **Create `src/ProfileUpdater.php`:**
    *   **Action:** Create a file `src/ProfileUpdater.php` inside the `src/` directory.
    *   **Content:** This file will contain a class `ProfileUpdater` with the following static methods:
        *   `updateApplicant(PDO $pdo, int $userId, array $data): bool`: Responsible for constructing and executing an `UPDATE` query for the `applicant` table based on the provided `$data` array.
        *   `updateApplicantCertificate(PDO $pdo, int $userId, array $data): bool`: Handles updates for the `applicant_certificate` table. It should check if a record for the given `$userId` exists. If it does, perform an `UPDATE`; otherwise, perform an `INSERT`.
        *   `updateApplicantEducation(PDO $pdo, int $userId, array $data): bool`: Similar to `updateApplicantCertificate`, handles `UPDATE` or `INSERT` for the `applicant_education` table.
    *   **Purpose:** To encapsulate the generic database update/insert logic for the main applicant-related tables, making it reusable and testable.

### Phase 2: Refactoring Update Handlers

4.  **Create `src/handlers/` directory:**
    *   **Action:** Create a new directory named `handlers` inside the `src/` directory (`/var/www/helloworld/src/handlers`).
    *   **Purpose:** To store the refactored handler functions, each responsible for processing specific form data.

5.  **Refactor `skilled/update_handlers/personal_info.php`:**
    *   **Action:** Move and rename this file to `src/handlers/PersonalInfoHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `getPersonalInfoUpdateData(array $postData): array`). This function will take the raw `$_POST` data (or a relevant subset) as an argument and return an associative array where keys are database column names for the `applicant` table and values are the corresponding data. Remove any `require_once` statements and database interaction logic.
    *   **Purpose:** To isolate the data extraction and mapping logic for personal information, making it independently testable.

6.  **Refactor `skilled/update_handlers/family_background.php`:**
    *   **Action:** Move and rename this file to `src/handlers/FamilyBackgroundHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `getFamilyBackgroundUpdateData(array $postData): array`). Similar to `PersonalInfoHandler`, it will process POST data and return an array of fields for the `applicant` table.
    *   **Purpose:** To isolate family background data processing.

7.  **Refactor `skilled/update_handlers/job_preferences.php`:**
    *   **Action:** Move and rename this file to `src/handlers/JobPreferencesHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `getJobPreferencesUpdateData(array $postData): array`). It will process POST data and return an array of fields for the `applicant` table.
    *   **Purpose:** To isolate job preferences data processing.

8.  **Refactor `skilled/update_handlers/health.php`:**
    *   **Action:** Move and rename this file to `src/handlers/HealthHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `getHealthUpdateData(array $postData): array`). It will process POST data and return an array of fields for the `applicant` table.
    *   **Purpose:** To isolate health information data processing.

9.  **Refactor `skilled/update_handlers/documents.php`:**
    *   **Action:** Move and rename this file to `src/handlers/DocumentsHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `getDocumentsUpdateData(array $postData, ?string $resumePath = null): array`). This function will take POST data and an optional `$resumePath` (for uploaded resumes) and return an array of fields for the `applicant` table.
    *   **Purpose:** To isolate document-related data processing, including handling the resume path.

10. **Refactor `skilled/update_handlers/certificates.php`:**
    *   **Action:** Move and rename this file to `src/handlers/CertificatesHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `getCertificatesUpdateData(array $postData): array`). This function will process POST data and return an array of fields for the `applicant_certificate` table. Remove all database interaction logic.
    *   **Purpose:** To isolate certificate data processing, preparing it for the `ProfileUpdater`.

11. **Refactor `skilled/update_handlers/education.php`:**
    *   **Action:** Move and rename this file to `src/handlers/EducationHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `getEducationUpdateData(array $postData): array`). This function will process POST data and return an array of fields for the `applicant_education` table. Remove all database interaction logic.
    *   **Purpose:** To isolate education data processing, preparing it for the `ProfileUpdater`.

12. **Refactor `skilled/update_handlers/work_experience.php`:**
    *   **Action:** Move and rename this file to `src/handlers/WorkExperienceHandler.php`.
    *   **Content:** Convert its content into a standalone function (e.g., `handleWorkExperienceUpdate(PDO $pdo, int $userId, array $experienceData): bool`). This function will directly handle the deletion of existing work experiences for the user and then re-insert the new/updated work experiences from the `$experienceData` array into the `applicant_experiences` table. This handler is an exception as its logic is inherently tied to managing a collection of related records.
    *   **Purpose:** To manage the complex, multi-record update logic for work experiences in an isolated manner.

### Phase 3: Updating the Main Update Script

13. **Update `skilled/update_profile.php`:**
    *   **Action:** Modify the existing `skilled/update_profile.php` file.
    *   **Content Changes:**
        *   Remove the dynamic variable creation using `foreach ($_POST as $key => $value) { $key = $value; }`.
        *   Include `src/Database.php` and `src/ProfileUpdater.php` at the top.
        *   Include the appropriate handler function from `src/handlers/` based on the `page` parameter (e.g., `require_once __DIR__ . '/../src/handlers/PersonalInfoHandler.php';`).
        *   Obtain a PDO connection using `DatabaseConnection::getConnection()`.
        *   Call the relevant handler function (e.g., `getPersonalInfoUpdateData($_POST)`) to get the data array for the update.
        *   Call `ProfileUpdater::updateApplicant($pdo, $user_id, $data)` for updates to the `applicant` table.
        *   For `certificates` and `education` pages, call `ProfileUpdater::updateApplicantCertificate($pdo, $user_id, $data)` and `ProfileUpdater::updateApplicantEducation($pdo, $user_id, $data)` respectively.
        *   For `work_experience`, call `WorkExperienceHandler::handleWorkExperienceUpdate($pdo, $user_id, $_POST['experience'])`.
        *   Centralize the file upload logic (for resumes and other documents) into a separate helper function or class within `src/` and call it from `update_profile.php`.
        *   Ensure proper error handling and session messages are set.
    *   **Purpose:** To orchestrate the update process using the new modular components, making the main script cleaner and more focused.

### Phase 4: Testing

14. **Create `tests/` directory and add unit tests:**
    *   **Action:** Create a new directory named `tests` at the project root (`/var/www/helloworld/tests`).
    *   **Content:**
        *   Add unit tests for each handler function in `src/handlers/`. These tests should verify that the functions correctly process input data and return the expected associative arrays.
        *   Add unit tests for the `ProfileUpdater` class methods. These tests should verify that the methods correctly construct and execute SQL queries (using a mocked PDO object or a test database).
        *   Add an integration test for `update_profile.php` to ensure the overall flow works correctly from receiving POST data to updating the database.
        *   **Important:** Ensure tests use a separate test database or mock the PDO object for isolation and to prevent data corruption in the development database.
    *   **Purpose:** To ensure the correctness and reliability of the refactored code, and to enable independent verification of each component.
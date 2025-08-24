# Refactoring Plan: Add Integration Tests

The goal is to add integration tests for the newly refactored skilled profile modules. The testing approach will be based on the pattern observed in `skilled/register.php`, which involves setting a `TESTING_MODE` constant and verifying database changes.

## Phase 1: Test Setup and `personal_info` Test

- [x] **1. Create Test Directory and Bootstrap:**
  - [x] Create a new directory: `tests/`.
  - [x] Create a test bootstrap file `tests/bootstrap.php` to set up the testing environment (e.g., define `TESTING_MODE`, initialize database connection).
  - [x] Create a test runner script `tests/run_tests.sh` to execute all test files.

- [x] **2. Create Test for `personal_info`:**
  - [x] Create a new test file: `tests/test_update_personal_info.php`.
  - [x] The test will:
    - a. Start with a clean database state (or create a dedicated test user).
    - b. Set `$_SESSION` to simulate a logged-in user.
    - c. Prepare a `$_POST` array with new data for the personal info form.
    - d. `include` the `skilled/actions/update_personal_info.php` script.
    - e.  Query the database to verify that the user's data was updated correctly.
    - f. Clean up any created test data.

## Phase 2: Test Remaining Sections

- [x] **3. Create Tests for Other Profile Sections:**
  - [x] Repeat step 2 for all other backend action scripts:
    - [x] `tests/test_update_family_background.php`
    - [x] `tests/test_update_documents.php` (mocking file upload)
    - [x] `tests/test_update_job_preferences.php`
    - [x] `tests/test_update_health.php`
    - [ ] `tests/test_update_education.php` (Skipped: Updater is not implemented)
    - [x] `tests/test_update_work_experience.php`
    - [x] `tests/test_update_certificates.php`

## Phase 3: Frontend Page Smoke Tests

- [x] **4. Create Basic Tests for Frontend Pages:**
  - [x] Create simple "smoke tests" that ensure the new frontend pages can be included without fatal errors.
  - [x] For each page (e.g., `skilled/personal_info.php`):
    - a. Create a test file (e.g., `tests/test_view_personal_info.php`).
    - b. The test will simulate a logged-in user and `include` the page.
    - c. The test passes if no PHP fatal error occurs.

## Phase 4: Execution and Verification

- [x] **5. Run All Tests:**
  - [x] Execute the `tests/run_tests.sh` script to run the entire test suite.
  - [x] Ensure all tests pass and provide a final report.
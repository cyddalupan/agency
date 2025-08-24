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

# Troubleshooting Plan for helloworld.welfareph.com

This checklist outlines the steps to resolve the "Service Unavailable" error and ensure the PHP 5.6 application runs correctly within the Docker environment.

- [x] **Step 1: Confirm `mod_proxy_fcgi` availability in `httpd:2.4`**
    - [x] **Objective:** Verify if `mod_proxy_fcgi.so` exists in the `httpd:2.4` image and find its exact path.
    - [x] **Action:** `docker run --rm httpd:2.4 find / -name "mod_proxy_fcgi.so" 2>/dev/null`
    - [x] **Expected Output:** The absolute path to `mod_proxy_fcgi.so` (e.g., `/usr/local/apache2/modules/mod_proxy_fcgi.so`).

- [x] **Step 2: Explicitly Load `mod_proxy_fcgi` in `docker-apache.conf`**
    - [x] **Objective:** Modify `docker-apache.conf` to load `mod_proxy_fcgi` using a `LoadModule` directive.
    - [x] **Action:**
        - [x] Read the current `docker-apache.conf`.
        - [x] Use `replace` to insert the `LoadModule` line at the top of the file.
    - [x] **Verification:** Read `docker-apache.conf` again to confirm the `LoadModule` line is present.

- [x] **Step 3: Confirm PHP-FPM Socket Path in `php:5.6-fpm`**
    - [x] **Objective:** Find the exact Unix socket path where PHP-FPM is listening in the `php:5.6-fpm` container.
    - [x] **Action:**
        - [x] `docker create --name temp_php_fpm php:5.6-fpm`
        - [x] `docker cp temp_php_fpm:/usr/local/etc/php-fpm.d/ /tmp/php-fpm.d/`
        - [x] `docker rm temp_php_fpm`
        - [x] `grep -r "listen =" /tmp/php-fpm.d/`
    - [x] **Expected Output:** The Unix socket path (e.g., `/var/run/php/php5.6-fpm.sock`).

- [x] **Step 4: Configure `ProxyPassMatch` in `docker-apache.conf`**
    - [x] **Objective:** Ensure `docker-apache.conf` correctly uses `ProxyPassMatch` to forward PHP requests to the PHP-FPM socket.
    - [x] **Action:**
        - [x] Read the current `docker-apache.conf`.
        - [x] Use `replace` to update the `ProxyPassMatch` line if necessary.
    - [x] **Verification:** Read `docker-apache.conf` again to confirm the `ProxyPassMatch` line is correct.

- [ ] **Step 5: Rebuild and Restart Docker Compose Services**
    - [ ] **Objective:** Apply all the Apache configuration changes by rebuilding the `web` service and restarting all services.
    - [ ] **Action:**
        - [ ] `docker-compose -f /var/www/helloworld/docker-compose.yml build --no-cache`
        - [ ] `docker-compose -f /var/www/helloworld/docker-compose.yml up -d`
    - [ ] **Expected Output:** Successful build and container startup messages.

- [ ] **Step 6: Verify PHP Execution and Error Logging**
    - [ ] **Objective:** Confirm that `test.php` executes correctly and that errors are logged.
    - [ ] **Action:**
        - [ ] Instruct the user to access `https://helloworld.welfareph.com/test.php` in their browser.
        - [ ] `docker logs helloworld_web_1`
    - [ ] **Expected Output:** The `docker logs` should show the "Test error from test.php" message and "If you see this, PHP is working." should be displayed in the browser.
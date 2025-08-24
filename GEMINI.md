# Helloworld Application - Docker Compose Setup

This document provides instructions on how to set up and run this legacy PHP 5.6 application using the provided Docker Compose environment.

## 1. Getting Started

This project runs in a Docker environment, providing an Apache server with **PHP 5.6** and a **MySQL 5.7** database.

**Step 1: Build and Start the Containers**

Navigate to the project's root directory and run:

```bash
docker-compose up -d --build
```

This command builds the Docker image and starts the services in the background.

**Step 2: Access the Application**

Once the containers are running, access the application at **http://localhost:8080**.

**Step 3: Import the Database**

The `docker-compose` setup creates the `iwebphil_everlast` database. To populate it, run these commands from your host machine:

```bash
# Import main database structure
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/iwebphil_everlast.sql

# Import secondary file (errors about existing tables can be ignored)
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/empty.sql
```

## 2. Environment Management

Use the following commands to manage the Docker containers:

*   **Stop:** `docker-compose down`
*   **Start:** `docker-compose up -d`
*   **View Logs:** `docker-compose logs -f`
*   **View Service Logs:** `docker-compose logs -f web`

## 3. Application Architecture

This project uses a hybrid architecture with two PHP frameworks:

*   **CodeIgniter Front-End:** Handles the main user interface and authentication.
*   **Laravel 5.0 Back-End:** Located in the `page/` directory, used for API-like functions (e.g., session management).

The two frameworks share a session cookie (`ics-ipac`) to maintain a unified login state.

### Login Flow

1.  User logs in on the CodeIgniter front-end (`/admin/signin`).
2.  CodeIgniter redirects to `/admin/ng_signin`.
3.  An AngularJS script on this page sends a POST request with session data to the Laravel back-end (`/page/save-session`).
4.  Laravel saves the session data.
5.  The user is redirected to the admin dashboard (`/admin/dashboard`).

## 4. Routing

The application uses a mix of CodeIgniter's routing and `.htaccess` rules.

*   **CodeIgniter Routes:** Defined in `app/config/routes.php`. It maps hyphenated URLs to controllers with underscores (e.g., `/admin-system` -> `Admin_system.php`).
*   **.htaccess:** Routes requests to CodeIgniter's `index.php`.

To find the controller for a URL (e.g., `/admin/applicants/review_single`):

1.  Check for custom routes in `app/config/routes.php`.
2.  Look for a controller file matching the URL structure (e.g., `app/controllers/admin/Applicants.php`).
3.  Inside the controller, find the method corresponding to the URL (e.g., `review_single`).

## 6. Troubleshooting and Best Practices

This section summarizes common problems encountered when working with the application and provides suggestions for resolving them, along with best practices for an optimal workflow.

### General Troubleshooting Tips

*   **CRITICAL: `replace` Tool Failures (`0 occurrences found for old_string`)**
    *   **Problem:** The `old_string` provided to the `replace` tool does not exactly match the content in the file. This is a very common and frustrating error. It can happen if the file has been modified since it was last read, or if there are subtle differences in whitespace, indentation, or hidden characters (e.g., invisible BOM characters, different line endings).
    *   **Solution:** **ALWAYS** use the `read_file` tool to read the file's content immediately before attempting a `replace` operation. This ensures you have the most up-to-date and accurate `old_string` to match against.
        ```
        # INCORRECT: old_string might be outdated or have subtle differences
        # print(default_api.replace(file_path="file.txt", old_string="old content", new_string="new content"))

        # CORRECT: Read the file content first to get the exact old_string
        file_content = default_api.read_file(absolute_path="file.txt")['content']
        # Now use file_content as the old_string, or a precise substring from it
        # For example, if you want to replace 'foo' with 'bar' in a line 'hello foo world':
        # old_string_to_replace = "hello foo world"
        # new_string_to_replace = "hello bar world"
        # print(default_api.replace(file_path="file.txt", old_string=old_string_to_replace, new_string=new_string_to_replace))
        ```
        If issues persist, use `cat -vET <file_path>` in the shell to reveal all characters, including hidden ones, for precise matching.

*   **PHP `session_start()` Errors (`headers already sent`):**
    *   **Problem:** `session_start()` must be called before any output (including whitespace) is sent to the browser. In test scripts or included files, `echo` statements or even blank lines can cause this error.
    *   **Suggestion:** Ensure `session_start()` is the very first executable statement in your PHP scripts. In test scripts, use `ob_start()` at the beginning and `ob_end_clean()` at the end to buffer and discard any unintended output.

*   **PHP Version Compatibility Issues (e.g., `??` operator in PHP 5.6):**
    *   **Problem:** Using language features or syntax that are not supported by the target PHP version (e.g., the null coalescing operator `??` was introduced in PHP 7, but this project uses PHP 5.6).
    *   **Suggestion:** Always be aware of the project's specified PHP version. When modifying and adding code, use only compatible syntax and functions. Refer to PHP documentation for version-specific feature availability.

*   **MySQL Strict Mode Errors (`Field 'X' doesn't have a default value`):**
    *   **Problem:** When inserting data into a MySQL database, strict mode can prevent inserts if non-nullable columns without default values are not explicitly provided in the `INSERT` statement.
    *   **Suggestion:** For development and testing environments, you can disable MySQL strict mode for the PDO connection by adding `PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''"` to your PDO options array. For production, ensure all non-nullable columns have appropriate default values or are always included in `INSERT` statements.

*   **Mismatched `$_POST` Keys and Database Column Names:**
    *   **Problem:** Discrepancies between the `name` attributes in HTML forms (which populate `$_POST`) and the actual column names in the database can lead to data not being saved or updated correctly.
    *   **Suggestion:** Always carefully verify that the `$_POST` keys used in your PHP scripts precisely match the `name` attributes of your form fields and the corresponding database column names. Consistency is key for data integrity.

*   **Incomplete Test Simulation:**
    *   **Problem:** Test scripts may fail because they don't fully mimic the application's real-world execution flow (e.g., not simulating a logged-in user, missing `$_SERVER` variables like `REQUEST_METHOD`).
    *   **Suggestion:** Ensure your test scripts accurately set up all necessary environment variables (`$_SESSION`, `$_POST`, `$_SERVER`, etc.) and simulate the complete user journey or application state required for the code under test to function correctly.

### Lessons Learned from Recent Debugging

*   **`replace` Tool Precision:** The `replace` tool is extremely sensitive to exact string matching, including whitespace and newlines. Always copy the `old_string` directly from `read_file` output to ensure accuracy. If issues persist, consider replacing smaller, more isolated code blocks.

*   **PHP `session_start()` in Tests:** It's common for `session_start()` to issue a "Notice: A session had already been started" in test environments where a session might be initiated by the test runner or a bootstrap file. This is generally harmless and does not indicate a test failure.

*   **`TESTING_MODE` for Redirects:** When testing PHP scripts that perform redirects (`header('Location: ...')`), define a `TESTING_MODE` constant (e.g., `define('TESTING_MODE', true);`) in your test setup. Then, wrap redirect calls in your application code with an `if (!defined('TESTING_MODE')) { ... }` block to prevent premature exits during testing.

*   **Password Handling Discrepancy:** Be aware of inconsistencies in password handling (e.g., plain text storage in some parts of the application vs. hashing in others). For security, all password storage should ideally use strong hashing functions like `password_hash()`. Temporary deviations for compatibility should be clearly documented and addressed.

*   **`PDOStatement::rowCount()` and `UPDATE` Queries:** A `rowCount()` of 0 for an `UPDATE` query does not necessarily mean failure. It indicates that no rows were *changed*. This can occur if:
    *   The `WHERE` clause does not match any records.
    *   The values being updated are identical to the existing values in the database.
    *   Subtle type mismatches or `NULL` value handling can sometimes lead to unexpected `rowCount()` behavior even if the query executes without error.

*   **Dynamic `UPDATE` Query Construction and `NULL` Values:** When dynamically building `UPDATE` queries (e.g., using `foreach` loops to construct `SET` clauses), ensure that `NULL` values are handled appropriately. If a handler function returns `NULL` for fields not present in `$_POST`, these `NULL`s can be included in the `UPDATE` statement. Filtering out `NULL` values from the data array before constructing the query can prevent unintended updates or `rowCount()` issues.
    *   **Solution:** Use `array_filter($data, function($value) { return $value !== null; });` to remove `NULL` entries from the `$data` array before passing it to the `UPDATE` logic.

### Running and Debugging Tests

To ensure a consistent environment, all PHP tests must be executed inside the `web` Docker container. This provides the test scripts with the correct PHP version and all the necessary extensions, like `pdo_mysql`.

*   **Running a Single Test:**
    To run a single test file, use the `docker-compose exec` command:
    ```bash
docker-compose exec web php /var/www/html/skilled/test_login_integration.php
    ```
    Replace `/var/www/html/skilled/test_login_integration.php` with the actual path to the test file you want to run.

*   **Running All Tests:**
    You can run all tests in a directory by iterating through the files and executing them. A dedicated shell script (e.g., `run_tests.sh`) can automate this process, providing a single, consistent command to run the entire test suite.

*   **Debugging Failing Tests (PHP/Database Interactions):**
    When tests fail, especially those involving database operations, it's crucial to inspect the exact SQL queries and parameters being executed.

    *   **Direct Output Debugging:** For quick debugging during development, you can temporarily add `var_dump()` statements followed by `die()` in your PHP code (e.g., in `update_profile.php`) to print variables and stop execution. Ensure any output buffering in calling scripts is disabled (e.g., by commenting out `ob_start()` and `ob_end_clean()` in test files).
        ```php
        // Example in your PHP script
        var_dump($sql);
        var_dump($params);
        die("Debug Stop");
        ```
    *   **Checking Server Error Logs:** If direct output is not feasible, PHP errors and `error_log()` messages are typically written to the web server's error log (e.g., `/var/log/apache2/error.log` inside the `web` container). You can view these logs using `docker-compose logs web` or `docker-compose exec web cat /var/log/apache2/error.log`.

*   **Importance of a Comprehensive Test Suite:**
    A robust test suite is invaluable for catching regressions and ensuring code quality. It allows for rapid validation of changes and helps maintain application stability.
### Running the Profile Module Test Suite

A dedicated test suite has been created for the refactored "Skilled Profile" module. These are integration tests that simulate form submissions and verify database changes.

**Prerequisites:**
1.  Ensure the Docker environment is running: `docker-compose up -d`

**Running the Full Suite:**
To run all tests for the profile module, execute the test runner script from the project root:

```bash
./tests/run_tests.sh
```

The script will automatically find and run all test files located in the `/tests` directory inside the `web` container. It will report progress and exit immediately if any test fails.

**How it Works:**
*   `tests/bootstrap.php`: This file sets up the testing environment. It defines a `TESTING_MODE` constant, connects to the database, and provides helper functions for creating and deleting test users.
*   `tests/test_*.php`: Each test file is responsible for a specific action (e.g., `test_update_personal_info.php`). It sets up the necessary `$_SESSION` and `$_POST` data, includes the action script it's testing, and then asserts that the database was updated correctly.
*   `tests/run_tests.sh`: This script automates the process of running all tests inside the Docker container, ensuring the correct PHP environment and database connection.

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

## 5. Database

*   **Host:** `db`
*   **Database:** `iwebphil_everlast`
*   **User:** `root`
*   **Password:** (empty)

Credentials are set in `config.php`. The full schema is in `database.md`.

## 6. Troubleshooting and Best Practices for Gemini CLI

This section summarizes common problems encountered when working with the Gemini CLI and provides suggestions for resolving them, along with best practices for an optimal workflow.

### Common Problems and Solutions

*   **`replace` Tool Failures (`0 occurrences found for old_string`):**
    *   **Problem:** The `old_string` provided to the `replace` tool does not exactly match the content in the file. This can happen if the file has been modified since it was last read, or if there are subtle differences in whitespace, indentation, or hidden characters.
    *   **Suggestion:** Always use the `read_file` tool to read the file's content immediately before using `replace`. This ensures you have the most up-to-date and accurate `old_string`. If issues persist, use `cat -vET <file_path>` in the shell to reveal all characters, including hidden ones, for precise matching.

*   **PHP `session_start()` Errors (`headers already sent`):**
    *   **Problem:** `session_start()` must be called before any output (including whitespace) is sent to the browser. In test scripts or included files, `echo` statements or even blank lines can cause this error.
    *   **Suggestion:** Ensure `session_start()` is the very first executable statement in your PHP scripts. In test scripts, use `ob_start()` at the beginning and `ob_end_clean()` at the end to buffer and discard any unintended output.

*   **PHP Version Compatibility Issues (e.g., `??` operator in PHP 5.6):**
    *   **Problem:** Using language features or syntax that are not supported by the target PHP version (e.g., the null coalescing operator `??` was introduced in PHP 7, but this project uses PHP 5.6).
    *   **Suggestion:** Always be aware of the project's specified PHP version. When modifying or adding code, use only compatible syntax and functions. Refer to PHP documentation for version-specific feature availability.

*   **MySQL Strict Mode Errors (`Field 'X' doesn't have a default value`):**
    *   **Problem:** When inserting data into a MySQL database, strict mode can prevent inserts if non-nullable columns without default values are not explicitly provided in the `INSERT` statement.
    *   **Suggestion:** For development and testing environments, you can disable MySQL strict mode for the PDO connection by adding `PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''"` to your PDO options array. For production, ensure all non-nullable columns have appropriate default values or are always included in `INSERT` statements.

*   **Mismatched `$_POST` Keys and Database Column Names:**
    *   **Problem:** Discrepancies between the `name` attributes in HTML forms (which populate `$_POST`) and the actual column names in the database can lead to data not being saved or updated correctly.
    *   **Suggestion:** Always carefully verify that the `$_POST` keys used in your PHP scripts precisely match the `name` attributes of your form fields and the corresponding database column names. Consistency is key for data integrity.

*   **Incomplete Test Simulation:**
    *   **Problem:** Test scripts may fail because they don't fully mimic the application's real-world execution flow (e.g., not simulating a logged-in user, missing `$_SERVER` variables like `REQUEST_METHOD`).
    *   **Suggestion:** Ensure your test scripts accurately set up all necessary environment variables (`$_SESSION`, `$_POST`, `$_SERVER`, etc.) and simulate the complete user journey or application state required for the code under test to function correctly.

## 7. Running Tests

To ensure a consistent environment, all tests must be executed inside the `web` Docker container. This provides the test scripts with the correct PHP version and all the necessary extensions, like `pdo_mysql`.

### Running a Single Test

To run a single test file, use the `docker-compose exec` command:

```bash
docker-compose exec web php /var/www/html/skilled/test_login_integration.php
```

Replace `/var/www/html/skilled/test_login_integration.php` with the actual path to the test file you want to run.

### Running All Tests

You can run all tests in a directory by iterating through the files and executing them:

```bash
for test_file in skilled/test_*.php; do
  echo "Running $test_file..."
  docker-compose exec web php /var/www/html/$test_file
  echo ""
done
```

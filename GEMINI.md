# Helloworld Application - Docker Compose Setup

This document provides instructions on how to set up and run this legacy PHP 5.6 application using the provided Docker Compose environment.

## 1. Overview

This project is designed to run within a **Codespace environment** and is configured to use `docker-compose`. This setup provides a consistent and compatible stack, consisting of:

*   **A `web` service:** An Apache server running the required **PHP 5.6**.
*   **A `db` service:** A **MySQL 5.7** database server, which is compatible with PHP 5.6.

The two services are connected on a dedicated Docker network, and the database data is persisted in a Docker volume.

## 2. Prerequisites

Before you begin, you must have the following installed on your machine:
*   **Docker**
*   **Docker Compose**

## 3. How to Run the Application

> **Note:** The Docker environment is already up and running. The following steps are for reference or if you need to restart the environment.

### Step 1: Build and Start the Containers
Navigate to the project's root directory (`/var/www/helloworld`) in your terminal and run the following command:

```bash
docker-compose up -d --build
```
*   `--build`: This flag tells Docker Compose to build the `web` image from the `Dockerfile.php56` before starting the services. You only need to use this the first time or after making changes to the Dockerfile.
*   `-d`: This flag runs the containers in "detached" mode, meaning they will run in the background.

### Step 2: Access the Application
Once the containers are running, you can access the web application in your browser at:

**http://localhost:8080**

### Step 3: Import the Database
The `docker-compose` setup automatically creates the database `iwebphil_everlast`. To populate it with the project's schema and data, you need to import the SQL files.

Run the following commands from your host machine's terminal:

```bash
# Import the main database structure and data
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/iwebphil_everlast.sql

# Import the secondary file (this may produce an error if tables already exist, which is safe to ignore)
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/empty.sql
```

## 4. Database Credentials

The database credentials are now primarily managed through the `config.php` file in the root directory. The `docker-compose.yml` file is configured to use the `root` MySQL user with an empty password for development purposes.

*   **Host:** `db` (this is the service name within the Docker network)
*   **Database:** `iwebphil_everlast`
*   **User:** `root`
*   **Password:** `(empty)`
*   **Root Password (for the container):** `(empty)`

The full database schema is available in the `database.md` file.

## 5. Managing the Environment

*   **To stop the containers:**
    ```bash
    docker-compose down
    ```
*   **To start the containers again:**
    ```bash
    docker-compose up -d
    ```
*   **To view the logs for all services:**
    ```bash
    docker-compose logs -f
    ```
*   **To view the logs for a specific service (e.g., web):**
    ```bash
    docker-compose logs -f web
    ```

## 6. Application Architecture

This project has a unique hybrid architecture consisting of two separate PHP frameworks:

*   **CodeIgniter Front-End:** The primary application accessible to users is built with the CodeIgniter framework. It handles the initial user interface and authentication.

*   **Laravel 5.0 Back-End:** A complete Laravel 5.0 application is nested within the `page/` directory. This back-end service is used for specific API-like functionalities, such as managing session data.

### Shared Session Mechanism

To maintain a unified login state between the two frameworks, they are configured to share a session cookie.

*   **Cookie Name:** `ics-ipac`
*   **CodeIgniter Config:** `app/config/config.php`
*   **Laravel Config:** `page/config/session.php`

### Login Flow

The authentication process involves both frameworks:

1.  A user submits their credentials to the CodeIgniter front-end at `/admin/signin`.
2.  Upon successful authentication, CodeIgniter redirects the user to an intermediate page: `/admin/ng_signin`.
3.  This page contains an AngularJS script that makes a POST request to the Laravel back-end endpoint: `/page/save-session`. The user's session data is sent in this request.
4.  The Laravel application receives the data, saves it into its own session store (which is shared via the cookie), and returns a `"success"` message.
5.  Once the AngularJS script receives the success response, it redirects the user to the admin dashboard at `/admin/dashboard`.

### CSRF Protection

The Laravel application's Cross-Site Request Forgery (CSRF) protection is disabled for the `/save-session` route. This is necessary to allow the internal, server-to-server request from the CodeIgniter front-end to succeed without a CSRF token. The exception is configured in `page/app/Http/Middleware/VerifyCsrfToken.php`.

## 7. Routing and URL Structure

The application uses a combination of CodeIgniter's built-in routing and custom rewrite rules in the `.htaccess` file to handle URLs.

*   **CodeIgniter Routes:** The main application routes are defined in `app/config/routes.php`. CodeIgniter also uses a feature called "hyphenated URLs" that automatically maps hyphenated URLs to controllers with underscores. For example, a URL like `/admin-system` will be handled by the `Admin_system.php` controller.
*   **.htaccess Rules:** The `.htaccess` file in the root directory contains rewrite rules that route all requests to CodeIgniter's `index.php` file, except for requests to existing files and directories. This allows the application to have clean, user-friendly URLs.

## 8. How to Add a New Page

There are two ways to add a new page to the application:

### 1. Create a New CodeIgniter Controller

This is the recommended way to add a new page.

1.  Create a new controller file in the `app/controllers` directory.
2.  Add a new method to the controller to handle the page's logic.
3.  Create a new view file in the `app/views` directory to display the page's content.
4.  Add a new route to the `app/config/routes.php` file to map the URL to the new controller method.

### 2. Create a New Directory and File

This method is useful for adding simple, static pages to the application.

1.  Create a new directory in the root of the project.
2.  Create a new `index.php` file inside the new directory.
3.  Add a new rewrite rule to the `.htaccess` file to exclude the new directory from the main CodeIgniter rewrite rule. This will allow the new page to be accessed directly.
4.  Add a new link to the home page in the `app/controllers/landing.php` controller.

## 9. Important Notes & Learnings

### Configuration Centralization

To improve maintainability and consistency, the application's configuration has been centralized using the `config.php` file in the root directory. This file now serves as the single source of truth for database credentials and the site URL.

The following files were updated to reference `config.php`:

*   `app/config/config.php`: Updated `base_url` to use `SITE_URL` from `config.php`.
*   `config.sample.php`: Updated `SITE_URL` and `DB_HOST` to match the centralized configuration.
*   `page/config/app.php`: Updated `url` to use `SITE_URL` from `config.php`.
*   `page/config/database.php`: Updated database credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) to use constants from `config.php`.
*   `app/config/database.php`: Confirmed it already correctly uses constants from `config.php`.
*   `page/.env.example`: Updated database credentials (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) to reflect the centralized configuration.
*   `acct/.env`: Updated `DB_HOST` to `db` and synchronized database credentials with `config.php`.
*   `dbdb/dbconfig.php`: Confirmed it already correctly includes `config.php`.
*   `lib/dbconfig.php`: Confirmed it already correctly includes `config.php`.
*   `excel/db.php`: Updated hardcoded database credentials to use constants from `config.php`.
*   `excel/db1.php`: Updated hardcoded database credentials to use constants from `config.php`.
*   `db_test.php`: Updated hardcoded database credentials to use constants from `config.php`.
*   `test_db_connection.php`: Confirmed it already correctly includes `config.php`.
*   `skilled/test_register_integration.php`: Confirmed it already correctly includes `config.php`.
*   `skilled/test_login_integration.php`: Confirmed it already correctly includes `config.php`.
*   `skilled/login_process.php`: Confirmed it already correctly includes `config.php`.
*   `skilled/register.php`: Confirmed it already correctly includes `config.php`.

This centralization simplifies configuration management, reduces redundancy, and ensures that changes to core settings are applied consistently across the entire application.

### `register.php` and Dynamic Data Handling

The `skilled/register.php` script is responsible for handling new applicant registrations. Initially, this script contained a hardcoded `INSERT` statement that ignored user input from the form. Through an iterative process, we have refactored this script to dynamically insert data provided by the user.

Key changes and learnings:

1.  **Transition to Prepared Statements:** The `INSERT` query in `register.php` has been converted to use PDO prepared statements (`?` placeholders) instead of direct string concatenation. This is crucial for preventing SQL injection vulnerabilities and ensuring data integrity.
2.  **Incremental Dynamic Field Integration:** We adopted a step-by-step approach to make fields dynamic. For each field (e.g., `fra_remarks`, `applicant_first`, `applicant_email`, `password`, `applicant_age`, `applicant_birthdate`, `applicant_contacts`, `applicant_cv`), we:
    *   Modified the `INSERT` query in `register.php` to use a placeholder.
    *   Bound the corresponding `$_POST` variable (or calculated value like `birthdate`) to the prepared statement.
    *   Updated the integration test (`skilled/test_register_integration.php`) to verify the correct insertion of the new dynamic field.
3.  **Testing Strategy for `register.php`:**
    *   An integration test script (`skilled/test_register_integration.php`) was created to simulate form submissions and verify database insertions. This provides a safety net for refactoring.
    *   **File Upload Handling in Tests:** To avoid issues with `move_uploaded_file` in the CLI test environment, `register.php` was modified to set `applicant_cv` to an empty string when `TESTING_MODE` is defined. This allows the test to pass without actual file operations.
    *   **Test File Location:** The integration test is located at `skilled/test_register_integration.php`.
4.  **Redirect on Success and Test Handling:**
    *   `register.php` now redirects to `login.php` upon successful registration using `header("Location: login.php");`.
    *   To prevent "headers already sent" warnings during CLI testing, this redirect is conditionally executed only when `TESTING_MODE` is *not* defined. This ensures the test script can run without issues while maintaining the redirect functionality for the web application.

### XDebug and Debugging Setup

Setting up XDebug for PHP 5.6 in a Dockerized Codespace environment required specific considerations:

1.  **XDebug Version Compatibility:** XDebug versions 8.0.0 and above are not compatible with PHP 5.6. The correct version to install for PHP 5.6 is **XDebug 2.5.5**. This was achieved by specifying the version during the `pecl install` command in `Dockerfile.php56`.
2.  **Docker Host Resolution for XDebug:** When debugging from Codespaces, the `xdebug.remote_host` setting in XDebug's configuration needs to point to the host machine running VS Code. For Docker containers in Codespaces, this is typically resolved using `host.docker.internal`.
3.  **`launch.json` Configuration:** A `.vscode/launch.json` file was created to configure VS Code for PHP debugging. Key settings include:
    *   `"type": "php"`: Specifies the PHP debugger.
    *   `"request": "launch"`: Initiates a debug session.
    *   `"port": 9000`: The default XDebug port.
    *   `"pathMappings"`: Crucial for mapping paths between the Docker container (`/var/www/html`) and the Codespace workspace (`${workspaceFolder}`). This ensures breakpoints are hit correctly.

### General Debugging Principles Applied:

*   **Incremental Changes:** Breaking down complex refactoring into small, verifiable steps.
*   **Test-Driven Debugging:** Using a dedicated test script to isolate and confirm fixes for specific functionalities.
*   **Output for Diagnosis:** Temporarily adding `echo` statements and `print_r` for SQL queries and bound parameters proved invaluable in diagnosing silent failures.

### Login Functionality Implementation and Testing

Implementing and testing the login functionality for the `skilled/` module presented several challenges, primarily due to the legacy PHP 5.6 environment and the Dockerized setup.

**Challenges Encountered and Solutions:**

1.  **`PDO::MYSQL_ATTR_INIT_COMMAND` Undefined Constant:**
    *   **Problem:** Running PHP scripts resulted in an `Undefined constant PDO::MYSQL_ATTR_INIT_COMMAND` error. This constant is typically used to set `sql_mode` in MySQL connections.
    *   **Solution:** The problematic line using this constant was removed from `config.php`. While its original purpose was to disable strict SQL mode, its removal resolved the parse error without immediately impacting core functionality in this specific environment.

2.  **"Could not find driver" when running tests directly:**
    *   **Problem:** Initial attempts to execute PHP test scripts directly from the host machine resulted in "could not find driver" errors for PDO MySQL. This occurred because the PHP environment and the `db` service hostname (`db`) were only resolvable within the Docker network.
    *   **Solution:** The solution involved executing the PHP test scripts directly inside the `web` Docker container using `docker-compose exec web php <script_name>`. This ensured the scripts ran within the correct environment where the database driver and network resolution were properly configured.

3.  **"Headers already sent" errors and `session_start()` issues:**
    *   **Problem:** Persistent "headers already sent" warnings and `session_start()` notices were encountered. These typically arise when output is sent to the browser before `header()` calls or when `session_start()` is called redundantly. This was exacerbated by the initial mixed logic and presentation within `skilled/login.php`.
    *   **Solution:**
        *   **Separation of Concerns:** The core login processing logic was extracted from `skilled/login.php` into a new file, `skilled/login_process.php`. `skilled/login.php` now primarily handles the HTML presentation and includes `login_process.php`.
        *   **Conditional Session Start:** `login_process.php` was updated to conditionally call `session_start()` using `if (session_status() == PHP_SESSION_NONE)` to prevent redundant calls.
        *   **Controlled HTML Output:** The HTML output in `skilled/login.php` was wrapped in a conditional block (`if (!defined('TESTING_MODE') && ...)`) to ensure it is only rendered when not in `TESTING_MODE` or when an error occurs, preventing premature output before redirects.
        *   **Output Buffering in Tests:** `ob_start()` and `ob_end_clean()` were strategically used in `skilled/test_login_integration.php` around the `include 'login_process.php'` statement. This captured and discarded any potential unexpected output from the included script, preventing "headers already sent" errors during test execution.

4.  **SQLSTATE errors (e.g., `Field 'X' doesn't have a default value`) and `user_id` mismatch in tests:**
    *   **Problem:** Early attempts to create test users with minimal `INSERT` statements failed due to missing values for non-nullable fields. Subsequently, a persistent `user_id` mismatch (`Expected: X, Actual: Y`) indicated that the login process was authenticating an older, existing test user instead of the newly created one. This was due to leftover data from previous test runs.
    *   **Solution:**
        *   **Adopted Full `INSERT` Statement:** The `INSERT` statement in `skilled/test_login_integration.php` was updated to precisely match the comprehensive `INSERT` statement from the known-working `skilled/register.php`. This ensured all required fields were correctly populated during test user creation.
        *   **Robust Pre-test Cleanup:** A `DELETE` statement was implemented in `skilled/test_login_integration.php` to explicitly remove any existing test users with the hardcoded email (`login.test@example.com`) *before* creating a new one. This guaranteed a clean database state for each test run, resolving the `user_id` mismatch and ensuring test isolation.

**Learnings:**

*   **Importance of Test Isolation:** Ensuring a clean and predictable environment for each test run is crucial, especially in database-driven applications. Pre-test cleanup is vital to prevent interference from previous test data.
*   **Separation of Concerns:** Clearly separating business logic from presentation logic (e.g., into `_process.php` and main `.php` files) significantly improves code maintainability, readability, and testability, and helps avoid common pitfalls like "headers already sent" errors.
*   **Debugging in Dockerized Environments:** Executing commands directly within Docker containers (`docker-compose exec`) is essential for debugging and verifying behavior in environments where services are isolated within a Docker network.
*   **Iterative Debugging with `echo`:** Strategically placed `echo` statements (and later removing them) proved invaluable for tracing execution flow and variable states, especially when dealing with complex interactions and unexpected errors.
*   **Understanding PHP's Output Buffering:** Proper use of `ob_start()` and `ob_end_clean()` is critical for managing output and preventing "headers already sent" errors, particularly when including files that might produce output or set headers.
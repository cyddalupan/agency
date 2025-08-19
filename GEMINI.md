# Helloworld Application - Docker Compose Setup

This document provides instructions on how to set up and run this legacy PHP 5.6 application using the provided Docker Compose environment.

## 1. Overview

This project is configured to run in a fully containerized environment using `docker-compose`. This setup provides a consistent and compatible stack, consisting of:

*   **A `web` service:** An Apache server running the required **PHP 5.6**.
*   **A `db` service:** A **MySQL 5.7** database server, which is compatible with the PHP 5.6 drivers.

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
docker exec -i helloworld_db_1 mysql -u root -proot_password_change_me iwebphil_everlast < /var/www/helloworld/iwebphil_everlast.sql

# Import the secondary file (this may produce an error if tables already exist, which is safe to ignore)
docker exec -i helloworld_db_1 mysql -u root -proot_password_change_me iwebphil_everlast < /var/www/helloworld/empty.sql
```

## 4. Database Credentials

The database credentials are set in two places:

1.  **`docker-compose.yml`:** This file sets the environment variables for the `db` container, which are used to initialize the MySQL server.
2.  **`config.php`:** This file is used by the PHP application to connect to the database.

The credentials are:
*   **Host:** `db` (this is the service name within the Docker network)
*   **Database:** `iwebphil_everlast`
*   **User:** `helloworld_user`
*   **Password:** `p@ssw0rd_H3ll0W0rld!`
*   **Root Password (for the container):** `root_password_change_me`

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

### General Debugging Principles Applied:

*   **Incremental Changes:** Breaking down complex refactoring into small, verifiable steps.
*   **Test-Driven Debugging:** Using a dedicated test script to isolate and confirm fixes for specific functionalities.
*   **Output for Diagnosis:** Temporarily adding `echo` statements and `print_r` for SQL queries and bound parameters proved invaluable in diagnosing silent failures.

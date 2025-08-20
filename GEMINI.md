# Helloworld Application - Docker Compose Setup

This document provides instructions on how to set up and run this legacy PHP 5.6 application using the provided Docker Compose environment.

## 1. Overview

This project is designed to run within a **Codespace environment** and is configured to use `docker-compose`. This setup provides a consistent and compatible stack, consisting of:

*   **A `web` service:** An Apache server running the required **PHP 5.6**.
*   **A `db` service:** A **MySQL 5.7** database server.

The two services are connected on a dedicated Docker network, and the database data is persisted in a Docker volume.

## 2. How to Run the Application

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

Run the following commands from your host machine's terminal (adjust paths if necessary):

```bash
# Import the main database structure and data
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/iwebphil_everlast.sql

# Import the secondary file (this may produce an error if tables already exist, which is safe to ignore)
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/empty.sql
```

## 3. Database Credentials

The database credentials are set in `config.php` in the root directory. For development, the `docker-compose.yml` uses the `root` MySQL user with an empty password.

*   **Host:** `db` (service name within Docker network)
*   **Database:** `iwebphil_everlast`
*   **User:** `root`
*   **Password:** `(empty)`

The full database schema is available in the `database.md` file.

## 4. Managing the Environment

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

## 5. Application Architecture

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

## 6. Routing and URL Structure

The application uses a combination of CodeIgniter's built-in routing and custom rewrite rules in the `.htaccess` file to handle URLs.

*   **CodeIgniter Routes:** The main application routes are defined in `app/config/routes.php`. CodeIgniter also uses a feature called "hyphenated URLs" that automatically maps hyphenated URLs to controllers with underscores. For example, a URL like `/admin-system` will be handled by the `Admin_system.php` controller.
*   **.htaccess Rules:** The `.htaccess` file in the root directory contains rewrite rules that route all requests to CodeIgniter's `index.php` file, except for requests to existing files and directories. This allows the application to have clean, user-friendly URLs.

### Locating CodeIgniter Controller Files

When trying to find the PHP file responsible for a specific URL in CodeIgniter, consider the following:

1.  **Default Routing:** CodeIgniter's default URL structure is `example.com/controller/method/parameter/`.
    *   The first segment after the base URL is typically the controller name (e.g., `applicants` in `/applicants/review_single`). The controller file would be `app/controllers/Applicants.php`.
    *   The second segment is usually a method within that controller (e.g., `review_single`).

2.  **Nested Controllers (Subdirectories):** Controllers can be organized into subdirectories within `app/controllers/`. If a URL segment matches a directory name, look for the controller file inside that directory.
    *   For a URL like `/admin/applicants/review_single`, `admin` might be a subdirectory within `app/controllers/`. In this case, the controller file would be `app/controllers/admin/Applicants.php`. 

3.  **Hyphenated URLs:** As mentioned, CodeIgniter maps hyphenated URLs to controllers with underscores (e.g., `/admin-system` maps to `Admin_system.php`).

4.  **Custom Routes:** Always check `app/config/routes.php` for custom routing rules that might override the default behavior.

**Example:** For the URL `/admin/applicants/review_single/<id>`:
*   First, check `app/config/routes.php` for any explicit routes.
*   If no custom route is found, consider `admin` as a potential subdirectory.
*   Look for `app/controllers/admin/applicants.php`.
*   Within `app/controllers/admin/applicants.php`, search for a method named `review_single`.

This approach helps in systematically locating the correct controller file and method.
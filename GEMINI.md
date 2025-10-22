# Gemini Code Understanding: Project Structure

This document provides an overview of the complex and heterogeneous structure of the "agency" project. The codebase is a mix of different PHP frameworks, JavaScript libraries, and native PHP code, indicating that it has likely evolved over time with different developers and technologies.

## High-Level Overview

The project is not a single, monolithic application but rather a collection of several distinct modules or sub-applications, each with its own structure and dependencies.

- **Multiple Frameworks**: The project utilizes CodeIgniter and native PHP. There are also indications of modern JavaScript usage, possibly including AngularJS.
- **Modular Architecture**: The top-level directory is divided into several folders, each representing a different part of the system (e.g., `app`, `acct`, `skilled`, `excel`).
- **Shared Configuration**: A global `config.php` at the root level seems to store the primary database credentials, which are then used by various parts of the application.

## Directory Breakdown

Here is a breakdown of the key directories and their likely purpose:

- **`/` (Root Directory)**:
  - `config.php`: Main database configuration.
  - `index.php`: Main entry point of the application.
  - `.htaccess`: Apache configuration for URL rewriting, likely for routing requests to the appropriate framework or script.

- **`acct/`**:
  - **Technology**: This directory appears to be a self-contained application. The presence of `bower.json`, `gulpfile.js`, and `package.json` suggests a JavaScript-heavy frontend, likely using AngularJS as mentioned by the user.
  - **Purpose**: Based on the file names (`agent_agreement.php`, `employer_applicants.php`, `expense-payroll.php`), this module seems to be related to accounting, agent and employer management.

- **`app/`**:
  - **Technology**: This is a classic **CodeIgniter** application. The directory structure (`controllers`, `models`, `views`, `helpers`, `libraries`) is characteristic of this framework.
  - **Purpose**: This is likely the main administrative backend of the application.

- **`excel/`**:
  - **Technology**: Native PHP scripts.
  - **Purpose**: This module seems dedicated to generating Excel reports and handling data imports/exports. It has its own database connection file (`db.php`).

- **`login/`**:
  - **Technology**: Native PHP.
  - **Purpose**: A simple, standalone login portal.

- **`others/`**:
  - **Technology**: A collection of various native PHP scripts.
  - **Purpose**: This directory seems to be a catch-all for miscellaneous scripts and reports that may not fit into the other modules.

- **`skilled/`**:
  - **Technology**: This is a self-contained module written in **native PHP**. It does not use a framework but follows a clear structural pattern.
  - **Purpose**: This module manages the registration, login, and profile of "skilled workers".

## Deep Dive: The `skilled/` Module

The `skilled/` directory is a good example of the native PHP implementation within this project. It follows a consistent pattern:

- **Page Files (e.g., `personal_info.php`, `documents.php`)**: These files are the user-facing pages. They handle:
  1.  **Bootstrapping**: Including `includes/profile_bootstrap.php` to manage sessions and database connections.
  2.  **UI**: Including a common `header.php`, `footer.php`, and `profile_nav.php` for consistent layout.
  3.  **Forms**: Displaying HTML forms that submit data to corresponding files in the `actions/` directory.

- **Action Files (`actions/`)**: These files process the form submissions from the page files. For example, `actions/update_personal_info.php` handles the form POSTed from `personal_info.php`. Their responsibilities include:
  1.  **Security**: Checking for user authentication and valid request methods.
  2.  **Data Handling**: Requiring a corresponding "handler" file (e.g., `src/handlers/PersonalInfoHandler.php`) to process the `$_POST` data.
  3.  **Database Updates**: Requiring an "updater" file (e.g., `src/updaters/ApplicantUpdater.php`) to execute the database queries.
  4.  **Redirection**: Redirecting the user back to the profile page after the action is complete.

- **Source Code (`src/`)**: Although not explicitly shown in the file listing, the `require_once` statements in the `actions` files point to a `src` directory at the root level, which likely contains the core business logic:
  - `src/Database.php`: A database connection manager.
  - `src/handlers/`: Classes or functions for processing and validating form data.
  - `src/updaters/`: Classes or functions responsible for writing data to the database.

- **Testing**: The `skilled` directory also contains several test files (`test_*.php`), indicating an attempt to write integration tests for the registration and login functionality.

## Conclusion

This project is a complex legacy system. When working on it, it is crucial to:

1.  **Identify the Context**: Determine which module or framework you are working in before making changes.
2.  **Respect Existing Patterns**: Follow the conventions of the specific module you are editing (e.g., CodeIgniter MVC in `app/`, native PHP page/action pattern in `skilled/`).
3.  **Be Mindful of Shared Resources**: Be careful when modifying shared files like `config.php` as they affect multiple parts of the system.

REMEMBER: 
- This is a live production code and we need to be careful on how we code and what strategies we are using.
- for large code file, find a way to split it in a safe way.

## New Module: Analytics Agent (Angular SPA)

A new standalone Angular Single Page Application (SPA) has been integrated into the project, accessible at `/agency/analytics-agent`. This module provides analytics capabilities and interacts with a simple PHP API endpoint.

### Deployment and Configuration

-   **Location**: The Angular application's build output is deployed to `/agency/analytics-agent/`.
-   **Build Process**: The Angular project (`analytics-agent-app/`) is built using `npm run build`. The build script automates the following:
    1.  Builds the Angular application into a temporary directory (`dist-temp`).
    2.  Removes the existing deployment directory (`../analytics-agent/`).
    3.  Creates a new deployment directory (`../analytics-agent/`).
    4.  Copies the contents of the temporary build's `browser/` subdirectory to the deployment directory.
    5.  Copies a specific `.htaccess` file from `analytics-agent-app/src/.htaccess` to the deployment directory.
    6.  Cleans up the temporary build directory.
-   **`baseHref`**: The Angular application is configured with a `baseHref` of `/agency/analytics-agent/` to ensure correct asset loading and routing within the subdirectory. This is explicitly set in the `ng build` command within `package.json`.
-   **`.htaccess`**: A simplified `.htaccess` file is used within `/agency/analytics-agent/` to handle client-side routing for the Angular SPA. It ensures that requests for non-existent files or directories are rewritten to `index.html`, allowing Angular's router to take over.
    ```apache
    <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteBase /agency/analytics-agent/

      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteRule ^ index.html [L]
    </IfModule>
    ```
-   **Zone.js**: For standalone Angular components, `zone.js` is imported directly in `src/main.ts` to ensure proper change detection.

### API Integration (PHP Endpoint)

The Angular application communicates with a basic PHP API endpoint for data exchange.

-   **Endpoint Location**: `/agency/api/hello.php`
-   **Functionality**: This endpoint currently returns a simple JSON object containing a message and a timestamp.
-   **CORS**: The PHP endpoint includes `Access-Control-Allow-Origin: *` header to enable Cross-Origin Resource Sharing (CORS) for development purposes. **This should be restricted to specific origins in a production environment.**
    ```php
    <?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); // IMPORTANT: Restrict in production

    echo json_encode([
        'message' => 'Hello from PHP!',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    ?>
    ```

### Angular Service and Component Usage

-   **`ApiService` (`analytics-agent-app/src/app/api.ts`)**:
    -   Provides a method (`getHelloMessage()`) to make an HTTP GET request to the PHP endpoint.
    -   Uses `HttpClient` for making requests.
-   **`AppComponent` (`analytics-agent-app/src/app/app.ts`)**:
    -   Injects `ApiService`.
    -   Calls `getHelloMessage()` on initialization (`ngOnInit`).
    -   Displays the fetched `message` and `timestamp` in its template.
-   **`app.config.ts` (`analytics-agent-app/src/app/app.config.ts`)**:
    -   Configured to provide `HttpClient` for the application using `provideHttpClient()`.

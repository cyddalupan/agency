# Helloworld Application - Docker Compose Setup

This document provides instructions on how to set up and run this legacy PHP 5.6 application using the provided Docker Compose environment.

## 1. Getting Started

This project runs in a Docker environment, providing an Apache server with **PHP 5.6** and a **MySQL 5.7** database.

**Step 1: Build and Start the Containers**

```bash
docker-compose up -d --build
```

**Step 2: Access the Application**

Access the application at **http://localhost:8080**.

**Step 3: Import the Database**

```bash
# Import main database structure
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/iwebphil_everlast.sql

# Import secondary file (errors about existing tables can be ignored)
docker exec -i agency-db-1 mysql -u root iwebphil_everlast < /var/www/html/empty.sql
```

## 2. Environment Management

*   **Stop:** `docker-compose down`
*   **Start:** `docker-compose up -d`
*   **View Logs:** `docker-compose logs -f`
*   **View Service Logs:** `docker-compose logs -f web`
*   **View Error Logs:** `docker exec -it helloworld_php_1 tail /var/log/php/errors.log`

## 3. Running Tests

To ensure a consistent environment, all PHP tests must be executed inside the `web` Docker container.

*   **Running a Single Test:**
    ```bash
    docker-compose exec web php /var/www/html/skilled/test_login_integration.php
    ```
*   **Running the Full Suite:**
    ```bash
    ./tests/run_tests.sh
    ```

## 4. Architecture Notes
* Routes are handled by CodeIgniter.
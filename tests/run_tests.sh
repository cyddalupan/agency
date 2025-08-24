#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# The root directory of the project on the host
HOST_PROJECT_ROOT="/workspaces/agency"
# The root directory of the project inside the container
CONTAINER_PROJECT_ROOT="/var/www/html"

# Find and run all test files
for test_file in $(find $HOST_PROJECT_ROOT/tests -name 'test_*.php'); do
    echo "Running $test_file..."
    # Translate the host path to the container path
    container_test_file=${test_file#$HOST_PROJECT_ROOT}
    container_test_file="$CONTAINER_PROJECT_ROOT$container_test_file"

    docker-compose exec web php "$container_test_file"
    echo "OK"
    echo ""
done

echo "All tests passed!"
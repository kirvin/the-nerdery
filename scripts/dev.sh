#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ---
# Functions
# ---

function start_dev_environment() {
  echo "Starting dev environment..."
  docker-compose -f nerdery-app-new/docker-compose.yml up -d
}

function stop_dev_environment() {
  echo "Stopping dev environment..."
  docker-compose -f nerdery-app-new/docker-compose.yml down
}

function run_tests() {
  echo "Running tests..."
  (cd nerdery-app-new && npm exec turbo run test)
}

show_help() {
    echo -e "${BLUE}The Nerdery - Development Helper${NC}"
    echo ""
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  [TODO] setup     - Initial development environment setup"
    echo "  start     - Start all services"
    echo "  stop      - Stop all services"
    echo "  [TODO] restart   - Restart all services"
    echo "  [TODO] logs      - Show logs for all services"
    echo "  [TODO] logs [service] - Show logs for specific service"
    echo "  [TODO] migrate   - Run database migrations"
    echo "  [TODO] shell [service] - Open shell in service container"
    echo "  test      - Run tests for all services"
    echo "  [TODO] test [service] - Run tests for specific service"
    echo "  [TODO] clean     - Clean up containers and volumes"
    echo "  [TODO] status    - Show status of all services"
    echo "  [TODO] build     - Build all services"
    echo "  [TODO] build [service] - Build specific service"
    echo ""
    echo "Services: user-service, content-service, api-gateway, web, mobile"
}

# ---
# Main
# ---

COMMAND=$1

case "$COMMAND" in
  start)
    start_dev_environment
    ;;
  stop)
    stop_dev_environment
    ;;
  test)
    run_tests
    ;;
  *)
    show_help
    exit 1
    ;;
esac

#!/bin/bash

# Frontend Development Helper Script
# Use this instead of installing Node.js on your main machine

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if docker-compose is running
check_docker() {
    if ! docker-compose ps | grep -q "frontend.*Up"; then
        print_error "Frontend container is not running. Start it with: docker-compose up -d"
        exit 1
    fi
}

# Function to show usage
show_usage() {
    echo "ImageShare Frontend Development Helper"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  dev          Start development server (hot reload)"
    echo "  build        Build for production"
    echo "  preview      Preview production build"
    echo "  install      Install/update dependencies"
    echo "  clean        Clean node_modules and reinstall"
    echo "  logs         Show frontend container logs"
    echo "  shell        Open shell in frontend container"
    echo "  test         Run tests (if configured)"
    echo "  lint         Run linting (if configured)"
    echo "  help         Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 dev       # Start development server"
    echo "  $0 build     # Build for production"
    echo "  $0 install   # Install dependencies"
}

# Function to start development server
start_dev() {
    print_status "Starting frontend development server..."
    check_docker
    
    if docker-compose ps | grep -q "frontend.*Up"; then
        print_success "Frontend is already running at http://localhost:5173"
        print_status "Press Ctrl+C to stop the logs (container will keep running)"
        docker-compose logs -f frontend
    else
        print_status "Starting frontend container..."
        docker-compose up -d frontend
        sleep 3
        print_success "Frontend development server started at http://localhost:5173"
        print_status "Showing logs (Press Ctrl+C to stop logs, container keeps running)"
        docker-compose logs -f frontend
    fi
}

# Function to build for production
build_production() {
    print_status "Building frontend for production..."
    check_docker
    
    docker-compose exec frontend npm run build
    print_success "Production build completed in frontend/dist/"
}

# Function to preview production build
preview_production() {
    print_status "Starting production preview server..."
    check_docker
    
    docker-compose exec -d frontend npm run preview -- --host 0.0.0.0 --port 5174
    sleep 3
    print_success "Production preview available at http://localhost:5174"
}

# Function to install dependencies
install_deps() {
    print_status "Installing frontend dependencies..."
    check_docker
    
    docker-compose exec frontend npm install
    print_success "Dependencies installed successfully"
}

# Function to clean and reinstall
clean_install() {
    print_status "Cleaning and reinstalling dependencies..."
    check_docker
    
    docker-compose exec frontend rm -rf node_modules package-lock.json
    docker-compose exec frontend npm install
    print_success "Dependencies cleaned and reinstalled"
}

# Function to show logs
show_logs() {
    check_docker
    docker-compose logs -f frontend
}

# Function to open shell
open_shell() {
    check_docker
    print_status "Opening shell in frontend container..."
    docker-compose exec frontend sh
}

# Function to run tests
run_tests() {
    print_status "Running frontend tests..."
    check_docker
    
    if docker-compose exec frontend npm run test 2>/dev/null; then
        print_success "Tests completed successfully"
    else
        print_warning "No test script found or tests failed"
        print_status "You can run tests manually with: $0 shell"
    fi
}

# Function to run linting
run_lint() {
    print_status "Running frontend linting..."
    check_docker
    
    if docker-compose exec frontend npm run lint 2>/dev/null; then
        print_success "Linting completed successfully"
    else
        print_warning "No lint script found or linting failed"
        print_status "You can run linting manually with: $0 shell"
    fi
}

# Main script logic
case "${1:-help}" in
    "dev")
        start_dev
        ;;
    "build")
        build_production
        ;;
    "preview")
        preview_production
        ;;
    "install")
        install_deps
        ;;
    "clean")
        clean_install
        ;;
    "logs")
        show_logs
        ;;
    "shell")
        open_shell
        ;;
    "test")
        run_tests
        ;;
    "lint")
        run_lint
        ;;
    "help"|*)
        show_usage
        ;;
esac

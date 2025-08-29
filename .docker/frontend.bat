@echo off
REM Frontend Development Helper Script for Windows
REM Use this instead of installing Node.js on your main machine

setlocal enabledelayedexpansion

REM Check if command is provided
if "%1"=="" goto help

REM Check if docker-compose is running
docker-compose ps | findstr "frontend.*Up" >nul
if errorlevel 1 (
    echo [ERROR] Frontend container is not running. Start it with: docker-compose up -d
    exit /b 1
)

REM Route to appropriate function
if "%1"=="dev" goto dev
if "%1"=="build" goto build
if "%1"=="preview" goto preview
if "%1"=="install" goto install
if "%1"=="clean" goto clean
if "%1"=="logs" goto logs
if "%1"=="shell" goto shell
if "%1"=="test" goto test
if "%1"=="lint" goto lint
if "%1"=="help" goto help
goto help

:dev
echo [INFO] Starting frontend development server...
docker-compose logs -f frontend
goto end

:build
echo [INFO] Building frontend for production...
docker-compose exec frontend npm run build
echo [SUCCESS] Production build completed in frontend/dist/
goto end

:preview
echo [INFO] Starting production preview server...
docker-compose exec -d frontend npm run preview -- --host 0.0.0.0 --port 5174
timeout /t 3 /nobreak >nul
echo [SUCCESS] Production preview available at http://localhost:5174
goto end

:install
echo [INFO] Installing frontend dependencies...
docker-compose exec frontend npm install
echo [SUCCESS] Dependencies installed successfully
goto end

:clean
echo [INFO] Cleaning and reinstalling dependencies...
docker-compose exec frontend rmdir /s /q node_modules
docker-compose exec frontend del package-lock.json
docker-compose exec frontend npm install
echo [SUCCESS] Dependencies cleaned and reinstalled
goto end

:logs
docker-compose logs -f frontend
goto end

:shell
echo [INFO] Opening shell in frontend container...
docker-compose exec frontend sh
goto end

:test
echo [INFO] Running frontend tests...
docker-compose exec frontend npm run test 2>nul
if errorlevel 1 (
    echo [WARNING] No test script found or tests failed
    echo [INFO] You can run tests manually with: %0 shell
) else (
    echo [SUCCESS] Tests completed successfully
)
goto end

:lint
echo [INFO] Running frontend linting...
docker-compose exec frontend npm run lint 2>nul
if errorlevel 1 (
    echo [WARNING] No lint script found or linting failed
    echo [INFO] You can run linting manually with: %0 shell
) else (
    echo [SUCCESS] Linting completed successfully
)
goto end

:help
echo ImageShare Frontend Development Helper
echo.
echo Usage: %0 [COMMAND]
echo.
echo Commands:
echo   dev          Start development server (hot reload)
echo   build        Build for production
echo   preview      Preview production build
echo   install      Install/update dependencies
echo   clean        Clean node_modules and reinstall
echo   logs         Show frontend container logs
echo   shell        Open shell in frontend container
echo   test         Run tests (if configured)
echo   lint         Run linting (if configured)
echo   help         Show this help message
echo.
echo Examples:
echo   %0 dev       # Start development server
echo   %0 build     # Build for production
echo   %0 install   # Install dependencies
echo.
echo Note: Make sure Docker containers are running first with: docker-compose up -d
goto end

:end
endlocal

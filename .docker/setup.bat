@echo off
REM ImageShare Docker Setup Script for Windows

echo 🚀 Setting up ImageShare Docker environment...

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not running. Please start Docker first.
    pause
    exit /b 1
)

REM Check if Docker Compose is available
docker-compose version >nul 2>&1
if %errorlevel% equ 0 (
    set DOCKER_COMPOSE=docker-compose
) else (
    docker compose version >nul 2>&1
    if %errorlevel% equ 0 (
        set DOCKER_COMPOSE=docker compose
    ) else (
        echo ❌ Docker Compose is not available.
        pause
        exit /b 1
    )
)

REM Start services
echo 📦 Starting Docker services...
%DOCKER_COMPOSE% up -d

REM Wait for services to be ready
echo ⏳ Waiting for services to start...
timeout /t 10 /nobreak >nul

REM Copy environment file
if not exist "..\.env.local" (
    echo 📄 Copying environment configuration...
    copy ..\env ..\env.local
    echo ✅ Environment file created: .env.local
)

REM Install PHP dependencies
echo 📦 Installing PHP dependencies...
%DOCKER_COMPOSE% exec php composer install

REM Set permissions
echo 🔐 Setting proper permissions...
%DOCKER_COMPOSE% exec php chown -R www-data:www-data /var/www/html
%DOCKER_COMPOSE% exec php chmod -R 777 /var/www/html/var
%DOCKER_COMPOSE% exec php chmod -R 777 /var/www/html/images

REM Create images directory
%DOCKER_COMPOSE% exec php mkdir -p /var/www/html/images

REM Create database and run migrations
echo 🗃️ Creating database and running migrations...
%DOCKER_COMPOSE% exec php php bin/console doctrine:database:create --if-not-exists
%DOCKER_COMPOSE% exec php php bin/console doctrine:migrations:migrate --no-interaction

echo.
echo 🎉 Setup completed successfully!
echo.
echo 📍 Access points:
echo    🌐 Application: http://localhost:8080
echo    🗃️  phpMyAdmin: http://localhost:8082
echo    📧 Mailpit:     http://localhost:8026
echo.
echo 🔧 Next steps:
echo    1. Login with default admin account:
echo       Email: admin@imageshare.com
echo       Password: admin123
echo    2. Register additional user accounts
echo    3. Start uploading and sharing images!
echo.
echo 📚 Useful commands:
echo    View logs: %DOCKER_COMPOSE% logs -f
echo    Stop services: %DOCKER_COMPOSE% down
echo    Restart: %DOCKER_COMPOSE% restart
echo.
echo Happy image sharing! 📸
echo.
pause

#!/bin/bash

# ImageShare Docker Setup Script
echo "🚀 Setting up ImageShare Docker environment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Check if Docker Compose is available
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose is not available."
    exit 1
fi

# Start services
echo "📦 Starting Docker services..."
if command -v docker-compose &> /dev/null; then
    docker-compose up -d
else
    docker compose up -d
fi

# Wait for services to be ready
echo "⏳ Waiting for services to start..."
sleep 10

# Copy environment file
if [ ! -f "../.env.local" ]; then
    echo "📄 Copying environment configuration..."
    cp ../.env ../.env.local
    echo "✅ Environment file created: .env.local"
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if command -v docker-compose &> /dev/null; then
    docker-compose exec php composer install
else
    docker compose exec php composer install
fi

# Set permissions
echo "🔐 Setting proper permissions..."
if command -v docker-compose &> /dev/null; then
    docker-compose exec php chown -R www-data:www-data /var/www/html
    docker-compose exec php chmod -R 777 /var/www/html/var
    docker-compose exec php chmod -R 777 /var/www/html/images
else
    docker compose exec php chown -R www-data:www-data /var/www/html
    docker compose exec php chmod -R 777 /var/www/html/var
    docker compose exec php chmod -R 777 /var/www/html/images
fi

# Create images directory if it doesn't exist
if command -v docker-compose &> /dev/null; then
    docker-compose exec php mkdir -p /var/www/html/images
else
    docker compose exec php mkdir -p /var/www/html/images
fi

# Create database and run migrations
echo "🗃️ Creating database and running migrations..."
if command -v docker-compose &> /dev/null; then
    docker-compose exec php php bin/console doctrine:database:create --if-not-exists
    docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
else
    docker compose exec php php bin/console doctrine:database:create --if-not-exists
    docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
fi

echo ""
echo "🎉 Setup completed successfully!"
echo ""
echo "📍 Access points:"
echo "   🌐 Application: http://localhost:8080"
echo "   🗃️  phpMyAdmin: http://localhost:8082"
echo "   📧 Mailpit:     http://localhost:8026"
echo ""
echo "🔧 Next steps:"
echo "   1. Login with default admin account:"
echo "      Email: admin@imageshare.com"
echo "      Password: admin123"
echo "   2. Register additional user accounts"
echo "   3. Start uploading and sharing images!"
echo ""
echo "📚 Useful commands:"
echo "   View logs: docker-compose logs -f"
echo "   Stop services: docker-compose down"
echo "   Restart: docker-compose restart"
echo ""
echo "Happy image sharing! 📸"

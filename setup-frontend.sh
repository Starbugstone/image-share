#!/bin/bash

echo "🚀 Setting up ImageShare Vue.js Frontend..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ docker-compose is not installed. Please install it first."
    exit 1
fi

echo "📦 Starting Docker containers..."
docker-compose up -d

echo "⏳ Waiting for containers to be ready..."
sleep 10

echo "🔧 Installing frontend dependencies..."
docker exec -it imageshare_node npm install

echo "🎯 Starting development server..."
echo ""
echo "✅ Setup complete! Your frontend should be available at:"
echo "   🌐 Frontend: http://localhost:5173"
echo "   🔧 Backend: http://localhost:8080"
echo "   🗄️  Database: http://localhost:8082 (PHPMyAdmin)"
echo ""
echo "📝 To start development:"
echo "   docker exec -it imageshare_node npm run dev"
echo ""
echo "📝 To build for production:"
echo "   docker exec -it imageshare_node npm run build"
echo ""
echo "🎉 Happy coding!"

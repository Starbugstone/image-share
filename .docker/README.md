# ImageShare Docker Development Environment

This folder contains the complete Docker setup for ImageShare development, including a dedicated frontend container for Vue.js development.

## 🚀 Quick Start

```bash
# Start all services
docker-compose up -d

# Access your application
# Frontend: http://localhost:5173 (Vue.js dev server)
# Backend: http://localhost:8080 (Symfony)
# Database: http://localhost:8082 (PHPMyAdmin)
```

## 🐳 Services

### Frontend Container
- **Purpose**: Vue.js development and compilation
- **Port**: 5173 (dev server), 5174 (preview)
- **Features**: Hot reload, dependency management, build tools
- **No Node.js installation needed** on your main machine!

### PHP Container
- **Purpose**: Symfony backend application
- **Port**: 8080
- **Features**: Composer, Symfony CLI, extensions

### Database Services
- **MariaDB**: Database server
- **PHPMyAdmin**: Database management interface
- **Mailpit**: Email testing and debugging

## 🛠️ Frontend Development

### Using Helper Scripts (Recommended)

**Linux/Mac:**
```bash
./frontend.sh dev      # Start development server
./frontend.sh build    # Build for production
./frontend.sh install  # Install dependencies
./frontend.sh help     # Show all commands
```

**Windows:**
```cmd
frontend.bat dev       # Start development server
frontend.bat build     # Build for production
frontend.bat install   # Install dependencies
frontend.bat help      # Show all commands
```

### Manual Docker Commands

```bash
# Start development server
docker-compose exec frontend npm run dev

# Build for production
docker-compose exec frontend npm run build

# Install dependencies
docker-compose exec frontend npm install

# Access container shell
docker-compose exec frontend sh
```

## 📁 File Structure

```
.docker/
├── docker-compose.yml      # Multi-service orchestration
├── Dockerfile.php          # PHP container definition
├── Dockerfile.frontend     # Node.js frontend container
├── frontend.sh             # Linux/Mac frontend helper
├── frontend.bat            # Windows frontend helper
├── php/                    # PHP configuration
├── mariadb/                # Database initialization
└── README.md               # This file
```

## 🔧 Configuration

### Environment Variables
- **Frontend**: Configured in `docker-compose.yml`
- **Backend**: Uses `.env.local` from project root
- **Database**: Configured in `docker-compose.yml`

### Ports
- **5173**: Frontend development server
- **8080**: Backend application
- **8082**: Database admin (PHPMyAdmin)
- **8026**: Email testing (Mailpit)

## 🚨 Troubleshooting

### Frontend Container Issues
```bash
# Check container status
docker-compose ps frontend

# View logs
docker-compose logs frontend

# Restart container
docker-compose restart frontend

# Rebuild container
docker-compose up -d --build frontend
```

### Common Issues
1. **Port conflicts**: Ensure ports 5173, 8080, 8082 are available
2. **Permission issues**: Run `docker-compose down -v` and restart
3. **Dependency issues**: Use `./frontend.sh clean` to reinstall

### Reset Everything
```bash
# Stop and remove all containers and volumes
docker-compose down -v

# Rebuild and start fresh
docker-compose up -d --build
```

## 📚 Documentation

- **[Main Documentation](../.docs/README.md)** - Complete project documentation
- **[Frontend Guide](../.docs/frontend-readme.md)** - Vue.js development guide
- **[Migration Tasks](../.docs/FRONTEND_MIGRATION_TASKS.md)** - Development roadmap

---

**Note**: This Docker setup provides a complete development environment without requiring Node.js, PHP, or database software on your main machine.

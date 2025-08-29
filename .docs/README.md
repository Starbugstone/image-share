# ImageShare Project Documentation

Welcome to the ImageShare project documentation. This folder contains comprehensive documentation for all aspects of the project.

## 📚 Documentation Index

### 🚀 Frontend Development
- **[Frontend Setup Guide](frontend-readme.md)** - Complete Vue.js frontend setup and development guide
- **[Frontend Migration Tasks](FRONTEND_MIGRATION_TASKS.md)** - Detailed task list for Twig to Vue.js migration

### 🏗️ Project Architecture
- **[Project Setup](FRONTEND_SETUP.md)** - Original project setup and configuration documentation
- **[Profile System](PROFILE_SYSTEM_README.md)** - User profile system documentation
- **[Sharing Improvements](SHARING_IMPROVEMENTS.md)** - Image sharing system enhancements

### 📋 Quick Reference

#### Frontend Development
- **Tech Stack**: Vue 3, Vite, Pinia, Vue Router, Axios
- **Development Server**: `npm run dev` (runs on http://localhost:5173)
- **Build Command**: `npm run build`
- **Docker Setup**: `docker-compose up -d`
- **Frontend Container**: Dedicated Node.js container for compilation

#### Backend Development
- **Framework**: Symfony 6
- **Database**: MariaDB
- **Development Server**: http://localhost:8080
- **Database Admin**: http://localhost:8082 (PHPMyAdmin)

## 🚀 Getting Started

### Prerequisites
- Docker and Docker Compose
- Git

### Quick Start with Docker
1. **Clone and navigate to project**
   ```bash
   git clone <repository-url>
   cd image-share
   ```

2. **Start the development environment**
   ```bash
   cd .docker
   docker-compose up -d
   ```

3. **Access the application**
   - Frontend: http://localhost:5173 (Vue.js dev server)
   - Backend: http://localhost:8080 (Symfony)
   - Database: http://localhost:8082 (PHPMyAdmin)

### Frontend Development (No Node.js Required!)

The project includes a dedicated Docker container for frontend development, so you don't need to install Node.js on your main machine.

#### Using Helper Scripts

**Linux/Mac:**
```bash
cd .docker
./frontend.sh dev      # Start development server
./frontend.sh build    # Build for production
./frontend.sh install  # Install dependencies
./frontend.sh help     # Show all commands
```

**Windows:**
```cmd
cd .docker
frontend.bat dev       # Start development server
frontend.bat build     # Build for production
frontend.bat install   # Install dependencies
frontend.bat help      # Show all commands
```

#### Manual Docker Commands

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

## 📁 Project Structure

```
image-share/
├── .docs/                    # Project documentation
├── .docker/                  # Docker configuration
│   ├── docker-compose.yml   # Multi-service setup
│   ├── Dockerfile.php       # PHP container
│   ├── Dockerfile.frontend  # Node.js frontend container
│   ├── frontend.sh          # Linux/Mac frontend helper
│   └── frontend.bat         # Windows frontend helper
├── frontend/                 # Vue.js frontend application
├── src/                      # Symfony backend source code
├── templates/                # Twig templates (legacy)
├── public/                   # Public assets
├── docker-compose.yml        # Docker environment configuration
└── README.md                 # Main project README
```

## 🔧 Development Workflow

### Frontend Development
1. **Start containers**: `docker-compose up -d`
2. **Use helper scripts**: `./frontend.sh dev` (Linux/Mac) or `frontend.bat dev` (Windows)
3. **Edit code**: Work in `frontend/` directory - changes auto-reload
4. **Build**: Use `./frontend.sh build` for production builds
5. **No Node.js installation needed** on your main machine!

### Backend Development
1. Work in the `src/` directory
2. Follow Symfony best practices
3. Use Doctrine for database operations
4. Implement proper validation and error handling

## 🐳 Docker Services

### Frontend Container
- **Base**: Node.js 18 Alpine
- **Purpose**: Vue.js development and compilation
- **Ports**: 5173 (dev server), 5174 (preview)
- **Features**: Hot reload, dependency management, build tools

### PHP Container
- **Base**: PHP 8.2 Apache
- **Purpose**: Symfony backend application
- **Port**: 8080
- **Features**: Composer, Symfony CLI, extensions

### Database Services
- **MariaDB**: Database server (port 3306)
- **PHPMyAdmin**: Database management (port 8082)
- **Mailpit**: Email testing (port 8026)

## 📖 Documentation Standards

- **File Naming**: Use kebab-case for file names
- **Content**: Write clear, concise documentation
- **Code Examples**: Include working code snippets
- **Updates**: Keep documentation current with code changes

## 🤝 Contributing to Documentation

1. **Update existing docs** when making code changes
2. **Add new docs** for new features or components
3. **Follow the established format** and style
4. **Include examples** and code snippets where helpful

## 📞 Support & Questions

- **Documentation Issues**: Create GitHub issue with `documentation` label
- **Code Questions**: Use GitHub Discussions
- **Feature Requests**: Submit through GitHub Issues

---

**Last Updated**: December 2024  
**Maintainer**: Development Team  
**Version**: 1.0.0

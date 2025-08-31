# Technology Stack & Build System

## Backend Stack

- **PHP**: 8.2+ (minimum 8.1)
- **Framework**: Symfony 6.4 LTS
- **Database**: MariaDB 10.11 with Doctrine ORM
- **Authentication**: Symfony Security Bundle with custom authenticators
- **File Uploads**: VichUploaderBundle for secure file handling
- **Admin Interface**: EasyAdminBundle 4.0+
- **Email**: Symfony Mailer with Mailpit for development

## Frontend Stack

- **Framework**: Vue.js 3.4+
- **Build Tool**: Vite 5.0+
- **State Management**: Pinia 2.1+
- **HTTP Client**: Axios 1.6+
- **Icons**: Lucide Vue Next
- **Testing**: Vitest with Vue Test Utils
- **Linting**: ESLint with Vue plugin

## Development Environment

- **Containerization**: Docker with Docker Compose
- **Web Server**: Apache (in PHP container)
- **Database Admin**: phpMyAdmin
- **Email Testing**: Mailpit web interface
- **Asset Building**: Webpack Encore + Vite (dual setup)

## Common Commands

### Docker Development
```bash
# Start all services
cd .docker && docker-compose up -d

# Setup project (automated)
cd .docker && ./setup.sh  # Linux/Mac
cd .docker && setup.bat   # Windows

# Frontend development (no Node.js installation required!)
cd .docker && ./frontend.sh dev  # Linux/Mac
cd .docker && frontend.bat dev   # Windows
```

### Symfony Commands
```bash
# Inside PHP container
docker-compose exec php bash

# Database operations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Cache operations
php bin/console cache:clear
php bin/console cache:warmup

# User management
php bin/console app:user-admin create
php bin/console app:user-admin promote

# Profile system setup
php bin/console app:setup-profile-images
php bin/console app:make-usernames-unique
```

### Frontend Development (Docker-based)
```bash
# Manual Docker commands
docker-compose exec frontend npm run dev
docker-compose exec frontend npm run build
docker-compose exec frontend npm install
docker-compose exec frontend sh  # Access container shell

# Helper scripts (preferred)
./frontend.sh dev      # Start dev server
./frontend.sh build    # Build for production
./frontend.sh install  # Install dependencies
```

### Testing
```bash
# PHP tests
docker-compose exec php php bin/phpunit

# Frontend tests
docker-compose exec frontend npm test
docker-compose exec frontend npm run test:ui
docker-compose exec frontend npm run test:run
```

### Migration & Setup
```bash
# Apply database migrations
php bin/console doctrine:migrations:migrate

# Make usernames unique (required for sharing)
php bin/console app:make-usernames-unique

# Setup profile image directories
php bin/console app:setup-profile-images
```

## Key Dependencies

### Backend
- `doctrine/orm`: Database ORM
- `symfony/security-bundle`: Authentication/authorization
- `vich/uploader-bundle`: File upload handling
- `easycorp/easyadmin-bundle`: Admin interface
- `symfonycasts/verify-email-bundle`: Email verification

### Frontend
- `vue-router`: Client-side routing
- `@vueuse/core`: Vue composition utilities
- `lucide-vue-next`: Icon library

## Environment Configuration

- `.env`: Main application config
- `.env.local`: Local overrides (auto-created by setup)
- `.docker/.env.docker`: Docker-specific settings (optional)
- `frontend/env.development`: Frontend development config
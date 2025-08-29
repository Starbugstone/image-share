# ImageShare - Private Image Sharing Platform

A secure, private image sharing platform built with Symfony 6.4 and PHP 8.2. Share images and albums with specific users while maintaining complete privacy and granular access control.

![ImageShare](https://img.shields.io/badge/ImageShare-Private%20Image%20Sharing-blue)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Symfony](https://img.shields.io/badge/Symfony-6.4-black)
![Docker](https://img.shields.io/badge/Docker-Ready-blue)
![MariaDB](https://img.shields.io/badge/MariaDB-10.11-blue)

## 🔒 Security-First Design

**Image Privacy is Paramount**: Images are stored outside the public directory and served through secure, permission-controlled endpoints. No one can access your images without explicit permission.

### Security Features
- ✅ **Secure Storage**: Images stored in `/images` (not `/public/images`)
- ✅ **Permission-Based Access**: Every image request is authenticated and authorized
- ✅ **Granular Sharing**: Share with specific users only (verified users only)
- ✅ **Private Comments**: Comments visible only to owner and shared users
- ✅ **No Public Access**: Direct file access is completely blocked
- ✅ **CSRF Protection**: Enabled on all forms
- ✅ **Secure Passwords**: Minimum 8 characters, bcrypt hashing
- ✅ **Email Verification**: Required for new accounts
- ✅ **Session Security**: Secure cookies with proper configuration
- ✅ **Input Validation**: All user input validated and sanitized
- ✅ **Access Control**: Role-based permissions with detailed controls

### Security Configuration
```yaml
# config/packages/security.yaml
access_control:
  - { path: ^/dashboard, roles: ROLE_USER }
  - { path: ^/images/upload, roles: ROLE_USER }
  - { path: ^/share, roles: ROLE_USER }
  - { path: ^/secure-image, roles: ROLE_USER }
```

### File Upload Security
```php
// Secure file constraints
new File([
    'maxSize' => '10M',
    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'mimeTypesMessage' => 'Please upload a valid image'
])
```

### Sharing Security
```php
// Only share with verified users
if ($user && $user !== $this->getUser() && $user->isVerified()) {
    // Sharing logic here
}
```

## ⚙️ Environment Configuration

The application uses a flexible environment configuration system:

- **`.env`** - Main application configuration (at project root)
- **`.env.local`** - Local overrides (created by setup scripts)
- **`.docker/.env.docker`** - Docker development customizations (ports, container names, resource limits)

### How it Works

1. **Main Configuration**: `.env` contains default application settings
2. **Local Overrides**: `.env.local` contains your local customizations
3. **Docker Customization**: `.docker/.env.docker` contains Docker-specific settings (optional)
4. **Automatic Loading**: Docker Compose automatically loads `.env.docker` for development customizations

This allows the same application to run both locally and in Docker with appropriate configurations.

## 🚀 Quick Start with Docker

### Prerequisites
- Docker and Docker Compose
- Git

### Automated Setup (Recommended)
```bash
# Clone the repository
git clone <your-repo-url>
cd image-share

# Run automated setup
cd .docker
./setup.sh          # Linux/Mac
# OR
setup.bat           # Windows

# That's it! Your application is ready
```

### Manual Setup
```bash
# Start Docker services
cd .docker
docker-compose up -d

# Copy and configure environment (if not already done)
cp ../.env ../.env.local
# Docker will automatically load .env.docker for port configurations

# Install dependencies
docker-compose exec php composer install

# Create database and run migrations
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# Set permissions
docker-compose exec php chmod -R 777 /var/www/html/var /var/www/html/images
```

## 📚 Documentation

For comprehensive documentation, development guides, and technical details, see the [`.docs/`](.docs/) folder:

- **[Frontend Development](.docs/frontend-readme.md)** - Vue.js setup, development workflow, and component guide
- **[Migration Tasks](.docs/FRONTEND_MIGRATION_TASKS.md)** - Twig to Vue.js migration roadmap
- **[Project Setup](.docs/FRONTEND_SETUP.md)** - Original project configuration and setup
- **[Profile System](.docs/PROFILE_SYSTEM_README.md)** - User profile system documentation
- **[Sharing Improvements](.docs/SHARING_IMPROVEMENTS.md)** - Image sharing system enhancements
- **[Documentation Index](.docs/README.md)** - Complete documentation overview

## 🚀 Frontend Development (Docker-Powered)

**No Node.js installation required!** The project includes a dedicated Docker container for frontend development:

- **Hot Reload**: Changes automatically refresh in the browser
- **Helper Scripts**: Simple commands for common tasks
- **Clean Environment**: No Node.js pollution on your main machine
- **Consistent Setup**: Same environment across all team members

**Quick Start:**
```bash
cd .docker
docker-compose up -d                    # Start all services
./frontend.sh dev                      # Start frontend dev server (Linux/Mac)
# OR
frontend.bat dev                       # Start frontend dev server (Windows)
```

Access your Vue.js app at: **http://localhost:5173**

## 📍 Access Points

After setup, access your application at:

- **🌐 Application**: http://localhost:8080
- **🗃️ Database Admin**: http://localhost:8082
  - Username: `imageshare_user`
  - Password: `imageshare_pass`
- **📧 Email Testing**: http://localhost:8026

## 👤 Default Admin Account

Login with the pre-created admin account:
- **Email**: `admin@imageshare.com`
- **Password**: `admin123`
- **Username**: `admin`

⚠️ **Important**: Change the default admin password after first login!

## ✨ Features

### 🖼️ Image Management
- **Upload Images**: Support for JPEG, PNG, GIF, WebP (max 100MB)
- **Organize Albums**: Create public or private albums
- **Image Metadata**: Title, description, and tags
- **Secure Storage**: Images stored outside public access

### 🔐 Private Sharing System
- **User-Based Sharing**: Share images with specific users only
- **Album Sharing**: Share entire albums with granular permissions
- **Access Control**: Owners control who can view their content
- **Permission Management**: Add/remove access for individual users

### 💬 Private Comments
- **Contextual Discussions**: Comment on shared images
- **Privacy First**: Comments visible only to owner and shared user
- **Threaded Conversations**: Maintain discussion history
- **Secure Communication**: No cross-user comment visibility

### 👥 User Management
- **Registration**: Email verification system
- **Authentication**: Secure login with role-based access
- **Profile Management**: User profiles and settings
- **Admin Panel**: EasyAdmin interface for administration

### 🛠️ Technical Features
- **Docker Ready**: Complete containerized environment
- **Database**: MariaDB with Doctrine ORM
- **Email**: Mailpit for development email testing
- **Security**: Symfony Security component with custom authenticators
- **File Upload**: VichUploaderBundle for secure file handling

## 🏗️ Architecture

### Database Schema
```sql
📊 imageshare_db

├── 👤 user (id, email, username, password, roles, is_verified)
├── 📁 album (id, user_id, name, description, is_public)
├── 🖼️ image (id, user_id, album_id, title, description, image_name)
├── 🔗 share (id, image_id, album_id, shared_by_id, shared_with_id, message)
└── 💬 comment (id, image_id, share_id, author_id, content)
```

### Security Flow
1. **Image Upload**: Stored in secure `/images` directory
2. **Access Request**: All image requests go through `/secure-image/{id}`
3. **Permission Check**: Controller verifies user has access rights
4. **Serve Image**: Only authorized users can view the image
5. **No Direct Access**: Direct file URLs return 403 Forbidden

## 🎯 Usage Guide

### 1. Register/Login
- Visit http://localhost:8080
- Register a new account or login with admin credentials
- Email verification required for new accounts

### 2. Upload Images
- Click "Upload Image" from dashboard
- Select image file (JPEG, PNG, GIF, WebP)
- Add title, description, and optional album
- Upload with secure storage

### 3. Create Albums
- Navigate to "My Albums"
- Click "Create Album"
- Set name, description, and privacy level
- Add images to organize your collection

### 4. Share Content
- Go to image or album view
- Click "Share" button
- Enter usernames (comma-separated)
- Add optional message
- Recipients get access notification

### 5. Manage Access
- View "My Shares" to see shared content
- Manage who has access to your images
- Remove access for specific users
- Monitor sharing activity

## 🔧 Development

### Project Structure
```
image-share/
├── .docker/                 # Docker configuration
│   ├── docker-compose.yml  # Multi-service setup
│   ├── Dockerfile.php      # PHP container
│   ├── setup.sh           # Linux/Mac setup script
│   └── setup.bat          # Windows setup script
├── config/                 # Symfony configuration
├── public/                 # Web accessible files
├── src/                    # Application source code
│   ├── Controller/        # Route controllers
│   ├── Entity/           # Doctrine entities
│   ├── Form/             # Form classes
│   ├── Repository/       # Data access layer
│   ├── Security/         # Authentication & authorization
│   └── Migrations/       # Database migrations
├── templates/            # Twig templates
├── images/               # Secure image storage (auto-created)
└── vendor/               # Composer dependencies
```

### Key Components

#### Controllers
- `HomeController` - Landing page
- `SecurityController` - Login/logout
- `RegistrationController` - User registration
- `DashboardController` - User dashboard
- `ImageController` - Image management
- `AlbumController` - Album management
- `ShareController` - Sharing functionality
- `CommentController` - Comment system
- `ImageServeController` - Secure image serving

#### Entities
- `User` - User accounts and authentication
- `Image` - Image metadata and file handling
- `Album` - Image collections
- `Share` - Sharing relationships
- `Comment` - Private comments

### Security Implementation

#### Image Security
```php
// Before: Public access
GET /public/images/photo.jpg  // ❌ Anyone can access

// After: Secure access
GET /secure-image/123         // ✅ Permission required
```

#### Access Control
- **Owner Access**: Users can always access their own content
- **Shared Access**: Users can access specifically shared content
- **Public Access**: Public albums are accessible to all users
- **Denied Access**: All other requests are blocked

## 🐳 Docker Services

### PHP Container
- **Base**: `php:8.2-apache`
- **Extensions**: pdo_mysql, mbstring, gd, zip
- **Tools**: Composer, Symfony CLI
- **Port**: 8080

### MariaDB Container
- **Version**: 10.11
- **Database**: imageshare_db
- **Port**: 3306
- **Persistent**: Data stored in Docker volumes

### phpMyAdmin Container
- **Interface**: Web-based database management
- **Port**: 8081
- **Access**: Connects to MariaDB container

### Mailpit Container
- **Purpose**: Email testing and debugging
- **Ports**: 8025 (web), 1025 (SMTP)
- **Interface**: Modern web UI for email testing

## 🔍 Troubleshooting

### Common Issues

#### Database Connection Failed
```bash
# Check if MariaDB is running
docker-compose ps

# View database logs
docker-compose logs mariadb

# Reset database
docker-compose exec mariadb mysql -u root -p -e "DROP DATABASE imageshare_db; CREATE DATABASE imageshare_db;"
docker-compose exec php php bin/console doctrine:migrations:migrate
```

#### Permission Issues
```bash
# Fix file permissions
docker-compose exec php chown -R www-data:www-data /var/www/html
docker-compose exec php chmod -R 777 /var/www/html/var /var/www/html/images
```

#### Images Not Displaying
1. Check if images directory exists: `docker-compose exec php ls -la /var/www/html/images`
2. Verify image permissions: `docker-compose exec php ls -la /var/www/html/images/`
3. Check web server logs: `docker-compose logs php`

#### Email Not Working
1. Visit Mailpit: http://localhost:8025
2. Check if Mailpit container is running
3. Verify MAILER_DSN in `.env.local`

### Useful Commands
```bash
# View all logs
docker-compose logs -f

# Access PHP container
docker-compose exec php bash

# Clear Symfony cache
docker-compose exec php php bin/console cache:clear

# View database
docker-compose exec mariadb mysql -u imageshare_user -p imageshare_db

# Restart services
docker-compose restart

# Stop all services
docker-compose down

# Remove all data (including database)
docker-compose down -v
```

## 📝 Development Notes

### Code Style
- Follow PSR-12 coding standards
- Use Symfony best practices
- Document complex logic with comments
- Use type hints for all methods

### Security Considerations
- All user input is validated and sanitized
- CSRF protection enabled on all forms
- Passwords hashed with bcrypt
- Session security with secure cookies
- Image access controlled by business logic

### Performance Optimization
- Database indexes on frequently queried fields
- Image caching with ETags and Last-Modified headers
- Lazy loading for related entities
- Query optimization in repositories

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review Docker logs: `docker-compose logs -f`
3. Check Symfony logs: `docker-compose exec php tail -f var/log/dev.log`
4. Create an issue with detailed information

---

**ImageShare** - Because your memories deserve privacy and security! 🔒📸

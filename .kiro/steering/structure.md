# Project Structure & Organization

## Root Directory Layout

```
image-share/
├── .docker/              # Docker development environment
├── .docs/               # Project documentation
├── .kiro/               # Kiro AI assistant configuration
├── assets/              # Symfony assets (legacy)
├── bin/                 # Symfony console scripts
├── config/              # Symfony configuration
├── frontend/            # Vue.js frontend application
├── public/              # Web-accessible files
├── src/                 # PHP application source
├── templates/           # Twig templates
├── tests/               # PHP unit tests
├── translations/        # i18n files
├── images/              # Secure image storage (auto-created)
└── vendor/              # Composer dependencies
```

## Backend Structure (src/)

### Controllers (`src/Controller/`)
- Follow Symfony controller conventions
- One controller per major feature area
- Use dependency injection for services
- Key controllers: `ImageController`, `AlbumController`, `ShareController`, `ImageServeController`

### Entities (`src/Entity/`)
- Doctrine ORM entities with proper relationships
- Core entities: `User`, `Image`, `Album`, `Share`, `Comment`, `UserProfile`
- Use annotations for ORM mapping
- Include validation constraints

### Repositories (`src/Repository/`)
- Custom query methods for complex database operations
- Follow Doctrine repository patterns
- One repository per entity

### Forms (`src/Form/`)
- Symfony form types for data handling
- Include validation and CSRF protection
- Types: `ImageType`, `AlbumType`, `ProfileType`, `RegistrationFormType`

### Services (`src/Service/`)
- Business logic separated from controllers
- Key service: `SharingService` for access control logic

### Security (`src/Security/`)
- Custom authenticators and security logic
- `LoginFormAuthenticator`, `EmailVerifier`

### Commands (`src/Command/`)
- Console commands for administrative tasks
- `UserAdminCommand` for user management

## Frontend Structure (frontend/src/)

### Views (`frontend/src/views/`)
- Page-level Vue components
- Route-mapped components
- Follow Vue.js single-file component structure

### Components (`frontend/src/components/`)
- Reusable UI components
- Shared functionality across views
- Key components: `AppLayout`, `ImageModal`, `ShareModal`

### Services (`frontend/src/services/`)
- API communication layer
- One service per backend feature area
- Services: `AuthService`, `ImageService`, `AlbumService`, `ShareService`

### Stores (`frontend/src/stores/`)
- Pinia state management
- Global application state
- Stores: `auth.js`, `notifications.js`

## Configuration Structure

### Symfony Config (`config/`)
- `packages/`: Bundle-specific configuration
- `routes/`: Route definitions
- `services.yaml`: Service container configuration
- Environment-specific configs in `packages/`

### Docker Config (`.docker/`)
- `docker-compose.yml`: Multi-service development setup
- `Dockerfile.php`: PHP/Apache container
- `Dockerfile.frontend`: Node.js/Vite container
- Setup scripts for automated initialization

## Templates Structure (`templates/`)

### Organized by feature:
- `album/`: Album-related templates
- `image/`: Image display templates
- `security/`: Login/registration templates
- `dashboard/`: User dashboard templates
- `components/`: Reusable template components
- `base.html.twig`: Main layout template

## File Naming Conventions

### PHP Files
- Controllers: `FeatureController.php` (PascalCase)
- Entities: `EntityName.php` (PascalCase)
- Services: `FeatureService.php` (PascalCase)
- Forms: `FeatureType.php` (PascalCase)

### Vue.js Files
- Components: `ComponentName.vue` (PascalCase)
- Views: `ViewName.vue` (PascalCase)
- Services: `featureService.js` (camelCase)
- Stores: `storeName.js` (camelCase)

### Templates
- Twig files: `feature_action.html.twig` (snake_case)
- Organized in feature-based directories

## Security Considerations

### File Storage
- Images stored in `/images` directory (outside public access)
- Public files only in `/public` directory
- Secure serving through `ImageServeController`

### Access Control
- All image access goes through permission checks
- No direct file serving from storage directory
- User verification required for sharing features

## Development Workflow

### Backend Development
1. Work in `src/` directory following Symfony conventions
2. Use Doctrine migrations for database changes
3. Add tests in `tests/` directory
4. Update services configuration as needed

### Frontend Development
1. Work in `frontend/src/` directory
2. Use Vue.js composition API
3. Maintain service layer for API communication
4. Add component tests using Vitest

### Full-Stack Features
1. Create backend API endpoints
2. Implement frontend service methods
3. Build Vue.js components/views
4. Update routing on both sides
5. Add appropriate tests
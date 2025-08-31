# Frontend Migration Guidelines

## Migration Status: Twig → Vue.js

The ImageShare project is actively migrating from Twig templates to a modern Vue.js 3 frontend. This is a **critical context** for all development work.

## Current Status

### ✅ Completed (Phase 1-4)
- **Core Infrastructure**: API services, state management, routing
- **Authentication System**: Login, registration, user management, email verification
- **Image Management**: Upload, gallery, modal viewing with drag & drop
- **Album System**: Creation, management, gallery views
- **Dashboard**: User overview with statistics and quick actions
- **Sharing System**: Complete share modal with user selection, permissions, and public links
- **Legacy Cleanup**: All Twig templates removed, controllers redirect to Vue.js

### ✅ Completed (Phase 5)
- **Password Reset**: Complete forgot password functionality with email templates
- **User Profiles**: Profile editing, viewing, status management (partial)

### 🚧 In Progress (Phase 6)
- **Advanced Sharing**: Share history and management
- **User Discovery**: Enhanced user search and browsing

### 📋 Pending (Phase 5-9)
- **User Profiles**: Profile editing, viewing, status management
- **Comments System**: Private comments on shared images
- **Enhanced Features**: Notifications, search, discovery
- **Performance**: Optimization, caching, lazy loading
- **Testing**: Comprehensive test coverage
- **Production**: Build optimization, deployment

## Development Approach

### Dual Frontend Architecture
- **Legacy**: Twig templates (being phased out)
- **Modern**: Vue.js 3 SPA (primary development focus)
- **Transition**: Gradual migration, feature by feature

### API-First Development
- All new features must have RESTful API endpoints
- Frontend communicates exclusively through APIs
- Backend serves both Twig (legacy) and Vue.js (modern)

## Key Migration Principles

### 1. Vue.js 3 Standards
- Use **Composition API** with `<script setup>`
- Implement **Pinia** for state management
- Follow **Vue Router 4** patterns
- Use **Vite** for build tooling

### 2. Component Architecture
- **Views**: Page-level components in `frontend/src/views/`
- **Components**: Reusable UI in `frontend/src/components/`
- **Services**: API communication in `frontend/src/services/`
- **Stores**: Global state in `frontend/src/stores/`

### 3. API Integration
- Create service classes for each feature area
- Use Axios for HTTP communication
- Implement proper error handling
- Add loading states and user feedback

### 4. Security Considerations
- Maintain CSRF protection
- Implement JWT authentication
- Validate all user inputs
- Preserve existing security measures

## Development Workflow

### For New Features
1. **Backend First**: Create Symfony controllers and API endpoints
2. **Service Layer**: Build Vue.js service classes
3. **Components**: Develop Vue.js components
4. **Integration**: Connect frontend to backend APIs
5. **Testing**: Add unit and integration tests

### For Existing Features
1. **Analyze**: Study existing Twig implementation
2. **API**: Create/update backend API endpoints
3. **Migrate**: Build Vue.js equivalent
4. **Test**: Ensure feature parity
5. **Replace**: Update routing to use Vue.js version

## Critical Guidelines

### DO
- Always create API endpoints for new features
- Use Vue.js Composition API for all new components
- Implement proper error handling and loading states
- Follow established naming conventions
- Add comprehensive documentation

### DON'T
- Create new Twig templates (migration in progress)
- Mix Twig and Vue.js in the same feature
- Skip API layer for frontend-backend communication
- Ignore existing security patterns
- Break backward compatibility during migration

## File Organization

### Backend (Symfony)
```
src/Controller/     # API endpoints for Vue.js
templates/          # Legacy Twig templates (being phased out)
```

### Frontend (Vue.js)
```
frontend/src/
├── views/          # Page components (AlbumCreate.vue, Dashboard.vue)
├── components/     # Reusable UI (ShareModal.vue, ImageModal.vue)
├── services/       # API communication (AlbumService.js, ImageService.js)
├── stores/         # State management (auth.js, notifications.js)
└── App.vue         # Root component
```

## Testing Strategy

### Backend Testing
- Unit tests for controllers and services
- API endpoint testing
- Database integration tests

### Frontend Testing
- Component testing with Vitest
- Service layer testing
- User workflow testing
- Accessibility testing

## Performance Considerations

### During Migration
- Maintain both Twig and Vue.js versions temporarily
- Optimize API responses for frontend consumption
- Implement proper caching strategies
- Monitor bundle sizes and loading times

### Post-Migration
- Remove unused Twig templates
- Optimize Vue.js bundle
- Implement code splitting
- Add performance monitoring

## Documentation Requirements

### For Each Migrated Feature
- Update API documentation
- Document Vue.js components
- Update user guides
- Record migration decisions

### Migration Tracking
- Update `.docs/FRONTEND_MIGRATION_TASKS.md`
- Mark completed phases
- Document any deviations from plan
- Track performance improvements
# ImageShare Frontend - Vue.js Application

## 🚀 Overview

The ImageShare frontend is a modern Vue.js 3 application built with the Composition API, designed to provide a fast, responsive user experience for image sharing and social networking.

## 🏗️ Architecture

### Tech Stack
- **Vue 3** - Progressive JavaScript framework with Composition API
- **Vite** - Lightning-fast build tool and dev server
- **Vue Router 4** - Official router for Vue.js
- **Pinia** - Intuitive, type-safe state management
- **Axios** - HTTP client for API communication
- **CSS Variables** - Modern styling with dark mode support

### Project Structure
```
frontend/
├── src/
│   ├── components/          # Reusable Vue components
│   ├── views/              # Page-level components
│   ├── stores/             # Pinia state management
│   ├── services/           # API and business logic services
│   ├── assets/             # Static assets
│   ├── App.vue             # Root component
│   ├── main.js             # Application entry point
│   └── style.css           # Global styles
├── public/                 # Public assets
├── index.html              # HTML template
├── package.json            # Dependencies and scripts
├── vite.config.js          # Vite configuration
└── README.md               # This file
```

## 🚀 Getting Started

### Prerequisites
- Node.js 18+ 
- Docker and Docker Compose
- Git

### Quick Start (Docker)
1. **Clone and navigate to project**
   ```bash
   cd image-share
   ```

2. **Start the development environment**
   ```bash
   docker-compose up -d
   ```

3. **Access the application**
   - Frontend: http://localhost:5173
   - Backend: http://localhost:8080
   - Database: http://localhost:8082 (PHPMyAdmin)

### Manual Setup (Alternative)
1. **Install dependencies**
   ```bash
   cd frontend
   npm install
   ```

2. **Start development server**
   ```bash
   npm run dev
   ```

3. **Build for production**
   ```bash
   npm run build
   ```

## 🔧 Development

### Available Scripts
- `npm run dev` - Start development server with hot reload
- `npm run build` - Build for production
- `npm run preview` - Preview production build locally

### Development Workflow
1. **Component Development**: Work in `src/components/` for reusable UI elements
2. **Page Development**: Create new views in `src/views/` and add routes in `main.js`
3. **State Management**: Use Pinia stores in `src/stores/` for application state
4. **API Integration**: Create services in `src/services/` for backend communication
5. **Styling**: Use CSS variables and utility classes in `src/style.css`

### Code Style Guidelines
- Use Vue 3 Composition API with `<script setup>`
- Follow Vue.js style guide conventions
- Use descriptive component and variable names
- Implement proper error handling and loading states
- Write self-documenting code with minimal comments

## 🎯 Current Status

### ✅ Completed Features
- **Authentication System**: Login, registration, and user management
- **Dashboard**: User overview with statistics and quick actions
- **Navigation**: Responsive navigation with user authentication
- **Routing**: Client-side routing with Vue Router
- **State Management**: Pinia stores for authentication and user data
- **Basic UI Components**: Modern, responsive design system

### 🚧 In Progress
- **Image Management**: Upload, gallery, and sharing functionality
- **Album System**: Creation, management, and sharing
- **User Profiles**: Profile viewing and editing
- **Sharing Interface**: Quick share modal and sharing workflows

### 📋 Pending Features
- **Image Gallery**: Full image browsing and management
- **Album Gallery**: Album viewing and organization
- **Advanced Sharing**: User-to-user sharing with permissions
- **Real-time Updates**: WebSocket integration for live updates
- **Messaging System**: User-to-user communication
- **Search & Discovery**: Find users, images, and albums
- **Notifications**: Real-time notification system

## 🔌 API Integration

### Backend Communication
- **Base URL**: http://localhost:8080
- **Authentication**: JWT-based with secure cookie storage
- **API Endpoints**: RESTful API following Symfony conventions
- **Error Handling**: Centralized error handling with user feedback

### Key Services
- `AuthService` - User authentication and session management
- `UserService` - User profile and data management
- `ImageService` - Image upload, retrieval, and management
- `AlbumService` - Album creation and management
- `ShareService` - Content sharing and permissions

## 🎨 Design System

### Color Palette
- **Primary**: Modern blue (#3B82F6)
- **Secondary**: Complementary accent (#8B5CF6)
- **Success**: Green (#10B981)
- **Warning**: Amber (#F59E0B)
- **Error**: Red (#EF4444)
- **Neutral**: Gray scale (#6B7280)

### Typography
- **Headings**: Inter font family for modern readability
- **Body**: System font stack for optimal performance
- **Sizes**: Responsive typography scale

### Components
- **Buttons**: Primary, secondary, and tertiary variants
- **Cards**: Consistent card design for content containers
- **Forms**: Accessible form components with validation
- **Modals**: Overlay components for focused interactions

## 🚀 Performance Features

### Optimization Strategies
- **Code Splitting**: Route-based code splitting for faster initial load
- **Lazy Loading**: Images and components loaded on demand
- **Caching**: Intelligent caching of API responses and user data
- **Bundle Optimization**: Tree-shaking and minification for production

### Monitoring
- **Performance Metrics**: Core Web Vitals tracking
- **Error Tracking**: Centralized error logging and reporting
- **User Analytics**: Usage patterns and feature adoption

## 🔒 Security

### Authentication
- **JWT Tokens**: Secure token-based authentication
- **CSRF Protection**: Cross-site request forgery prevention
- **Secure Storage**: HttpOnly cookies for sensitive data
- **Session Management**: Automatic token refresh and validation

### Data Protection
- **Input Validation**: Client-side and server-side validation
- **XSS Prevention**: Content Security Policy implementation
- **HTTPS**: Secure communication in production

## 🧪 Testing

### Testing Strategy
- **Unit Tests**: Component and service testing with Vitest
- **Integration Tests**: API integration and user workflows
- **E2E Tests**: Full user journey testing
- **Accessibility Tests**: WCAG compliance verification

### Running Tests
```bash
# Unit tests
npm run test:unit

# Integration tests
npm run test:integration

# E2E tests
npm run test:e2e

# All tests
npm run test
```

## 📱 Responsive Design

### Breakpoints
- **Mobile**: 320px - 768px
- **Tablet**: 768px - 1024px
- **Desktop**: 1024px+

### Mobile-First Approach
- Touch-friendly interface elements
- Optimized navigation for small screens
- Responsive image handling
- Progressive enhancement

## 🌙 Dark Mode

### Theme Support
- **Light Theme**: Default bright interface
- **Dark Theme**: Eye-friendly dark interface
- **System Preference**: Automatic theme detection
- **User Preference**: Persistent theme selection

## 🚀 Deployment

### Production Build
1. **Build the application**
   ```bash
   npm run build
   ```

2. **Deploy to web server**
   - Copy `dist/` contents to web server
   - Configure server for SPA routing
   - Set up environment variables

### Environment Configuration
- **Development**: `.env.development`
- **Production**: `.env.production`
- **Docker**: Environment variables in docker-compose.yml

## 🤝 Contributing

### Development Process
1. Create feature branch from `main`
2. Implement feature with tests
3. Update documentation
4. Submit pull request
5. Code review and merge

### Code Review Checklist
- [ ] Vue 3 Composition API usage
- [ ] Component reusability
- [ ] Error handling implementation
- [ ] Responsive design compliance
- [ ] Accessibility standards
- [ ] Test coverage
- [ ] Documentation updates

## 📚 Resources

### Documentation
- [Vue 3 Documentation](https://vuejs.org/)
- [Vite Documentation](https://vitejs.dev/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Vue Router Documentation](https://router.vuejs.org/)

### Design Resources
- [Figma Design System](link-to-figma)
- [Icon Library](https://lucide.dev/)
- [Color Palette](link-to-colors)

## 🆘 Support

### Getting Help
- **Documentation**: Check this README first
- **Issues**: Create GitHub issue with detailed description
- **Discussions**: Use GitHub Discussions for questions
- **Team Chat**: Internal communication channel

### Common Issues
- **Port Conflicts**: Ensure ports 5173, 8080, and 8082 are available
- **Docker Issues**: Restart containers with `docker-compose restart`
- **Build Errors**: Clear node_modules and reinstall dependencies
- **API Errors**: Verify backend is running and accessible

---

**Last Updated**: December 2024  
**Version**: 1.0.0  
**Maintainer**: Development Team

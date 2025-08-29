# ImageShare Vue.js Frontend Setup

This document explains how to set up and run the new Vue.js frontend for ImageShare.

## Overview

The frontend has been converted from Twig templates to a modern Vue.js 3 application using:
- **Vue 3 Composition API** for reactive components
- **Vite** for fast development and building
- **Vue Router** for client-side routing
- **Pinia** for state management
- **Modern CSS** with CSS variables and responsive design

## Prerequisites

- Node.js 18+ 
- Docker and Docker Compose
- The existing Symfony backend running

## Quick Start

### 1. Start the Docker containers

```bash
docker-compose up -d
```

This will start:
- PHP backend on port 8080
- Node.js frontend dev server on port 5173
- MariaDB on port 3307
- PHPMyAdmin on port 8082

### 2. Install frontend dependencies

```bash
# Enter the Node.js container
docker exec -it imageshare_node sh

# Install dependencies
npm install
```

### 3. Start development server

```bash
# In the Node.js container
npm run dev
```

The frontend will be available at: http://localhost:5173

### 4. Build for production

```bash
# In the Node.js container
npm run build
```

This will build the frontend and output to `public/dist/` which is mounted in the PHP container.

## Project Structure

```
frontend/
├── src/
│   ├── components/          # Reusable Vue components
│   ├── views/              # Page components
│   ├── stores/             # Pinia state stores
│   ├── services/           # API services
│   ├── App.vue             # Root component
│   ├── main.js             # Application entry point
│   └── style.css           # Global styles
├── index.html              # HTML template
├── package.json            # Dependencies and scripts
└── vite.config.js          # Vite configuration
```

## Key Features

### Modern UI/UX
- Responsive design that works on all devices
- Smooth animations and transitions
- Modern card-based layout
- Dark mode support
- Mobile-first approach

### Performance Improvements
- Client-side routing (no page reloads)
- Reactive state management
- Optimized image loading
- Lazy loading of components
- Efficient re-rendering with Vue 3

### Enhanced Functionality
- Real-time search and filtering
- Interactive sharing modals
- Better user feedback and notifications
- Improved form validation
- Social media integration ready

## API Integration

The frontend communicates with the Symfony backend through RESTful APIs:

- **Authentication**: JWT-based auth with automatic token refresh
- **Images**: Upload, manage, and share images
- **Albums**: Create and organize photo collections
- **Users**: Profile management and social features
- **Sharing**: Secure sharing with other users

## Development Workflow

1. **Frontend Development**: Work in the `frontend/` directory
2. **Hot Reload**: Vite provides instant updates during development
3. **API Testing**: Backend runs on port 8080, frontend on 5173
4. **Database**: Use PHPMyAdmin on port 8082 for data management

## Building for Production

```bash
npm run build
```

The built files are automatically copied to `public/dist/` and served by the Symfony backend.

## Environment Configuration

Create a `.env` file in the `frontend/` directory:

```env
VITE_API_URL=http://localhost:8080
VITE_APP_TITLE=ImageShare
```

## Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 5173 and 8080 are available
2. **Dependencies**: Run `npm install` in the Node.js container
3. **Build errors**: Check Node.js version (requires 18+)
4. **API errors**: Verify Symfony backend is running on port 8080

### Debug Mode

Enable Vue devtools in your browser for better debugging experience.

## Future Enhancements

- **Messaging System**: Real-time chat between users
- **Push Notifications**: Browser and mobile notifications
- **Advanced Search**: AI-powered image recognition
- **Social Features**: Comments, likes, and follows
- **Mobile App**: React Native companion app

## Support

For issues or questions about the frontend, check the browser console for errors and refer to the Vue.js documentation.

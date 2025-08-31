import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './style.css'

// Import components
import Dashboard from './views/Dashboard.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'
import Profile from './views/Profile.vue'
import ImageUpload from './views/ImageUpload.vue'
import ImageGallery from './views/ImageGallery.vue'
// Lazy-load AlbumCreate to avoid loading on home
const AlbumCreate = () => import('./views/AlbumCreate.vue')
import AlbumGallery from './views/AlbumGallery.vue'
import UserProfile from './views/UserProfile.vue'
import Home from './views/Home.vue'

// Create router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Home, name: 'home' },
    { path: '/login', component: Login, name: 'login' },
    { path: '/register', component: Register, name: 'register' },
    { path: '/password/forgot', component: () => import('./views/ForgotPassword.vue'), name: 'forgot-password' },
    { path: '/password/reset', component: () => import('./views/PasswordReset.vue'), name: 'password-reset' },
    { path: '/verify/email', component: () => import('./views/EmailVerification.vue'), name: 'email-verification' },
    { path: '/dashboard', component: Dashboard, name: 'dashboard' },
    { path: '/profile', component: Profile, name: 'profile' },
    { path: '/upload', component: ImageUpload, name: 'upload' },
    { path: '/images', component: ImageGallery, name: 'images' },
    { path: '/albums/create', component: AlbumCreate, name: 'album-create' },
    { path: '/albums', component: AlbumGallery, name: 'albums' },
    { path: '/user/:username', component: UserProfile, name: 'user-profile' },
    // Add missing routes to fix Vue Router warnings
    { path: '/notifications', component: () => import('./views/Notifications.vue'), name: 'notifications' },
    { path: '/settings', component: () => import('./views/Settings.vue'), name: 'settings' },
    { path: '/about', component: () => import('./views/About.vue'), name: 'about' },
    { path: '/privacy', component: () => import('./views/Privacy.vue'), name: 'privacy' },
    { path: '/terms', component: () => import('./views/Terms.vue'), name: 'terms' },
    { path: '/help', component: () => import('./views/Help.vue'), name: 'help' },
    { path: '/contact', component: () => import('./views/Contact.vue'), name: 'contact' },
    { path: '/faq', component: () => import('./views/FAQ.vue'), name: 'faq' }
  ]
})

// Create Pinia store
const pinia = createPinia()

// Create and mount app
const app = createApp(App)
app.use(router)
app.use(pinia)
app.mount('#app')

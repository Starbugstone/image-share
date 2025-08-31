<template>
  <div class="app-layout">
    <!-- Header Navigation -->
    <header class="header" :class="{ 'header-scrolled': isScrolled }">
      <div class="container">
        <div class="header-content">
          <!-- Logo/Brand -->
          <div class="brand">
            <router-link to="/" class="brand-link">
              <div class="brand-icon">
                <i class="fas fa-images"></i>
              </div>
              <span class="brand-text">ImageShare</span>
            </router-link>
          </div>

          <!-- Desktop Navigation -->
          <nav class="nav-desktop" v-if="!isMobile">
            <router-link to="/" class="nav-link" active-class="active">
              <i class="fas fa-home"></i>
              <span>Home</span>
            </router-link>
            
            <router-link 
              v-if="authStore.isAuthenticated" 
              to="/dashboard" 
              class="nav-link" 
              active-class="active"
            >
              <i class="fas fa-tachometer-alt"></i>
              <span>Dashboard</span>
            </router-link>
            
            <router-link 
              v-if="authStore.isAuthenticated" 
              to="/images" 
              class="nav-link" 
              active-class="active"
            >
              <i class="fas fa-images"></i>
              <span>My Images</span>
            </router-link>
            
            <router-link
              v-if="authStore.isAuthenticated"
              to="/albums"
              class="nav-link"
              active-class="active"
            >
              <i class="fas fa-folder"></i>
              <span>Albums</span>
            </router-link>

            <router-link
              v-if="authStore.isAuthenticated && authStore.hasRole('admin')"
              to="/admin"
              class="nav-link"
              active-class="active"
            >
              <i class="fas fa-user-shield"></i>
              <span>Admin</span>
            </router-link>
          </nav>

          <!-- User Actions -->
          <div class="user-actions">
            <!-- Search -->
            <div class="search-container" v-if="authStore.isAuthenticated">
              <button class="search-toggle" @click="toggleSearch">
                <i class="fas fa-search"></i>
              </button>
              <div class="search-dropdown" :class="{ 'search-open': isSearchOpen }">
                <input 
                  type="text" 
                  placeholder="Search images, albums, users..." 
                  v-model="searchQuery"
                  @input="handleSearch"
                  @focus="isSearchOpen = true"
                  class="search-input"
                />
                <div class="search-results" v-if="searchResults.length > 0">
                  <div 
                    v-for="result in searchResults" 
                    :key="result.id"
                    class="search-result-item"
                    @click="handleSearchResultClick(result)"
                  >
                    <div class="result-icon">
                      <i :class="getResultIcon(result.type)"></i>
                    </div>
                    <div class="result-content">
                      <div class="result-title">{{ result.title }}</div>
                      <div class="result-subtitle">{{ result.subtitle }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notifications -->
            <div class="notifications-container" v-if="authStore.isAuthenticated">
              <button class="notifications-toggle" @click="toggleNotifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" v-if="unreadNotifications > 0">
                  {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                </span>
              </button>
              <div class="notifications-dropdown" :class="{ 'notifications-open': isNotificationsOpen }">
                <div class="notifications-header">
                  <h3>Notifications</h3>
                  <button @click="markAllAsRead" class="mark-all-read">
                    Mark all as read
                  </button>
                </div>
                <div class="notifications-list">
                  <div 
                    v-for="notification in recentNotifications" 
                    :key="notification.id"
                    class="notification-item"
                    :class="{ 'unread': !notification.read_at }"
                    @click="handleNotificationClick(notification)"
                  >
                    <div class="notification-icon">
                      <i :class="getNotificationIcon(notification.type)"></i>
                    </div>
                    <div class="notification-content">
                      <div class="notification-text">{{ notification.message }}</div>
                      <div class="notification-time">{{ formatTime(notification.created_at) }}</div>
                    </div>
                  </div>
                </div>
                <div class="notifications-footer">
                  <router-link to="/notifications" class="view-all">
                    View all notifications
                  </router-link>
                </div>
              </div>
            </div>

            <!-- User Menu -->
            <div class="user-menu-container" v-if="authStore.isAuthenticated">
              <button class="user-menu-toggle" @click="toggleUserMenu">
                <div class="user-avatar">
                  <img 
                    :src="authStore.profileImageUrl" 
                    :alt="authStore.displayName"
                    @error="handleAvatarError"

                  />
                </div>
                <span class="user-name">{{ authStore.displayName }}</span>
                <i class="fas fa-chevron-down"></i>
              </button>
              
              <div class="user-dropdown" :class="{ 'user-open': isUserMenuOpen }">
                <div class="user-dropdown-header">
                  <div class="user-info">
                    <div class="user-avatar-large">
                      <img 
                        :src="authStore.profileImageUrl" 
                        :alt="authStore.displayName"
                        @error="handleAvatarError"
                      />
                    </div>
                    <div class="user-details">
                      <div class="user-name">{{ authStore.displayName }}</div>
                      <div class="user-email">{{ authStore.currentUser?.email }}</div>
                      <div class="user-status">
                        <span class="status-dot" :class="authStore.userStatusClass"></span>
                        {{ authStore.userStatusText }}
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="user-dropdown-menu">
                  <router-link to="/profile" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                  </router-link>
                  
                  <router-link to="/settings" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                  </router-link>
                  
                  <div class="dropdown-divider"></div>
                  
                  <button @click="handleLogout" class="dropdown-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign Out</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Auth Buttons (when not authenticated) -->
            <div class="auth-buttons" v-else>
              <router-link to="/login" class="btn btn-secondary">
                Sign In
              </router-link>
              <router-link to="/register" class="btn btn-primary">
                Get Started
              </router-link>
            </div>
          </div>

          <!-- Mobile Menu Toggle -->
          <button class="mobile-menu-toggle" @click="toggleMobileMenu" v-if="isMobile">
            <i class="fas fa-bars"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" :class="{ 'mobile-open': isMobileMenuOpen }" v-if="isMobile">
      <div class="mobile-nav-header">
        <div class="mobile-user-info" v-if="authStore.isAuthenticated">
          <div class="mobile-user-avatar">
            <img 
              :src="authStore.profileImageUrl" 
              :alt="authStore.displayName"
              @error="handleAvatarError"
            />
          </div>
          <div class="mobile-user-details">
            <div class="mobile-user-name">{{ authStore.displayName }}</div>
            <div class="mobile-user-email">{{ authStore.currentUser?.email }}</div>
          </div>
        </div>
        
        <button class="mobile-close" @click="closeMobileMenu">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <nav class="mobile-nav-menu">
        <router-link to="/" class="mobile-nav-link" @click="closeMobileMenu">
          <i class="fas fa-home"></i>
          <span>Home</span>
        </router-link>
        
        <router-link 
          v-if="authStore.isAuthenticated" 
          to="/dashboard" 
          class="mobile-nav-link" 
          @click="closeMobileMenu"
        >
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </router-link>
        
        <router-link 
          v-if="authStore.isAuthenticated" 
          to="/images" 
          class="mobile-nav-link" 
          @click="closeMobileMenu"
        >
          <i class="fas fa-images"></i>
          <span>My Images</span>
        </router-link>
        
        <router-link
          v-if="authStore.isAuthenticated"
          to="/albums"
          class="mobile-nav-link"
          @click="closeMobileMenu"
        >
          <i class="fas fa-folder"></i>
          <span>Albums</span>
        </router-link>

        <router-link
          v-if="authStore.isAuthenticated && authStore.hasRole('admin')"
          to="/admin"
          class="mobile-nav-link"
          @click="closeMobileMenu"
        >
          <i class="fas fa-user-shield"></i>
          <span>Admin</span>
        </router-link>
        
        <router-link 
          v-if="authStore.isAuthenticated" 
          to="/profile" 
          class="mobile-nav-link" 
          @click="closeMobileMenu"
        >
          <i class="fas fa-user"></i>
          <span>Profile</span>
        </router-link>
        
        <router-link 
          v-if="authStore.isAuthenticated" 
          to="/settings" 
          class="mobile-nav-link" 
          @click="closeMobileMenu"
        >
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </router-link>
        
        <button 
          v-if="authStore.isAuthenticated" 
          @click="handleLogout" 
          class="mobile-nav-link logout"
        >
          <i class="fas fa-sign-out-alt"></i>
          <span>Sign Out</span>
        </button>
        
        <div class="mobile-auth-buttons" v-else>
          <router-link to="/login" class="btn btn-secondary btn-block" @click="closeMobileMenu">
            Sign In
          </router-link>
          <router-link to="/register" class="btn btn-primary btn-block" @click="closeMobileMenu">
            Get Started
          </router-link>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <main class="main-content">
      <slot></slot>
    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h3>ImageShare</h3>
            <p>Share your moments with the world. Connect, organize, and discover amazing photos.</p>
          </div>
          
          <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
              <li><router-link to="/">Home</router-link></li>
              <li><router-link to="/about">About</router-link></li>
              <li><router-link to="/privacy">Privacy</router-link></li>
              <li><router-link to="/terms">Terms</router-link></li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h4>Support</h4>
            <ul>
              <li><router-link to="/help">Help Center</router-link></li>
              <li><router-link to="/contact">Contact Us</router-link></li>
              <li><router-link to="/faq">FAQ</router-link></li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h4>Connect</h4>
            <div class="social-links">
              <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
              <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
              <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
              <a href="#" class="social-link"><i class="fab fa-github"></i></a>
            </div>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p>&copy; 2024 ImageShare. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <!-- Mobile Menu Overlay -->
    <div 
      class="mobile-overlay" 
      :class="{ 'overlay-active': isMobileMenuOpen }" 
      @click="closeMobileMenu"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'
import { useNotificationStore } from '../stores/notifications.js'

// Props
const props = defineProps({
  showHeader: {
    type: Boolean,
    default: true
  },
  showFooter: {
    type: Boolean,
    default: true
  }
})

// Composables
const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Reactive state
const isScrolled = ref(false)
const isMobile = ref(false)
const isMobileMenuOpen = ref(false)
const isUserMenuOpen = ref(false)
const isNotificationsOpen = ref(false)
const isSearchOpen = ref(false)
const searchQuery = ref('')
const searchResults = ref([])

// Computed
const unreadNotifications = computed(() => {
  // TODO: Implement actual notification count
  return 0
})

const recentNotifications = computed(() => {
  // TODO: Implement actual notifications
  return []
})

// Methods
const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  document.body.style.overflow = isMobileMenuOpen.value ? 'hidden' : ''
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
  document.body.style.overflow = ''
}

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value
  if (isUserMenuOpen.value) {
    isNotificationsOpen.value = false
    isSearchOpen.value = false
  }
}

const toggleNotifications = () => {
  isNotificationsOpen.value = !isNotificationsOpen.value
  if (isNotificationsOpen.value) {
    isUserMenuOpen.value = false
    isSearchOpen.value = false
  }
}

const toggleSearch = () => {
  isSearchOpen.value = !isSearchOpen.value
  if (isSearchOpen.value) {
    isUserMenuOpen.value = false
    isNotificationsOpen.value = false
  }
}

const handleSearch = () => {
  // TODO: Implement search functionality
  if (searchQuery.value.length > 2) {
    // Perform search
    searchResults.value = []
  } else {
    searchResults.value = []
  }
}

const handleSearchResultClick = (result) => {
  // TODO: Navigate to search result
  isSearchOpen.value = false
  searchQuery.value = ''
  searchResults.value = []
}

const handleNotificationClick = (notification) => {
  // TODO: Handle notification click
  isNotificationsOpen.value = false
}

const markAllAsRead = () => {
  // TODO: Mark all notifications as read
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    closeMobileMenu()
    router.push('/')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

  const handleAvatarError = (event) => {
    event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwODYgMTIgMTIgMTJaIiBmaWxsPSIjNjY2NjY2Ii8+CjxwYXRoIGQ9Ik0xMiAxNEM5LjMzIDE0IDUgMTUuMDkgNSAxN1YyMEgxOVYxN0MxOSAxNS4wOSAxNC42NyAxNCAxMiAxNFoiIGZpbGw9IiM2NjY2NjYiLz4KPC9zdmc+'
  }

const getResultIcon = (type) => {
  const icons = {
    image: 'fas fa-image',
    album: 'fas fa-folder',
    user: 'fas fa-user'
  }
  return icons[type] || 'fas fa-search'
}

const getNotificationIcon = (type) => {
  const icons = {
    share: 'fas fa-share',
    comment: 'fas fa-comment',
    like: 'fas fa-heart',
    follow: 'fas fa-user-plus'
  }
  return icons[type] || 'fas fa-bell'
}

const formatTime = (timestamp) => {
  // TODO: Implement proper time formatting
  return '2 hours ago'
}

// Lifecycle
onMounted(() => {
  console.log('AppLayout: Component mounted')
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('resize', checkMobile)
  checkMobile()
  
  // Initialize auth if not already done
  if (!authStore.isInitialized) {
    console.log('AppLayout: Initializing auth...')
    authStore.initializeAuth()
  } else {
    console.log('AppLayout: Auth already initialized')
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('resize', checkMobile)
  document.body.style.overflow = ''
})

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.user-menu-container')) {
    isUserMenuOpen.value = false
  }
  if (!event.target.closest('.notifications-container')) {
    isNotificationsOpen.value = false
  }
  if (!event.target.closest('.search-container')) {
    isSearchOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* App Layout */
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header */
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.header-scrolled {
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 0;
}

/* Brand */
.brand-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: var(--primary-color);
  font-weight: 700;
  font-size: 1.5rem;
}

.brand-icon {
  font-size: 2rem;
  margin-right: 0.5rem;
}

.brand-text {
  font-size: 1.5rem;
}

/* Navigation */
.nav-desktop {
  display: flex;
  gap: 2rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: var(--text-color);
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.nav-link:hover {
  color: var(--primary-color);
  background: var(--primary-color-light);
}

.nav-link.active {
  color: var(--primary-color);
  background: var(--primary-color-light);
}

/* User Actions */
.user-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Search */
.search-container {
  position: relative;
}

.search-toggle {
  background: none;
  border: none;
  color: var(--text-color);
  font-size: 1.1rem;
  padding: 0.5rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.search-toggle:hover {
  background: var(--background-light);
  color: var(--primary-color);
}

.search-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 400px;
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.search-open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.search-input {
  width: 100%;
  padding: 1rem;
  border: none;
  border-bottom: 1px solid var(--border-color);
  font-size: 1rem;
  outline: none;
}

.search-results {
  max-height: 300px;
  overflow-y: auto;
}

.search-result-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

.search-result-item:hover {
  background: var(--background-light);
}

.result-icon {
  color: var(--primary-color);
  font-size: 1.2rem;
}

.result-title {
  font-weight: 500;
  color: var(--text-color);
}

.result-subtitle {
  font-size: 0.9rem;
  color: var(--text-muted);
}

/* Notifications */
.notifications-container {
  position: relative;
}

.notifications-toggle {
  position: relative;
  background: none;
  border: none;
  color: var(--text-color);
  font-size: 1.1rem;
  padding: 0.5rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.notifications-toggle:hover {
  background: var(--background-light);
  color: var(--primary-color);
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: var(--error-color);
  color: white;
  font-size: 0.7rem;
  padding: 0.2rem 0.4rem;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.notifications-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 350px;
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.notifications-open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.notifications-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.notifications-header h3 {
  margin: 0;
  font-size: 1.1rem;
}

.mark-all-read {
  background: none;
  border: none;
  color: var(--primary-color);
  font-size: 0.9rem;
  cursor: pointer;
}

.notifications-list {
  max-height: 300px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  cursor: pointer;
  transition: background 0.2s ease;
  border-bottom: 1px solid var(--border-light);
}

.notification-item:hover {
  background: var(--background-light);
}

.notification-item.unread {
  background: var(--primary-color-very-light);
}

.notification-icon {
  color: var(--primary-color);
  font-size: 1.2rem;
  margin-top: 0.2rem;
}

.notification-text {
  font-size: 0.9rem;
  color: var(--text-color);
  line-height: 1.4;
}

.notification-time {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-top: 0.3rem;
}

.notifications-footer {
  padding: 1rem;
  text-align: center;
  border-top: 1px solid var(--border-color);
}

.view-all {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
}

/* User Menu */
.user-menu-container {
  position: relative;
}

.user-menu-toggle {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: none;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-menu-toggle:hover {
  background: var(--background-light);
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-name {
  font-weight: 500;
  color: var(--text-color);
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 280px;
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.user-open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.user-dropdown-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar-large {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
}

.user-avatar-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: var(--text-color);
  margin-bottom: 0.2rem;
}

.user-email {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.user-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-online {
  background: var(--success-color);
}

.status-offline {
  background: var(--text-muted);
}

.user-dropdown-menu {
  padding: 0.5rem 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1.5rem;
  background: none;
  border: none;
  text-align: left;
  text-decoration: none;
  color: var(--text-color);
  cursor: pointer;
  transition: background 0.2s ease;
}

.dropdown-item:hover {
  background: var(--background-light);
}

.dropdown-item.logout {
  color: var(--error-color);
}

.dropdown-divider {
  height: 1px;
  background: var(--border-color);
  margin: 0.5rem 0;
}

/* Auth Buttons */
.auth-buttons {
  display: flex;
  gap: 0.75rem;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--text-color);
  font-size: 1.5rem;
  padding: 0.5rem;
  cursor: pointer;
}

/* Mobile Navigation */
.mobile-nav {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  background: white;
  z-index: 2000;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
  overflow-y: auto;
}

.mobile-open {
  transform: translateX(0);
}

.mobile-nav-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.mobile-user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.mobile-user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
}

.mobile-user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.mobile-user-name {
  font-weight: 600;
  color: var(--text-color);
}

.mobile-user-email {
  font-size: 0.9rem;
  color: var(--text-muted);
}

.mobile-close {
  background: none;
  border: none;
  color: var(--text-color);
  font-size: 1.5rem;
  padding: 0.5rem;
  cursor: pointer;
}

.mobile-nav-menu {
  padding: 1rem 0;
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  width: 100%;
  padding: 1rem;
  text-decoration: none;
  color: var(--text-color);
  border-bottom: 1px solid var(--border-light);
  transition: background 0.2s ease;
}

.mobile-nav-link:hover {
  background: var(--background-light);
}

.mobile-nav-link.logout {
  color: var(--error-color);
}

.mobile-auth-buttons {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.btn-block {
  width: 100%;
  text-align: center;
}

/* Mobile Overlay */
.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1500;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.overlay-active {
  opacity: 1;
  visibility: visible;
}

/* Main Content */
.main-content {
  flex: 1;
  margin-top: 80px; /* Header height */
}

/* Footer */
.footer {
  background: var(--background-dark);
  color: var(--text-light);
  padding: 3rem 0 1rem;
  margin-top: auto;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-section h3,
.footer-section h4 {
  margin-bottom: 1rem;
  color: white;
}

.footer-section p {
  line-height: 1.6;
  color: var(--text-muted);
}

.footer-section ul {
  list-style: none;
  padding: 0;
}

.footer-section ul li {
  margin-bottom: 0.5rem;
}

.footer-section ul li a {
  color: var(--text-muted);
  text-decoration: none;
  transition: color 0.2s ease;
}

.footer-section ul li a:hover {
  color: white;
}

.social-links {
  display: flex;
  gap: 1rem;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: var(--primary-color);
  color: white;
  border-radius: 50%;
  text-decoration: none;
  transition: all 0.2s ease;
}

.social-link:hover {
  background: var(--primary-color-dark);
  transform: translateY(-2px);
}

.footer-bottom {
  text-align: center;
  padding-top: 2rem;
  border-top: 1px solid var(--border-dark);
  color: var(--text-muted);
}

/* Responsive Design */
@media (max-width: 768px) {
  .nav-desktop,
  .user-actions {
    display: none;
  }
  
  .mobile-menu-toggle {
    display: block;
  }
  
  .header-content {
    padding: 0.75rem 0;
  }
  
  .main-content {
    margin-top: 70px;
  }
  
  .footer-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .social-links {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .brand-text {
    display: none;
  }
  
  .header-content {
    padding: 0.5rem 0;
  }
  
  .main-content {
    margin-top: 60px;
  }
}
</style>

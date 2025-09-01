<template>
  <AppLayout>
    <div class="dashboard">
      <div class="container">
      <!-- Welcome Section -->
      <div class="welcome-section">
        <div class="welcome-card">
          <div class="welcome-content">
            <div class="welcome-text">
              <h1>
                <i class="fas fa-tachometer-alt"></i>
                Welcome back, {{ authStore.user?.username }}!
              </h1>
              <p>Here's an overview of your ImageShare activity.</p>
            </div>
            <div class="welcome-actions">
              <router-link to="/profile" class="btn btn-secondary">
                <i class="fas fa-user"></i>
                My Profile
              </router-link>
              <div class="status-indicator">
                <span class="status-dot" :class="userStatusClass"></span>
                {{ userStatusText }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="stats-section">
        <div class="row">
          <div class="col-3">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-images"></i>
              </div>
              <div class="stat-content">
                <h3>{{ stats.total_images || 0 }}</h3>
                <p>Total Images</p>
              </div>
            </div>
          </div>
          
          <div class="col-3">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-folder"></i>
              </div>
              <div class="stat-content">
                <h3>{{ stats.total_albums || 0 }}</h3>
                <p>Albums</p>
              </div>
            </div>
          </div>
          
          <div class="col-3">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-share"></i>
              </div>
              <div class="stat-content">
                <h3>{{ stats.shared_items || 0 }}</h3>
                <p>Items Shared</p>
              </div>
            </div>
          </div>
          
          <div class="col-3">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-inbox"></i>
              </div>
              <div class="stat-content">
                <h3>{{ stats.received_shares || 0 }}</h3>
                <p>Shared With Me</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="actions-section">
        <div class="card">
          <div class="card-header">
            <h2>
              <i class="fas fa-bolt"></i>
              Quick Actions
            </h2>
          </div>
          <div class="actions-grid">
            <router-link to="/upload" class="action-card upload-action">
              <div class="action-icon">
                <i class="fas fa-cloud-upload-alt"></i>
              </div>
              <div class="action-content">
                <h3>Upload Image</h3>
                <p>Share your moments</p>
              </div>
              <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
              </div>
            </router-link>
            
            <router-link to="/albums/create" class="action-card album-action">
              <div class="action-icon">
                <i class="fas fa-folder-plus"></i>
              </div>
              <div class="action-content">
                <h3>Create Album</h3>
                <p>Organize your photos</p>
              </div>
              <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
              </div>
            </router-link>
            
            <router-link to="/images" class="action-card images-action">
              <div class="action-icon">
                <i class="fas fa-images"></i>
              </div>
              <div class="action-content">
                <h3>Manage Images</h3>
                <p>View and edit photos</p>
              </div>
              <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
              </div>
            </router-link>
            
            <router-link to="/albums" class="action-card manage-action">
              <div class="action-icon">
                <i class="fas fa-folder-open"></i>
              </div>
              <div class="action-content">
                <h3>Manage Albums</h3>
                <p>Organize collections</p>
              </div>
              <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
              </div>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Available Users for Sharing -->
      <div class="users-section">
        <div class="card">
          <div class="card-header">
            <h2>
              <i class="fas fa-users"></i>
              Available Users for Sharing
            </h2>
            <button @click="refreshUsers" class="btn btn-secondary btn-sm" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Refresh
            </button>
          </div>
          <div class="users-container">
            <div v-if="loading" class="loading-state">
              <div class="spinner"></div>
              <p>Loading available users...</p>
            </div>
            <div v-else-if="availableUsers.length === 0" class="empty-state">
              <p>No other users available for sharing.</p>
            </div>
            <div v-else class="users-grid">
              <div v-for="user in availableUsers" :key="user.id" class="user-card">
                <div class="user-avatar">
                  <i class="fas fa-user"></i>
                  <span class="verification-badge" :class="user.isVerified ? 'verified' : 'unverified'">
                    <i class="fas fa-circle"></i>
                  </span>
                </div>
                <div class="user-info">
                  <h4>{{ user.username }}</h4>
                  <p>Member since {{ formatDate(user.createdAt) }}</p>
                </div>
                <router-link :to="`/user/${user.username}`" class="btn btn-secondary btn-sm">
                  <i class="fas fa-eye"></i>
                  View Profile
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="activity-section">
        <div class="row">
          <!-- Recent Images -->
          <div class="col-4">
            <div class="card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-image"></i>
                  Recent Images
                </h3>
              </div>
              <div class="activity-content">
                <div v-if="recentImages.length === 0" class="empty-state">
                  <p>No images yet. <router-link to="/upload">Upload your first image!</router-link></p>
                </div>
                <div v-else class="activity-list">
                  <div v-for="image in recentImages" :key="image.id" class="activity-item">
                    <img :src="image.imageUrl" :alt="image.title" class="activity-thumbnail">
                    <div class="activity-details">
                      <h4>{{ image.title }}</h4>
                      <p>{{ formatDate(image.createdAt) }}</p>
                    </div>
                    <button @click="openQuickShare('image', image.id, image.title)" class="btn btn-success btn-sm">
                      <i class="fas fa-share"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Albums -->
          <div class="col-4">
            <div class="card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-folder"></i>
                  Recent Albums
                </h3>
              </div>
              <div class="activity-content">
                <div v-if="recentAlbums.length === 0" class="empty-state">
                  <p>No albums yet. <router-link to="/albums/create">Create your first album!</router-link></p>
                </div>
                <div v-else class="activity-list">
                  <div v-for="album in recentAlbums" :key="album.id" class="activity-item">
                    <div class="activity-icon">
                      <i class="fas fa-folder"></i>
                    </div>
                    <div class="activity-details">
                      <h4>{{ album.name }}</h4>
                      <p>{{ formatDate(album.createdAt) }} • {{ album.images?.length || 0 }} images</p>
                    </div>
                    <button @click="openQuickShare('album', album.id, album.name)" class="btn btn-info btn-sm">
                      <i class="fas fa-share"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Shares -->
          <div class="col-4">
            <div class="card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-share"></i>
                  Recent Shares
                </h3>
              </div>
              <div class="activity-content">
                <div v-if="recentShares.length === 0" class="empty-state">
                  <p>No shares yet. Share your first image or album!</p>
                </div>
                <div v-else class="activity-list">
                  <div v-for="share in recentShares" :key="share.id" class="activity-item">
                    <div class="activity-icon">
                      <i :class="share.isImageShare ? 'fas fa-image' : 'fas fa-folder'"></i>
                    </div>
                    <div class="activity-details">
                      <h4>{{ share.sharedItemTitle }}</h4>
                      <p>From {{ share.sharedBy.username }} • {{ formatDate(share.sharedAt) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>

    <!-- Quick Share Modal -->
    <QuickShareModal 
      v-if="showQuickShareModal"
      :type="quickShareType"
      :item-id="quickShareItemId"
      :item-title="quickShareItemTitle"
      @close="closeQuickShare"
      @shared="onItemShared"
    />
    </AppLayout>
  </template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications.js'
import DashboardService from '../services/DashboardService'
import QuickShareModal from '../components/QuickShareModal.vue'
import AppLayout from '../components/AppLayout.vue'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Reactive data
const stats = ref({})
const availableUsers = ref([])
const recentImages = ref([])
const recentAlbums = ref([])
const recentShares = ref([])
const loading = ref(false)

// Quick share modal state
const showQuickShareModal = ref(false)
const quickShareType = ref('')
const quickShareItemId = ref(null)
const quickShareItemTitle = ref('')

// Computed properties
const userStatusClass = computed(() => {
  if (!authStore.user?.profile) return 'offline'
  return authStore.user.profile.status === 'online' ? 'online' : 'offline'
})

const userStatusText = computed(() => {
  if (!authStore.user?.profile) return 'Offline'
  return authStore.user.profile.status === 'online' ? 'Online' : 'Offline'
})

// Methods
const loadDashboardData = async () => {
  try {
    console.log('Dashboard: Loading dashboard data, auth token:', localStorage.getItem('auth_token'))
    loading.value = true
    const [statsResponse, usersResponse, imagesResponse, albumsResponse, sharesResponse] = await Promise.all([
      DashboardService.getDashboardStats(),
      DashboardService.getAvailableUsers(),
      DashboardService.getUserImages({ limit: 5 }),
      DashboardService.getUserAlbums({ limit: 5 }),
      DashboardService.getUserShares({ limit: 5 })
    ])
    
    // Debug: Log API responses
    console.log('Dashboard: API responses:', {
      stats: statsResponse,
      users: usersResponse,
      images: imagesResponse,
      albums: albumsResponse,
      shares: sharesResponse
    })
    
    // Handle API response structure with success field
    if (statsResponse.success && statsResponse.stats) {
      stats.value = statsResponse.stats
    } else {
      console.warn('Dashboard: Invalid stats response:', statsResponse)
      stats.value = {}
    }
    
    if (usersResponse.success && usersResponse.users) {
      availableUsers.value = usersResponse.users
    } else {
      console.warn('Dashboard: Invalid users response:', usersResponse)
      availableUsers.value = []
    }
    
    if (imagesResponse.success && imagesResponse.images) {
      const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080'
      const token = localStorage.getItem('auth_token')
      const q = token ? `?token=${encodeURIComponent(token)}` : ''
      recentImages.value = imagesResponse.images.map(img => ({
        ...img,
        imageUrl: img.imageUrl || `${API_BASE}/api/secure-image/${img.id}${q}`
      }))
    } else {
      console.warn('Dashboard: Invalid images response:', imagesResponse)
      recentImages.value = []
    }
    
    if (albumsResponse.success && albumsResponse.albums) {
      recentAlbums.value = albumsResponse.albums
    } else {
      console.warn('Dashboard: Invalid albums response:', albumsResponse)
      recentAlbums.value = []
    }
    
    if (sharesResponse.success && sharesResponse.shares) {
      recentShares.value = sharesResponse.shares
    } else {
      console.warn('Dashboard: Invalid shares response:', sharesResponse)
      recentShares.value = []
    }
  } catch (error) {
    console.error('Error loading dashboard data:', error)
    notificationStore.error('Failed to load dashboard data')
  } finally {
    loading.value = false
  }
}

const refreshUsers = () => {
  loadDashboardData()
}

const openQuickShare = (type, itemId, itemTitle) => {
  quickShareType.value = type
  quickShareItemId.value = itemId
  quickShareItemTitle.value = itemTitle
  showQuickShareModal.value = true
}

const closeQuickShare = () => {
  showQuickShareModal.value = false
  quickShareType.value = ''
  quickShareItemId.value = null
  quickShareItemTitle.value = ''
}

const onItemShared = () => {
  closeQuickShare()
  loadDashboardData() // Refresh data
  notificationStore.showSuccess('Item shared successfully!')
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Lifecycle
onMounted(async () => {
  console.log('Dashboard: Component mounted, auth state:', {
    isAuthenticated: authStore.isAuthenticated,
    user: authStore.user,
    token: localStorage.getItem('auth_token')
  })
  
  // Wait for authentication to be properly initialized
  if (!authStore.isAuthenticated) {
    console.log('Dashboard: Waiting for authentication...')
    // Wait for auth to be initialized
    await new Promise(resolve => setTimeout(resolve, 1000))
  }
  
  // Ensure we have a token before proceeding
  let attempts = 0
  const maxAttempts = 10
  while (!localStorage.getItem('auth_token') && attempts < maxAttempts) {
    console.log(`Dashboard: Waiting for token (attempt ${attempts + 1}/${maxAttempts})...`)
    await new Promise(resolve => setTimeout(resolve, 200))
    attempts++
  }
  
  const hasToken = !!localStorage.getItem('auth_token')
  console.log('Dashboard: Proceeding with data load, token available:', hasToken)
  
  if (hasToken) {
    loadDashboardData()
  } else {
    console.error('Dashboard: No token available after waiting, redirecting to login')
    // Redirect to login if no token after waiting
    window.location.href = '/login'
  }
})
</script>

<style scoped>
.dashboard {
  padding: 2rem 0;
}

.welcome-section {
  margin-bottom: 2rem;
}

.welcome-card {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-lg);
}

.welcome-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.welcome-text h1 {
  color: white;
  margin-bottom: 0.5rem;
}

.welcome-text p {
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 0;
}

.welcome-actions {
  text-align: right;
}

.status-indicator {
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
}

.status-dot.offline {
  background: #6b7280;
}

.stats-section {
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  text-align: center;
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.stat-icon {
  font-size: 2.5rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.stat-content h3 {
  font-size: 2rem;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.stat-content p {
  color: var(--text-secondary);
  margin-bottom: 0;
}

.actions-section {
  margin-bottom: 2rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.action-card {
  display: flex;
  align-items: center;
  padding: 1.5rem;
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  text-decoration: none;
  color: var(--text-primary);
  transition: var(--transition);
  border: 2px solid transparent;
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-color);
}

.action-icon {
  font-size: 2rem;
  margin-right: 1rem;
  width: 60px;
  text-align: center;
}

.upload-action .action-icon { color: var(--primary-color); }
.album-action .action-icon { color: var(--success-color); }
.images-action .action-icon { color: var(--info-color); }
.manage-action .action-icon { color: var(--warning-color); }

.action-content {
  flex: 1;
}

.action-content h3 {
  margin-bottom: 0.25rem;
  font-size: 1.125rem;
}

.action-content p {
  margin-bottom: 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.action-arrow {
  color: var(--text-light);
  transition: var(--transition);
}

.action-card:hover .action-arrow {
  transform: translateX(4px);
  color: var(--primary-color);
}

.users-section {
  margin-bottom: 2rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.users-container {
  min-height: 200px;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--text-secondary);
}

.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.user-card {
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  text-align: center;
  border: 1px solid var(--border-color);
}

.user-avatar {
  position: relative;
  width: 80px;
  height: 80px;
  background: var(--bg-tertiary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 2rem;
  color: var(--text-light);
}

.verification-badge {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: var(--shadow-sm);
}

.verification-badge.verified {
  background: var(--success-color);
}

.verification-badge.unverified {
  background: var(--text-light);
}

.user-info h4 {
  margin-bottom: 0.5rem;
}

.user-info p {
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.activity-section {
  margin-bottom: 2rem;
}

.activity-content {
  min-height: 200px;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
}

.activity-thumbnail {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: var(--border-radius);
}

.activity-icon {
  width: 50px;
  height: 50px;
  background: var(--bg-tertiary);
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--primary-color);
}

.activity-details {
  flex: 1;
}

.activity-details h4 {
  margin-bottom: 0.25rem;
  font-size: 1rem;
}

.activity-details p {
  margin-bottom: 0;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .welcome-content {
    flex-direction: column;
    gap: 1rem;
  }
  
  .welcome-actions {
    text-align: left;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .users-grid {
    grid-template-columns: 1fr;
  }
  
  .row {
    display: block;
  }
  
  .col-4 {
    margin-bottom: 1.5rem;
  }
}
</style>

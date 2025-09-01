<template>
  <AppLayout>
    <div class="album-gallery-page">
      <div class="container">
        <!-- Page Header -->
        <div class="page-header">
          <div class="header-content">
            <h1>
              <i class="fas fa-folder-open"></i>
              My Albums
            </h1>
            <p>Organize and manage your photo collections</p>
          </div>
          <div class="header-actions">
            <router-link to="/albums/create" class="btn btn-primary">
              <i class="fas fa-plus"></i>
              Create Album
            </router-link>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-filters">
          <div class="search-section">
            <div class="search-input-wrapper">
              <i class="fas fa-search search-icon"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search albums..."
                class="search-input"
                @input="handleSearch"
              />
              <button 
                v-if="searchQuery" 
                @click="clearSearch" 
                class="clear-search"
                title="Clear search"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <div class="filters-section">
            <div class="filter-group">
              <label for="privacy-filter">Privacy:</label>
              <select 
                id="privacy-filter" 
                v-model="filters.privacy" 
                class="filter-select"
                @change="applyFilters"
              >
                <option value="">All</option>
                <option value="private">Private</option>
                <option value="friends">Friends Only</option>
                <option value="public">Public</option>
              </select>
            </div>

            <div class="filter-group">
              <label for="sort-filter">Sort by:</label>
              <select 
                id="sort-filter" 
                v-model="filters.sort" 
                class="filter-select"
                @change="applyFilters"
              >
                <option value="createdAt">Date Created</option>
                <option value="name">Name</option>
                <option value="imageCount">Image Count</option>
                <option value="updatedAt">Last Updated</option>
              </select>
            </div>

            <div class="filter-group">
              <label for="order-filter">Order:</label>
              <select 
                id="order-filter" 
                v-model="filters.order" 
                class="filter-select"
                @change="applyFilters"
              >
                <option value="desc">Newest First</option>
                <option value="asc">Oldest First</option>
              </select>
            </div>

            <button @click="resetFilters" class="btn btn-secondary btn-sm">
              <i class="fas fa-undo"></i>
              Reset
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <LoadingSpinner />
          <p>Loading albums...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="albums.length === 0 && !searchQuery" class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-folder-open"></i>
          </div>
          <h3>No albums yet</h3>
          <p>Create your first album to start organizing your photos</p>
          <router-link to="/albums/create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Create Your First Album
          </router-link>
        </div>

        <!-- No Search Results -->
        <div v-else-if="albums.length === 0 && searchQuery" class="no-results">
          <div class="no-results-icon">
            <i class="fas fa-search"></i>
          </div>
          <h3>No albums found</h3>
          <p>Try adjusting your search terms or filters</p>
          <button @click="clearSearch" class="btn btn-secondary">
            <i class="fas fa-undo"></i>
            Clear Search
          </button>
        </div>

        <!-- Albums Grid -->
        <div v-else class="albums-container">
          <div class="albums-header">
            <div class="results-info">
              <span class="results-count">{{ totalAlbums }} album{{ totalAlbums !== 1 ? 's' : '' }}</span>
              <span v-if="searchQuery" class="search-info">for "{{ searchQuery }}"</span>
            </div>
            
            <div class="view-options">
              <button 
                @click="viewMode = 'grid'" 
                class="view-btn"
                :class="{ 'active': viewMode === 'grid' }"
                title="Grid view"
              >
                <i class="fas fa-th"></i>
              </button>
              <button 
                @click="viewMode = 'list'" 
                class="view-btn"
                :class="{ 'active': viewMode === 'list' }"
                title="List view"
              >
                <i class="fas fa-list"></i>
              </button>
            </div>
          </div>

          <!-- Grid View -->
          <div v-if="viewMode === 'grid'" class="albums-grid">
            <div 
              v-for="album in albums" 
              :key="album.id"
              class="album-card"
              @click="openAlbum(album)"
            >
              <div class="album-cover">
                <img 
                  v-if="album.coverImage" 
                  :src="album.coverImage.thumbnailUrl" 
                  :alt="album.name"
                />
                <div v-else class="no-cover">
                  <i class="fas fa-folder"></i>
                </div>
                
                <div class="album-overlay">
                  <div class="album-actions">
                    <button 
                      @click.stop="openQuickActions(album, $event)"
                      class="action-btn"
                      title="More actions"
                    >
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                  </div>
                </div>

                <div class="album-privacy">
                  <i 
                    :class="getPrivacyIcon(album.privacy)"
                    :title="getPrivacyText(album.privacy)"
                  ></i>
                </div>
              </div>

              <div class="album-info">
                <h3 class="album-name">{{ album.name }}</h3>
                <p v-if="album.description" class="album-description">
                  {{ album.description }}
                </p>
                <div class="album-meta">
                  <span class="image-count">
                    <i class="fas fa-image"></i>
                    {{ album.imageCount || 0 }} images
                  </span>
                  <span class="album-date">
                    <i class="fas fa-calendar"></i>
                    {{ formatDate(album.createdAt) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- List View -->
          <div v-else class="albums-list">
            <div 
              v-for="album in albums" 
              :key="album.id"
              class="album-list-item"
              @click="openAlbum(album)"
            >
              <div class="album-thumbnail">
                <img 
                  v-if="album.coverImage" 
                  :src="album.coverImage.thumbnailUrl" 
                  :alt="album.name"
                />
                <div v-else class="no-thumbnail">
                  <i class="fas fa-folder"></i>
                </div>
              </div>

              <div class="album-details">
                <div class="album-main">
                  <h3 class="album-name">{{ album.name }}</h3>
                  <p v-if="album.description" class="album-description">
                    {{ album.description }}
                  </p>
                </div>

                <div class="album-stats">
                  <span class="image-count">
                    <i class="fas fa-image"></i>
                    {{ album.imageCount || 0 }} images
                  </span>
                  <span class="album-date">
                    <i class="fas fa-calendar"></i>
                    {{ formatDate(album.createdAt) }}
                  </span>
                  <span class="album-privacy">
                    <i 
                      :class="getPrivacyIcon(album.privacy)"
                      :title="getPrivacyText(album.privacy)"
                    ></i>
                    {{ getPrivacyText(album.privacy) }}
                  </span>
                </div>

                <div class="album-actions">
                  <button 
                    @click.stop="openQuickActions(album, $event)"
                    class="action-btn"
                    title="More actions"
                  >
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="pagination">
            <button 
              @click="changePage(currentPage - 1)"
              :disabled="currentPage <= 1"
              class="page-btn"
            >
              <i class="fas fa-chevron-left"></i>
              Previous
            </button>

            <div class="page-numbers">
              <button 
                v-for="page in visiblePages" 
                :key="page"
                @click="changePage(page)"
                class="page-btn"
                :class="{ 'active': page === currentPage }"
              >
                {{ page }}
              </button>
            </div>

            <button 
              @click="changePage(currentPage + 1)"
              :disabled="currentPage >= totalPages"
              class="page-btn"
            >
              Next
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Actions Menu -->
      <div 
        v-if="showQuickActions" 
        class="quick-actions-menu"
        :style="quickActionsPosition"
        @click.stop
      >
        <div class="action-item" @click="editAlbum(quickActionsAlbum)">
          <i class="fas fa-edit"></i>
          Edit Album
        </div>
        <div class="action-item" @click="shareAlbum(quickActionsAlbum)">
          <i class="fas fa-share"></i>
          Share Album
        </div>
        <div class="action-item" @click="duplicateAlbum(quickActionsAlbum)">
          <i class="fas fa-copy"></i>
          Duplicate
        </div>
        <div class="action-item" @click="exportAlbum(quickActionsAlbum)">
          <i class="fas fa-download"></i>
          Export
        </div>
        <div class="action-divider"></div>
        <div class="action-item danger" @click="deleteAlbum(quickActionsAlbum)">
          <i class="fas fa-trash"></i>
          Delete Album
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Delete Album</h3>
            <button @click="closeDeleteModal" class="modal-close">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="modal-body">
            <p>Are you sure you want to delete "<strong>{{ albumToDelete?.name }}</strong>"?</p>
            <p class="warning-text">
              <i class="fas fa-exclamation-triangle"></i>
              This action cannot be undone. All images in the album will remain but will no longer be organized in this collection.
            </p>
          </div>
          
          <div class="modal-actions">
            <button @click="closeDeleteModal" class="btn btn-secondary">
              Cancel
            </button>
            <button @click="confirmDeleteAlbum" class="btn btn-danger">
              <i class="fas fa-trash"></i>
              Delete Album
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications.js'
import AppLayout from '../components/AppLayout.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import AlbumService from '../services/AlbumService'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Data
const albums = ref([])
const loading = ref(false)
const totalAlbums = ref(0)
const currentPage = ref(1)
const totalPages = ref(1)
const viewMode = ref('grid')

// Search and filters
const searchQuery = ref('')
const filters = ref({
  privacy: '',
  sort: 'createdAt',
  order: 'desc'
})

// Quick actions
const showQuickActions = ref(false)
const quickActionsAlbum = ref(null)
const quickActionsPosition = ref({})

// Delete modal
const showDeleteModal = ref(false)
const albumToDelete = ref(null)

// Computed properties
const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

// Methods
const loadAlbums = async () => {
  loading.value = true
  
  try {
    const params = {
      page: currentPage.value,
      limit: 12,
      search: searchQuery.value,
      ...filters.value
    }
    
    const response = await AlbumService.getUserAlbums(params)
    
    if (response.success) {
      albums.value = response.albums || []
      totalAlbums.value = response.total || 0
      totalPages.value = response.pages || 1
    } else {
      console.warn('Invalid albums response:', response)
      albums.value = []
      totalAlbums.value = 0
      totalPages.value = 1
    }
  } catch (error) {
    console.error('Error loading albums:', error)
    notificationStore.error('Failed to load albums')
    albums.value = []
    totalAlbums.value = 0
    totalPages.value = 1
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  loadAlbums()
}

const clearSearch = () => {
  searchQuery.value = ''
  currentPage.value = 1
  loadAlbums()
}

const applyFilters = () => {
  currentPage.value = 1
  loadAlbums()
}

const resetFilters = () => {
  filters.value = {
    privacy: '',
    sort: 'createdAt',
    order: 'desc'
  }
  currentPage.value = 1
  loadAlbums()
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    loadAlbums()
  }
}

const openAlbum = (album) => {
  router.push(`/albums/${album.id}`)
}

// Quick actions
const openQuickActions = (album, event) => {
  event.preventDefault()
  event.stopPropagation()
  
  quickActionsAlbum.value = album
  showQuickActions.value = true
  
  const rect = event.target.getBoundingClientRect()
  quickActionsPosition.value = {
    top: `${rect.bottom + 5}px`,
    left: `${rect.left}px`
  }
}

const closeQuickActions = () => {
  showQuickActions.value = false
  quickActionsAlbum.value = null
}

const editAlbum = (album) => {
  closeQuickActions()
  router.push(`/albums/${album.id}/edit`)
}

const shareAlbum = (album) => {
  closeQuickActions()
  // TODO: Implement album sharing
  notificationStore.showInfo('Album sharing coming soon!')
}

const duplicateAlbum = async (album) => {
  closeQuickActions()
  
  try {
    const newAlbum = await AlbumService.duplicateAlbum(album.id, {
      name: `${album.name} (Copy)`,
      includeImages: true,
      includeSharing: false
    })
    
    notificationStore.showSuccess('Album duplicated successfully!')
    loadAlbums() // Refresh the list
  } catch (error) {
    console.error('Error duplicating album:', error)
    notificationStore.error('Failed to duplicate album')
  }
}

const exportAlbum = async (album) => {
  closeQuickActions()
  
  try {
    const downloadUrl = await AlbumService.exportAlbum(album.id, {
      format: 'zip',
      quality: 'high'
    })
    
    // Create download link
    const link = document.createElement('a')
    link.href = downloadUrl
    link.download = `${album.name}.zip`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    notificationStore.showSuccess('Album export started!')
  } catch (error) {
    console.error('Error exporting album:', error)
    notificationStore.error('Failed to export album')
  }
}

const deleteAlbum = (album) => {
  closeQuickActions()
  albumToDelete.value = album
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  albumToDelete.value = null
}

const confirmDeleteAlbum = async () => {
  if (!albumToDelete.value) return
  
  try {
    await AlbumService.deleteAlbum(albumToDelete.value.id)
    
    notificationStore.showSuccess('Album deleted successfully!')
    closeDeleteModal()
    loadAlbums() // Refresh the list
  } catch (error) {
    console.error('Error deleting album:', error)
    notificationStore.error('Failed to delete album')
  }
}

// Utility methods
const getPrivacyIcon = (privacy) => {
  switch (privacy) {
    case 'private': return 'fas fa-lock'
    case 'friends': return 'fas fa-users'
    case 'public': return 'fas fa-globe'
    default: return 'fas fa-question'
  }
}

const getPrivacyText = (privacy) => {
  switch (privacy) {
    case 'private': return 'Private'
    case 'friends': return 'Friends Only'
    case 'public': return 'Public'
    default: return 'Unknown'
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Event listeners
const handleClickOutside = (event) => {
  if (showQuickActions.value && !event.target.closest('.quick-actions-menu')) {
    closeQuickActions()
  }
}

// Lifecycle
onMounted(() => {
  loadAlbums()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.album-gallery-page {
  padding: 2rem 0;
  min-height: 100vh;
  background: var(--bg-secondary);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header-content h1 {
  font-size: 2.5rem;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.header-content p {
  color: var(--text-secondary);
  font-size: 1.1rem;
  margin: 0;
}

.search-filters {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
}

.search-section {
  margin-bottom: 1.5rem;
}

.search-input-wrapper {
  position: relative;
  max-width: 500px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 1rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.clear-search {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--text-light);
  cursor: pointer;
  padding: 0.25rem;
}

.clear-search:hover {
  color: var(--text-primary);
}

.filters-section {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group label {
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
}

.filter-select {
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.loading-state,
.empty-state,
.no-results {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);
}

.empty-icon,
.no-results-icon {
  font-size: 4rem;
  color: var(--text-light);
  margin-bottom: 1rem;
}

.empty-state h3,
.no-results h3 {
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.albums-container {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.albums-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.results-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.results-count {
  font-weight: 600;
  color: var(--text-primary);
}

.search-info {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.view-options {
  display: flex;
  gap: 0.25rem;
}

.view-btn {
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
}

.view-btn:first-child {
  border-radius: var(--border-radius) 0 0 var(--border-radius);
}

.view-btn:last-child {
  border-radius: 0 var(--border-radius) var(--border-radius) 0;
}

.view-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

/* Grid View */
.albums-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.album-card {
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  overflow: hidden;
  cursor: pointer;
  transition: var(--transition);
  border: 1px solid var(--border-color);
}

.album-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.album-cover {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.album-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-cover {
  width: 100%;
  height: 100%;
  background: var(--bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: var(--text-light);
}

.album-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  opacity: 0;
  transition: var(--transition);
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
  padding: 1rem;
}

.album-card:hover .album-overlay {
  opacity: 1;
}

.album-privacy {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: var(--border-radius);
  font-size: 0.75rem;
}

.album-info {
  padding: 1.5rem;
}

.album-name {
  font-size: 1.25rem;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.album-description {
  color: var(--text-secondary);
  margin-bottom: 1rem;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.album-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--text-light);
}

.album-meta span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* List View */
.albums-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.album-list-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
  border: 1px solid var(--border-color);
}

.album-list-item:hover {
  background: var(--bg-tertiary);
}

.album-thumbnail {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
}

.album-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--border-radius);
}

.no-thumbnail {
  width: 100%;
  height: 100%;
  background: var(--bg-tertiary);
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--text-light);
}

.album-details {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.album-main {
  flex: 1;
}

.album-main .album-name {
  margin-bottom: 0.25rem;
}

.album-main .album-description {
  margin-bottom: 0;
}

.album-stats {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--text-light);
  margin-right: 1rem;
}

.album-stats span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.album-actions {
  flex-shrink: 0;
}

.action-btn {
  background: none;
  border: none;
  color: var(--text-light);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: var(--border-radius);
  transition: var(--transition);
}

.action-btn:hover {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

/* Quick Actions Menu */
.quick-actions-menu {
  position: fixed;
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
  z-index: 1000;
  min-width: 180px;
  overflow: hidden;
}

.action-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text-primary);
  transition: var(--transition);
}

.action-item:hover {
  background: var(--bg-secondary);
}

.action-item.danger {
  color: var(--danger-color);
}

.action-item.danger:hover {
  background: var(--danger-light);
}

.action-divider {
  height: 1px;
  background: var(--border-color);
  margin: 0.5rem 0;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-primary);
  cursor: pointer;
  transition: var(--transition);
  border-radius: var(--border-radius);
}

.page-btn:hover:not(:disabled) {
  background: var(--bg-tertiary);
}

.page-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  max-width: 500px;
  width: 90vw;
  box-shadow: var(--shadow-xl);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
  margin: 0;
  color: var(--text-primary);
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-light);
  cursor: pointer;
  padding: 0.5rem;
}

.modal-close:hover {
  color: var(--text-primary);
}

.modal-body {
  padding: 1.5rem;
}

.warning-text {
  color: var(--warning-color);
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  font-size: 1rem;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background: var(--primary-dark);
}

.btn-secondary {
  background: var(--secondary-color);
  color: white;
}

.btn-secondary:hover {
  background: var(--secondary-dark);
}

.btn-danger {
  background: var(--danger-color);
  color: white;
}

.btn-danger:hover {
  background: var(--danger-dark);
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .header-content h1 {
    font-size: 2rem;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-group {
    justify-content: space-between;
  }
  
  .albums-grid {
    grid-template-columns: 1fr;
  }
  
  .album-list-item {
    flex-direction: column;
  }
  
  .album-details {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .album-stats {
    justify-content: space-between;
    margin-right: 0;
  }
  
  .pagination {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .page-numbers {
    order: -1;
  }
}
</style>

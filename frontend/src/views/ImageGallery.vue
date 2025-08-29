<template>
  <div class="gallery-page">
    <AppLayout>
      <div class="container">
        <!-- Gallery Header -->
        <div class="gallery-header">
          <div class="header-content">
            <h1>My Images</h1>
            <p>{{ totalImages }} images in your collection</p>
          </div>
          <div class="header-actions">
            <router-link to="/upload" class="btn btn-primary">
              <i class="fas fa-plus"></i>
              Upload Images
            </router-link>
          </div>
        </div>

        <!-- Gallery Controls -->
        <div class="gallery-controls">
          <div class="controls-left">
            <!-- View Toggle -->
            <div class="view-toggle">
              <button 
                class="view-btn"
                :class="{ active: viewMode === 'grid' }"
                @click="viewMode = 'grid'"
                title="Grid View"
              >
                <i class="fas fa-th"></i>
              </button>
              <button 
                class="view-btn"
                :class="{ active: viewMode === 'list' }"
                @click="viewMode = 'list'"
                title="List View"
              >
                <i class="fas fa-list"></i>
              </button>
            </div>

            <!-- Sort Options -->
            <div class="sort-controls">
              <label for="sort-select">Sort by:</label>
              <select 
                id="sort-select" 
                v-model="sortBy"
                @change="handleSortChange"
                class="form-control"
              >
                <option value="uploaded_at">Date Uploaded</option>
                <option value="name">Name</option>
                <option value="size">File Size</option>
                <option value="views">Views</option>
                <option value="likes">Likes</option>
              </select>
              <button 
                class="sort-direction"
                @click="toggleSortDirection"
                :title="sortDirection === 'desc' ? 'Descending' : 'Ascending'"
              >
                <i :class="sortDirection === 'desc' ? 'fas fa-sort-down' : 'fas fa-sort-up'"></i>
              </button>
            </div>
          </div>

          <div class="controls-right">
            <!-- Search -->
            <div class="search-container">
              <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Search images..."
                  @input="handleSearch"
                  class="search-input"
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

            <!-- Filter Toggle -->
            <button 
              class="filter-toggle"
              @click="toggleFilters"
              :class="{ active: showFilters }"
              title="Toggle filters"
            >
              <i class="fas fa-filter"></i>
              <span v-if="activeFiltersCount > 0" class="filter-badge">
                {{ activeFiltersCount }}
              </span>
            </button>
          </div>
        </div>

        <!-- Filters Panel -->
        <div v-if="showFilters" class="filters-panel">
          <div class="filters-content">
            <div class="filter-group">
              <label>Date Range:</label>
              <div class="date-inputs">
                <input 
                  type="date" 
                  v-model="filters.dateFrom"
                  class="form-control"
                  placeholder="From"
                />
                <input 
                  type="date" 
                  v-model="filters.dateTo"
                  class="form-control"
                  placeholder="To"
                />
              </div>
            </div>

            <div class="filter-group">
              <label>File Type:</label>
              <div class="checkbox-group">
                <label class="checkbox-item">
                  <input 
                    type="checkbox" 
                    v-model="filters.fileTypes" 
                    value="image/jpeg"
                  />
                  <span>JPG</span>
                </label>
                <label class="checkbox-item">
                  <input 
                    type="checkbox" 
                    v-model="filters.fileTypes" 
                    value="image/png"
                  />
                  <span>PNG</span>
                </label>
                <label class="checkbox-item">
                  <input 
                    type="checkbox" 
                    v-model="filters.fileTypes" 
                    value="image/gif"
                  />
                  <span>GIF</span>
                </label>
                <label class="checkbox-item">
                  <input 
                    type="checkbox" 
                    v-model="filters.fileTypes" 
                    value="image/webp"
                  />
                  <span>WebP</span>
                </label>
              </div>
            </div>

            <div class="filter-group">
              <label>Privacy:</label>
              <div class="checkbox-group">
                <label class="checkbox-item">
                  <input 
                    type="checkbox" 
                    v-model="filters.privacy" 
                    value="private"
                  />
                  <span>Private</span>
                </label>
                <label class="checkbox-item">
                  <input 
                    type="checkbox" 
                    v-model="filters.privacy" 
                    value="friends"
                  />
                  <span>Friends Only</span>
                </label>
                <label class="checkbox-item">
                  <input 
                    type="checkbox" 
                    v-model="filters.privacy" 
                    value="public"
                  />
                  <span>Public</span>
                </label>
              </div>
            </div>

            <div class="filter-group">
              <label>Size Range (MB):</label>
              <div class="range-inputs">
                <input 
                  type="number" 
                  v-model="filters.sizeFrom"
                  class="form-control"
                  placeholder="Min"
                  min="0"
                  step="0.1"
                />
                <span>to</span>
                <input 
                  type="number" 
                  v-model="filters.sizeTo"
                  class="form-control"
                  placeholder="Max"
                  min="0"
                  step="0.1"
                />
              </div>
            </div>

            <div class="filter-actions">
              <button @click="clearFilters" class="btn btn-secondary">
                Clear All
              </button>
              <button @click="applyFilters" class="btn btn-primary">
                Apply Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Gallery Content -->
        <div class="gallery-content">
          <!-- Loading State -->
          <div v-if="isLoading" class="loading-state">
            <LoadingSpinner />
            <p>Loading images...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredImages.length === 0" class="empty-state">
            <div class="empty-icon">
              <i class="fas fa-images"></i>
            </div>
            <h3>No images found</h3>
            <p v-if="searchQuery || hasActiveFilters">
              Try adjusting your search or filters
            </p>
            <p v-else>
              Start by uploading some images
            </p>
            <router-link to="/upload" class="btn btn-primary">
              Upload Your First Image
            </router-link>
          </div>

          <!-- Images Grid/List -->
          <div v-else class="images-container" :class="viewMode">
            <div 
              v-for="image in paginatedImages" 
              :key="image.id"
              class="image-item"
              @click="openImageModal(image)"
            >
              <div class="image-wrapper">
                <img 
                  :src="image.thumbnailUrl" 
                  :alt="image.name"
                  :data-src="image.thumbnailUrl"
                  class="image-thumbnail"
                  loading="lazy"
                />
                <div class="image-overlay">
                  <div class="overlay-actions">
                    <button 
                      @click.stop="toggleFavorite(image)"
                      class="action-btn"
                      :class="{ active: image.isFavorite }"
                      :title="image.isFavorite ? 'Remove from favorites' : 'Add to favorites'"
                    >
                      <i class="fas fa-heart"></i>
                    </button>
                    <button 
                      @click.stop="shareImage(image)"
                      class="action-btn"
                      title="Share image"
                    >
                      <i class="fas fa-share-alt"></i>
                    </button>
                    <button 
                      @click.stop="downloadImage(image)"
                      class="action-btn"
                      title="Download image"
                    >
                      <i class="fas fa-download"></i>
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="image-info">
                <div class="image-name">{{ image.name }}</div>
                <div class="image-meta">
                  <span class="upload-date">{{ formatDate(image.uploadedAt) }}</span>
                  <span class="file-size">{{ formatFileSize(image.size) }}</span>
                </div>
                <div class="image-stats">
                  <span class="stat">
                    <i class="fas fa-eye"></i>
                    {{ image.views }}
                  </span>
                  <span class="stat">
                    <i class="fas fa-heart"></i>
                    {{ image.likes }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="pagination">
            <button 
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
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
                :class="['page-btn', { active: page === currentPage }]"
              >
                {{ page }}
              </button>
            </div>
            
            <button 
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="page-btn"
            >
              Next
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </AppLayout>

    <!-- Image Modal -->
    <ImageModal 
      v-if="selectedImage"
      :image="selectedImage"
      @close="selectedImage = null"
      @favorite="toggleFavorite"
      @share="shareImage"
      @download="downloadImage"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notification'
import AppLayout from '../components/AppLayout.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import ImageModal from '../components/ImageModal.vue'
import ImageService from '../services/ImageService'

// Router and stores
const router = useRouter()
const authStore = useAuthStore()
const notificationsStore = useNotificationStore()

// Gallery state
const images = ref([])
const isLoading = ref(false)
const viewMode = ref('grid')
const searchQuery = ref('')
const showFilters = ref(false)
const selectedImage = ref(null)

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(20)

// Sorting and filtering
const sortBy = ref('uploaded_at')
const sortDirection = ref('desc')
const filters = ref({
  dateFrom: '',
  dateTo: '',
  fileTypes: [],
  privacy: [],
  sizeFrom: '',
  sizeTo: ''
})

// Computed properties
const filteredImages = computed(() => {
  let filtered = [...images.value]

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(image => 
      image.name.toLowerCase().includes(query) ||
      image.description?.toLowerCase().includes(query)
    )
  }

  // Apply filters
  if (filters.value.dateFrom) {
    filtered = filtered.filter(image => 
      new Date(image.uploadedAt) >= new Date(filters.value.dateFrom)
    )
  }
  
  if (filters.value.dateTo) {
    filtered = filtered.filter(image => 
      new Date(image.uploadedAt) <= new Date(filters.value.dateTo)
    )
  }

  if (filters.value.fileTypes.length > 0) {
    filtered = filtered.filter(image => 
      filters.value.fileTypes.includes(image.mimeType)
    )
  }

  if (filters.value.privacy.length > 0) {
    filtered = filtered.filter(image => 
      filters.value.privacy.includes(image.privacy)
    )
  }

  if (filters.value.sizeFrom) {
    filtered = filtered.filter(image => 
      image.size >= parseFloat(filters.value.sizeFrom) * 1024 * 1024
    )
  }

  if (filters.value.sizeTo) {
    filtered = filtered.filter(image => 
      image.size <= parseFloat(filters.value.sizeTo) * 1024 * 1024
    )
  }

  // Apply sorting
  filtered.sort((a, b) => {
    let aVal, bVal
    
    switch (sortBy.value) {
      case 'name':
        aVal = a.name.toLowerCase()
        bVal = b.name.toLowerCase()
        break
      case 'size':
        aVal = a.size
        bVal = b.size
        break
      case 'views':
        aVal = a.views || 0
        bVal = b.views || 0
        break
      case 'likes':
        aVal = a.likes || 0
        bVal = b.likes || 0
        break
      default:
        aVal = new Date(a.uploadedAt)
        bVal = new Date(b.uploadedAt)
    }

    if (sortDirection.value === 'desc') {
      return aVal > bVal ? -1 : 1
    } else {
      return aVal < bVal ? -1 : 1
    }
  })

  return filtered
})

const totalImages = computed(() => filteredImages.value.length)
const totalPages = computed(() => Math.ceil(totalImages.value / itemsPerPage.value))
const paginatedImages = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredImages.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.value.dateFrom) count++
  if (filters.value.dateTo) count++
  if (filters.value.fileTypes.length > 0) count++
  if (filters.value.privacy.length > 0) count++
  if (filters.value.sizeFrom) count++
  if (filters.value.sizeTo) count++
  return count
})

const hasActiveFilters = computed(() => activeFiltersCount.value > 0)

// Methods
const loadImages = async () => {
  isLoading.value = true
  try {
    const params = {
      page: currentPage.value,
      limit: itemsPerPage.value,
      sort: sortBy.value,
      order: sortDirection.value,
      search: searchQuery.value,
      privacy: filters.value.privacy,
      dateFrom: filters.value.dateFrom,
      dateTo: filters.value.dateTo
    }
    
    const response = await ImageService.getUserImages(params)
    const raw = response && response.images ? response.images : []
    // Normalize API payload to the fields the UI expects
    images.value = raw.map((item) => ({
      id: item.id,
      name: item.title || item.imageName || 'Untitled',
      description: item.description || '',
      // Backend provides createdAt; map to uploadedAt used by UI
      uploadedAt: item.createdAt || item.uploadedAt || null,
      // Backend provides imageSize; map to size used by UI
      size: item.imageSize ?? item.size ?? 0,
      views: item.views ?? 0,
      likes: item.likes ?? 0,
      isFavorite: item.isFavorite ?? false,
      // Build a secure image URL if backend didn't provide one
      thumbnailUrl: item.thumbnailUrl || item.url || buildImageUrl(item.id),
      fullUrl: item.fullUrl || buildImageUrl(item.id)
    }))
    
  } catch (error) {
    console.error('Error loading images:', error)
    notificationsStore.showError('Failed to load images')
    // Set empty array instead of mock data
    images.value = []
  } finally {
    isLoading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
}

const clearSearch = () => {
  searchQuery.value = ''
  currentPage.value = 1
}

const toggleFilters = () => {
  showFilters.value = !showFilters.value
}

const applyFilters = () => {
  currentPage.value = 1
  showFilters.value = false
}

const clearFilters = () => {
  filters.value = {
    dateFrom: '',
    dateTo: '',
    fileTypes: [],
    privacy: [],
    sizeFrom: '',
    sizeTo: ''
  }
  currentPage.value = 1
}

const handleSortChange = () => {
  currentPage.value = 1
}

const toggleSortDirection = () => {
  sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc'
  currentPage.value = 1
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const openImageModal = (image) => {
  selectedImage.value = image
}

const toggleFavorite = (image) => {
  image.isFavorite = !image.isFavorite
  // TODO: API call to update favorite status
  notificationsStore.showSuccess(
    image.isFavorite ? 'Added to favorites' : 'Removed from favorites'
  )
}

const shareImage = (image) => {
  // TODO: Implement share functionality
  notificationsStore.showInfo('Share functionality coming soon')
}

const downloadImage = (image) => {
  // TODO: Implement download functionality
  notificationsStore.showInfo('Download functionality coming soon')
}

// Utility methods
const formatDate = (date) => {
  try {
    if (!date) return ''
    const d = new Date(date)
    if (isNaN(d.getTime())) return ''
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    }).format(d)
  } catch (e) {
    return ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}



// Watchers
watch(searchQuery, () => {
  currentPage.value = 1
})

// Load images on mount
onMounted(() => {
  loadImages()
})
</script>

<style scoped>
.gallery-page {
  min-height: 100vh;
  background: #f8f9fa;
}

.gallery-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-content h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.header-content p {
  color: #6c757d;
  font-size: 1.1rem;
  margin: 0;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

.gallery-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.controls-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.view-toggle {
  display: flex;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  overflow: hidden;
}

.view-btn {
  padding: 0.5rem 1rem;
  border: none;
  background: white;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.3s ease;
}

.view-btn:hover {
  background: #f8f9fa;
}

.view-btn.active {
  background: #007bff;
  color: white;
}

.sort-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-controls label {
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.form-control {
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 0.9rem;
}

.sort-direction {
  padding: 0.5rem;
  border: 1px solid #ced4da;
  background: white;
  color: #6c757d;
  cursor: pointer;
  border-radius: 4px;
}

.sort-direction:hover {
  background: #f8f9fa;
}

.controls-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.search-container {
  position: relative;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  color: #6c757d;
}

.search-input {
  padding: 0.5rem 0.75rem 0.5rem 2.5rem;
  border: 1px solid #ced4da;
  border-radius: 6px;
  width: 250px;
  font-size: 0.9rem;
}

.clear-search {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 0.25rem;
}

.clear-search:hover {
  color: #495057;
}

.filter-toggle {
  position: relative;
  padding: 0.5rem 1rem;
  border: 1px solid #ced4da;
  background: white;
  color: #6c757d;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.filter-toggle:hover {
  background: #f8f9fa;
}

.filter-toggle.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.filter-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #dc3545;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.filters-panel {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.filters-content {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.filter-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.date-inputs,
.range-inputs {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.range-inputs span {
  color: #6c757d;
}

.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-item input[type="checkbox"] {
  margin: 0;
}

.filter-actions {
  grid-column: 1 / -1;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.gallery-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-icon {
  font-size: 4rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6c757d;
  margin-bottom: 1.5rem;
}

.images-container {
  margin-bottom: 2rem;
}

.images-container.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.images-container.list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.images-container.list .image-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.images-container.list .image-wrapper {
  width: 100px;
  height: 100px;
  flex-shrink: 0;
}

.images-container.list .image-info {
  flex: 1;
  text-align: left;
}

.image-item {
  cursor: pointer;
  transition: transform 0.3s ease;
}

.image-item:hover {
  transform: translateY(-2px);
}

.image-wrapper {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background: #f8f9fa;
}

.image-thumbnail {
  width: 100%;
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.image-item:hover .image-thumbnail {
  transform: scale(1.05);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.image-item:hover .image-overlay {
  opacity: 1;
}

.overlay-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn:hover {
  background: white;
  transform: scale(1.1);
}

.action-btn.active {
  background: #dc3545;
  color: white;
}

.image-info {
  padding: 1rem 0;
  text-align: center;
}

.image-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.image-meta {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
  font-size: 0.8rem;
  color: #6c757d;
}

.image-stats {
  display: flex;
  justify-content: center;
  gap: 1rem;
  font-size: 0.8rem;
  color: #6c757d;
}

.stat {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #dee2e6;
  background: white;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 4px;
}

.page-btn:hover:not(:disabled) {
  background: #f8f9fa;
}

.page-btn.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .gallery-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .gallery-controls {
    flex-direction: column;
    gap: 1rem;
  }
  
  .controls-left,
  .controls-right {
    width: 100%;
    justify-content: center;
  }
  
  .search-input {
    width: 100%;
  }
  
  .filters-content {
    grid-template-columns: 1fr;
  }
  
  .images-container.grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
}
</style>
const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080'
const authToken = () => localStorage.getItem('auth_token')

const buildImageUrl = (id) => {
  if (!id) return ''
  const token = authToken()
  const q = token ? `?token=${encodeURIComponent(token)}` : ''
  return `${API_BASE}/api/secure-image/${id}${q}`
}

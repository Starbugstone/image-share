<template>
  <div class="album-detail">
    <!-- Album Header -->
    <div class="album-header">
      <div class="album-info">
        <div class="album-cover">
          <img 
            v-if="album?.coverImage" 
            :src="album.coverImage.thumbnailUrl" 
            :alt="album.name"
          />
          <div v-else class="no-cover">
            <i class="fas fa-folder"></i>
          </div>
        </div>
        
        <div class="album-details">
          <h1 class="album-name">{{ album?.name }}</h1>
          <p v-if="album?.description" class="album-description">
            {{ album.description }}
          </p>
          
          <div class="album-meta">
            <span class="meta-item">
              <i class="fas fa-image"></i>
              {{ album?.imageCount || 0 }} images
            </span>
            <span class="meta-item">
              <i class="fas fa-calendar"></i>
              Created {{ formatDate(album?.createdAt) }}
            </span>
            <span class="meta-item">
              <i :class="getPrivacyIcon(album?.privacy)"></i>
              {{ getPrivacyText(album?.privacy) }}
            </span>
          </div>
        </div>
      </div>
      
      <div class="album-actions">
        <button @click="editAlbum" class="btn btn-secondary">
          <i class="fas fa-edit"></i>
          Edit
        </button>
        <button @click="shareAlbum" class="btn btn-primary">
          <i class="fas fa-share"></i>
          Share
        </button>
        <button @click="addImages" class="btn btn-success">
          <i class="fas fa-plus"></i>
          Add Images
        </button>
        <button @click="showMoreActions" class="btn btn-secondary">
          <i class="fas fa-ellipsis-v"></i>
        </button>
      </div>
    </div>

    <!-- Album Content -->
    <div class="album-content">
      <!-- Images Grid -->
      <div v-if="images.length > 0" class="images-section">
        <div class="section-header">
          <h2>Images ({{ images.length }})</h2>
          <div class="view-controls">
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
        <div v-if="viewMode === 'grid'" class="images-grid">
          <div 
            v-for="image in images" 
            :key="image.id"
            class="image-card"
            @click="openImage(image)"
          >
            <div class="image-container">
              <img :src="image.thumbnailUrl" :alt="image.title || image.name" />
              <div class="image-overlay">
                <div class="image-actions">
                  <button 
                    @click.stop="removeFromAlbum(image.id)"
                    class="action-btn remove-btn"
                    title="Remove from album"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                  <button 
                    @click.stop="setAsCover(image.id)"
                    class="action-btn cover-btn"
                    title="Set as cover"
                  >
                    <i class="fas fa-star"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="image-info">
              <h4 class="image-title">{{ image.title || image.name }}</h4>
              <p class="image-date">{{ formatDate(image.createdAt) }}</p>
            </div>
          </div>
        </div>

        <!-- List View -->
        <div v-else class="images-list">
          <div 
            v-for="image in images" 
            :key="image.id"
            class="image-list-item"
            @click="openImage(image)"
          >
            <div class="image-thumbnail">
              <img :src="image.thumbnailUrl" :alt="image.title || image.name" />
            </div>
            
            <div class="image-details">
              <div class="image-main">
                <h4 class="image-title">{{ image.title || image.name }}</h4>
                <p v-if="image.description" class="image-description">
                  {{ image.description }}
                </p>
              </div>
              
              <div class="image-meta">
                <span class="image-date">
                  <i class="fas fa-calendar"></i>
                  {{ formatDate(image.createdAt) }}
                </span>
                <span class="image-size">
                  <i class="fas fa-file-image"></i>
                  {{ formatFileSize(image.fileSize) }}
                </span>
              </div>
              
              <div class="image-actions">
                <button 
                  @click.stop="removeFromAlbum(image.id)"
                  class="action-btn remove-btn"
                  title="Remove from album"
                >
                  <i class="fas fa-times"></i>
                </button>
                <button 
                  @click.stop="setAsCover(image.id)"
                  class="action-btn cover-btn"
                  title="Set as cover"
                >
                  <i class="fas fa-star"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-images"></i>
        </div>
        <h3>No images in this album</h3>
        <p>Start building your collection by adding some photos</p>
        <button @click="addImages" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Add Images
        </button>
      </div>
    </div>

    <!-- More Actions Menu -->
    <div 
      v-if="showMoreActionsMenu" 
      class="more-actions-menu"
      :style="moreActionsPosition"
      @click.stop
    >
      <div class="action-item" @click="duplicateAlbum">
        <i class="fas fa-copy"></i>
        Duplicate Album
      </div>
      <div class="action-item" @click="exportAlbum">
        <i class="fas fa-download"></i>
        Export Album
      </div>
      <div class="action-item" @click="reorderImages">
        <i class="fas fa-sort"></i>
        Reorder Images
      </div>
      <div class="action-divider"></div>
      <div class="action-item danger" @click="deleteAlbum">
        <i class="fas fa-trash"></i>
        Delete Album
      </div>
    </div>

    <!-- Add Images Modal -->
    <div v-if="showAddImagesModal" class="modal-overlay" @click="closeAddImagesModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Add Images to Album</h3>
          <button @click="closeAddImagesModal" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="image-search">
            <input
              v-model="imageSearchQuery"
              type="text"
              placeholder="Search your images..."
              class="search-input"
            />
          </div>
          
          <div class="available-images">
            <div 
              v-for="image in availableImages" 
              :key="image.id"
              class="available-image"
              :class="{ 'selected': selectedImageIds.includes(image.id) }"
              @click="toggleImageSelection(image.id)"
            >
              <img :src="image.thumbnailUrl" :alt="image.name" />
              <div class="selection-overlay">
                <i class="fas fa-check"></i>
              </div>
            </div>
          </div>
          
          <div class="selection-summary">
            <p>{{ selectedImageIds.length }} images selected</p>
            <button @click="confirmAddImages" class="btn btn-primary">
              Add to Album
            </button>
          </div>
        </div>
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
          <p>Are you sure you want to delete "<strong>{{ album?.name }}</strong>"?</p>
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
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '../stores/notifications.js'
import AlbumService from '../services/AlbumService'
import ImageService from '../services/ImageService'

const props = defineProps({
  albumId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['update', 'delete'])

const router = useRouter()
const notificationStore = useNotificationStore()

// Data
const album = ref(null)
const images = ref([])
const loading = ref(false)
const viewMode = ref('grid')

// UI state
const showMoreActionsMenu = ref(false)
const showAddImagesModal = ref(false)
const showDeleteModal = ref(false)
const moreActionsPosition = ref({})

// Image selection
const imageSearchQuery = ref('')
const availableImages = ref([])
const selectedImageIds = ref([])

// Computed properties
const isOwner = computed(() => {
  // TODO: Implement ownership check
  return true
})

// Methods
const loadAlbum = async () => {
  loading.value = true
  
  try {
    const [albumResponse, imagesResponse] = await Promise.all([
      AlbumService.getAlbum(props.albumId),
      AlbumService.getAlbumImages(props.albumId)
    ])
    
    if (albumResponse.success) {
      album.value = albumResponse.album
    }
    
    if (imagesResponse.success) {
      images.value = imagesResponse.images || []
    }
  } catch (error) {
    console.error('Error loading album:', error)
    notificationStore.showError('Failed to load album')
  } finally {
    loading.value = false
  }
}

const editAlbum = () => {
  router.push(`/albums/${props.albumId}/edit`)
}

const shareAlbum = () => {
  // TODO: Implement album sharing
  notificationStore.showInfo('Album sharing coming soon!')
}

const addImages = () => {
  showAddImagesModal.value = true
  loadAvailableImages()
}

const showMoreActions = (event) => {
  showMoreActionsMenu.value = true
  
  const rect = event.target.getBoundingClientRect()
  moreActionsPosition.value = {
    top: `${rect.bottom + 5}px`,
    right: `${window.innerWidth - rect.right}px`
  }
}

const closeMoreActions = () => {
  showMoreActionsMenu.value = false
}

const duplicateAlbum = async () => {
  closeMoreActions()
  
  try {
    const newAlbum = await AlbumService.duplicateAlbum(props.albumId, {
      name: `${album.value.name} (Copy)`,
      includeImages: true,
      includeSharing: false
    })
    
    notificationStore.showSuccess('Album duplicated successfully!')
    router.push(`/albums/${newAlbum.id}`)
  } catch (error) {
    console.error('Error duplicating album:', error)
    notificationStore.showError('Failed to duplicate album')
  }
}

const exportAlbum = async () => {
  closeMoreActions()
  
  try {
    const downloadUrl = await AlbumService.exportAlbum(props.albumId, {
      format: 'zip',
      quality: 'high'
    })
    
    const link = document.createElement('a')
    link.href = downloadUrl
    link.download = `${album.value.name}.zip`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    notificationStore.showSuccess('Album export started!')
  } catch (error) {
    console.error('Error exporting album:', error)
    notificationStore.showError('Failed to export album')
  }
}

const reorderImages = () => {
  closeMoreActions()
  // TODO: Implement image reordering
  notificationStore.showInfo('Image reordering coming soon!')
}

const deleteAlbum = () => {
  closeMoreActions()
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
}

const confirmDeleteAlbum = async () => {
  try {
    await AlbumService.deleteAlbum(props.albumId)
    
    notificationStore.showSuccess('Album deleted successfully!')
    closeDeleteModal()
    emit('delete', props.albumId)
  } catch (error) {
    console.error('Error deleting album:', error)
    notificationStore.showError('Failed to delete album')
  }
}

// Image management
const openImage = (image) => {
  // TODO: Open image in modal or navigate to image detail
  console.log('Opening image:', image)
}

const removeFromAlbum = async (imageId) => {
  try {
    await AlbumService.removeImagesFromAlbum(props.albumId, [imageId])
    
    // Remove from local state
    const index = images.value.findIndex(img => img.id === imageId)
    if (index > -1) {
      images.value.splice(index, 1)
    }
    
    notificationStore.showSuccess('Image removed from album')
  } catch (error) {
    console.error('Error removing image from album:', error)
    notificationStore.showError('Failed to remove image from album')
  }
}

const setAsCover = async (imageId) => {
  try {
    await AlbumService.setAlbumCover(props.albumId, imageId)
    
    // Update local state
    if (album.value) {
      album.value.coverImage = images.value.find(img => img.id === imageId)
    }
    
    notificationStore.showSuccess('Cover image updated')
  } catch (error) {
    console.error('Error setting cover image:', error)
    notificationStore.showError('Failed to set cover image')
  }
}

// Add images modal
const closeAddImagesModal = () => {
  showAddImagesModal.value = false
  imageSearchQuery.value = ''
  selectedImageIds.value = []
}

const loadAvailableImages = async () => {
  try {
    const response = await ImageService.getUserImages({ limit: 100 })
    if (response.success && response.images) {
      // Filter out images already in the album
      const albumImageIds = images.value.map(img => img.id)
      availableImages.value = response.images.filter(img => !albumImageIds.includes(img.id))
    }
  } catch (error) {
    console.error('Error loading available images:', error)
  }
}

const toggleImageSelection = (imageId) => {
  const index = selectedImageIds.value.indexOf(imageId)
  if (index > -1) {
    selectedImageIds.value.splice(index, 1)
  } else {
    selectedImageIds.value.push(imageId)
  }
}

const confirmAddImages = async () => {
  if (selectedImageIds.value.length === 0) return
  
  try {
    await AlbumService.addImagesToAlbum(props.albumId, selectedImageIds.value)
    
    // Add to local state
    const newImages = availableImages.value.filter(img => 
      selectedImageIds.value.includes(img.id)
    )
    images.value.push(...newImages)
    
    notificationStore.showSuccess(`${selectedImageIds.value.length} images added to album`)
    closeAddImagesModal()
  } catch (error) {
    console.error('Error adding images to album:', error)
    notificationStore.showError('Failed to add images to album')
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
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatFileSize = (bytes) => {
  if (!bytes) return 'Unknown'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Event listeners
const handleClickOutside = (event) => {
  if (showMoreActionsMenu.value && !event.target.closest('.more-actions-menu')) {
    closeMoreActions()
  }
}

// Lifecycle
onMounted(() => {
  loadAlbum()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.album-detail {
  padding: 2rem 0;
}

.album-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  padding: 2rem;
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-sm);
}

.album-info {
  display: flex;
  gap: 2rem;
  flex: 1;
}

.album-cover {
  width: 200px;
  height: 150px;
  flex-shrink: 0;
}

.album-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--border-radius);
}

.no-cover {
  width: 100%;
  height: 100%;
  background: var(--bg-tertiary);
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: var(--text-light);
}

.album-details {
  flex: 1;
}

.album-name {
  font-size: 2.5rem;
  color: var(--text-primary);
  margin-bottom: 1rem;
}

.album-description {
  color: var(--text-secondary);
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.album-meta {
  display: flex;
  gap: 2rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-light);
  font-size: 0.875rem;
}

.album-actions {
  display: flex;
  gap: 1rem;
  flex-shrink: 0;
}

.album-content {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.images-section {
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  color: var(--text-primary);
  margin: 0;
}

.view-controls {
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
.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.image-card {
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  overflow: hidden;
  cursor: pointer;
  transition: var(--transition);
  border: 1px solid var(--border-color);
}

.image-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.image-container {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-overlay {
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

.image-card:hover .image-overlay {
  opacity: 1;
}

.image-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  padding: 0.5rem;
  cursor: pointer;
  transition: var(--transition);
}

.action-btn:hover {
  background: rgba(0, 0, 0, 0.9);
}

.action-btn.remove-btn:hover {
  background: var(--danger-color);
}

.action-btn.cover-btn:hover {
  background: var(--warning-color);
}

.image-info {
  padding: 1rem;
}

.image-title {
  font-size: 1rem;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.image-date {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 0;
}

/* List View */
.images-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.image-list-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
  border: 1px solid var(--border-color);
}

.image-list-item:hover {
  background: var(--bg-tertiary);
}

.image-thumbnail {
  width: 100px;
  height: 75px;
  flex-shrink: 0;
}

.image-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--border-radius);
}

.image-details {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.image-main {
  flex: 1;
}

.image-main .image-title {
  margin-bottom: 0.25rem;
}

.image-description {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 0;
}

.image-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--text-light);
  margin-right: 1rem;
}

.image-meta span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);
}

.empty-icon {
  font-size: 4rem;
  color: var(--text-light);
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

/* More Actions Menu */
.more-actions-menu {
  position: fixed;
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
  z-index: 1000;
  min-width: 200px;
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
  max-width: 800px;
  width: 90vw;
  max-height: 90vh;
  overflow: hidden;
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
  max-height: 70vh;
  overflow-y: auto;
}

.image-search {
  margin-bottom: 1.5rem;
}

.search-input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 1rem;
}

.available-images {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.available-image {
  position: relative;
  cursor: pointer;
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 2px solid transparent;
  transition: var(--transition);
}

.available-image:hover {
  border-color: var(--primary-color);
}

.available-image.selected {
  border-color: var(--primary-color);
}

.available-image img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.selection-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 123, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: var(--transition);
}

.available-image.selected .selection-overlay {
  opacity: 1;
}

.selection-overlay i {
  color: white;
  font-size: 1.5rem;
}

.selection-summary {
  text-align: center;
  padding: 1rem;
  border-top: 1px solid var(--border-color);
}

.selection-summary p {
  margin-bottom: 1rem;
  color: var(--text-primary);
  font-weight: 600;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.warning-text {
  color: var(--warning-color);
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
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

.btn-success {
  background: var(--success-color);
  color: white;
}

.btn-success:hover {
  background: var(--success-dark);
}

.btn-danger {
  background: var(--danger-color);
  color: white;
}

.btn-danger:hover {
  background: var(--danger-dark);
}

/* Responsive design */
@media (max-width: 768px) {
  .album-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .album-info {
    flex-direction: column;
    gap: 1rem;
  }
  
  .album-cover {
    width: 100%;
    height: 200px;
  }
  
  .album-name {
    font-size: 2rem;
  }
  
  .album-meta {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .album-actions {
    flex-direction: column;
    width: 100%;
  }
  
  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .images-grid {
    grid-template-columns: 1fr;
  }
  
  .image-list-item {
    flex-direction: column;
  }
  
  .image-details {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .image-meta {
    justify-content: space-between;
    margin-right: 0;
  }
}
</style>

<template>
  <div class="image-modal-overlay" @click="closeModal">
    <div class="image-modal" @click.stop>
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="image-title">
          <h2>{{ image.name }}</h2>
          <div class="image-meta">
            <span class="upload-date">
              <i class="fas fa-calendar"></i>
              {{ formatDate(image.uploadedAt) }}
            </span>
            <span class="file-size">
              <i class="fas fa-file"></i>
              {{ formatFileSize(image.size) }}
            </span>
            <span class="privacy">
              <i class="fas fa-lock" v-if="image.privacy === 'private'"></i>
              <i class="fas fa-users" v-else-if="image.privacy === 'friends'"></i>
              <i class="fas fa-globe" v-else></i>
              {{ formatPrivacy(image.privacy) }}
            </span>
          </div>
        </div>
        
        <div class="modal-actions">
          <button 
            @click="toggleFavorite"
            class="action-btn"
            :class="{ active: image.isFavorite }"
            :title="image.isFavorite ? 'Remove from favorites' : 'Add to favorites'"
          >
            <i class="fas fa-heart"></i>
          </button>
          
          <button 
            @click="shareImage"
            class="action-btn"
            title="Share image"
          >
            <i class="fas fa-share-alt"></i>
          </button>
          
          <button 
            @click="downloadImage"
            class="action-btn"
            title="Download image"
          >
            <i class="fas fa-download"></i>
          </button>
          
          <button 
            @click="closeModal"
            class="close-btn"
            title="Close modal"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Modal Content -->
      <div class="modal-content">
        <div class="image-container">
          <img 
            :src="image.fullUrl" 
            :alt="image.name"
            class="full-image"
            @load="imageLoaded = true"
            @error="imageError = true"
          />
          
          <!-- Loading State -->
          <div v-if="!imageLoaded && !imageError" class="image-loading">
            <LoadingSpinner />
            <p>Loading image...</p>
          </div>
          
          <!-- Error State -->
          <div v-if="imageError" class="image-error">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Failed to load image</p>
            <button @click="retryLoad" class="btn btn-secondary">
              Retry
            </button>
          </div>
          
          <!-- Image Navigation -->
          <div v-if="hasNavigation" class="image-navigation">
            <button 
              @click="previousImage"
              class="nav-btn nav-prev"
              :disabled="!hasPrevious"
              title="Previous image"
            >
              <i class="fas fa-chevron-left"></i>
            </button>
            
            <button 
              @click="nextImage"
              class="nav-btn nav-next"
              :disabled="!hasNext"
              title="Next image"
            >
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <!-- Image Details Sidebar -->
        <div class="image-details">
          <div class="details-section">
            <h3>Image Information</h3>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Dimensions:</span>
                <span class="info-value">{{ image.dimensions?.width || 'Unknown' }} × {{ image.dimensions?.height || 'Unknown' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">File Type:</span>
                <span class="info-value">{{ formatMimeType(image.mimeType) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Upload Date:</span>
                <span class="info-value">{{ formatDate(image.uploadedAt) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">File Size:</span>
                <span class="info-value">{{ formatFileSize(image.size) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Privacy:</span>
                <span class="info-value">{{ formatPrivacy(image.privacy) }}</span>
              </div>
            </div>
          </div>

          <div class="details-section">
            <h3>Statistics</h3>
            <div class="stats-grid">
              <div class="stat-item">
                <div class="stat-icon">
                  <i class="fas fa-eye"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ image.views || 0 }}</div>
                  <div class="stat-label">Views</div>
                </div>
              </div>
              
              <div class="stat-item">
                <div class="stat-icon">
                  <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ image.likes || 0 }}</div>
                  <div class="stat-label">Likes</div>
                </div>
              </div>
              
              <div class="stat-item">
                <div class="stat-icon">
                  <i class="fas fa-download"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ image.downloads || 0 }}</div>
                  <div class="stat-label">Downloads</div>
                </div>
              </div>
              
              <div class="stat-item">
                <div class="stat-icon">
                  <i class="fas fa-share"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ image.shares || 0 }}</div>
                  <div class="stat-label">Shares</div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="image.description" class="details-section">
            <h3>Description</h3>
            <p class="image-description">{{ image.description }}</p>
          </div>

          <div v-if="image.tags && image.tags.length > 0" class="details-section">
            <h3>Tags</h3>
            <div class="tags-container">
              <span 
                v-for="tag in image.tags" 
                :key="tag"
                class="tag"
              >
                {{ tag }}
              </span>
            </div>
          </div>

          <div class="details-section">
            <h3>Actions</h3>
            <div class="action-buttons">
              <button @click="editImage" class="btn btn-secondary">
                <i class="fas fa-edit"></i>
                Edit Details
              </button>
              
              <button @click="moveToAlbum" class="btn btn-secondary">
                <i class="fas fa-folder"></i>
                Move to Album
              </button>
              
              <button @click="deleteImage" class="btn btn-danger">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useNotificationStore } from '../stores/notifications.js'
import LoadingSpinner from './LoadingSpinner.vue'

// Props
const props = defineProps({
  image: {
    type: Object,
    required: true
  },
  // Optional: for navigation between images
  images: {
    type: Array,
    default: () => []
  },
  currentIndex: {
    type: Number,
    default: 0
  }
})

// Emits
const emit = defineEmits(['close', 'favorite', 'share', 'download'])

// Store
const notificationsStore = useNotificationStore()

// Local state
const imageLoaded = ref(false)
const imageError = ref(false)

// Computed properties
const hasNavigation = computed(() => props.images.length > 1)
const hasPrevious = computed(() => props.currentIndex > 0)
const hasNext = computed(() => props.currentIndex < props.images.length - 1)

// Methods
const closeModal = () => {
  emit('close')
}

const toggleFavorite = () => {
  emit('favorite', props.image)
}

const shareImage = () => {
  emit('share', props.image)
}

const downloadImage = () => {
  emit('download', props.image)
}

const previousImage = () => {
  if (hasPrevious.value) {
    // TODO: Emit event to parent to change current image
    notificationsStore.showInfo('Previous image functionality coming soon')
  }
}

const nextImage = () => {
  if (hasNext.value) {
    // TODO: Emit event to parent to change current image
    notificationsStore.showInfo('Next image functionality coming soon')
  }
}

const retryLoad = () => {
  imageError.value = false
  imageLoaded.value = false
  // Force image reload by changing src
  const img = document.querySelector('.full-image')
  if (img) {
    img.src = props.image.fullUrl + '?t=' + Date.now()
  }
}

const editImage = () => {
  // TODO: Implement edit functionality
      notificationsStore.showInfo('Edit functionality coming soon')
}

const moveToAlbum = () => {
  // TODO: Implement move to album functionality
      notificationsStore.showInfo('Move to album functionality coming soon')
}

const deleteImage = () => {
  if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
    // TODO: Implement delete functionality
    notificationsStore.showSuccess('Image deleted successfully')
    closeModal()
  }
}

// Utility methods
const formatDate = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatMimeType = (mimeType) => {
  const types = {
    'image/jpeg': 'JPEG',
    'image/png': 'PNG',
    'image/gif': 'GIF',
    'image/webp': 'WebP'
  }
  return types[mimeType] || mimeType
}

const formatPrivacy = (privacy) => {
  const privacyLabels = {
    'private': 'Private',
    'friends': 'Friends Only',
    'public': 'Public'
  }
  return privacyLabels[privacy] || privacy
}
</script>

<style scoped>
.image-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.image-modal {
  background: white;
  border-radius: 12px;
  max-width: 95vw;
  max-height: 95vh;
  width: 1200px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
}

.image-title h2 {
  margin: 0 0 0.5rem 0;
  color: #2c3e50;
  font-size: 1.5rem;
}

.image-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.9rem;
  color: #6c757d;
}

.image-meta span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.modal-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn,
.close-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: white;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dee2e6;
}

.action-btn:hover {
  background: #f8f9fa;
  transform: scale(1.05);
}

.action-btn.active {
  background: #dc3545;
  color: white;
  border-color: #dc3545;
}

.close-btn:hover {
  background: #dc3545;
  color: white;
  border-color: #dc3545;
}

.modal-content {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.image-container {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #000;
  min-height: 400px;
}

.full-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.image-loading,
.image-error {
  text-align: center;
  color: white;
}

.image-loading p,
.image-error p {
  margin: 1rem 0;
}

.image-error i {
  font-size: 3rem;
  color: #dc3545;
  margin-bottom: 1rem;
}

.image-navigation {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 0 1rem;
  pointer-events: none;
}

.nav-btn {
  width: 50px;
  height: 50px;
  border: none;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: auto;
}

.nav-btn:hover:not(:disabled) {
  background: white;
  transform: scale(1.1);
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.image-details {
  width: 350px;
  background: #f8f9fa;
  padding: 1.5rem;
  overflow-y: auto;
  border-left: 1px solid #dee2e6;
}

.details-section {
  margin-bottom: 2rem;
}

.details-section h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
  font-size: 1.1rem;
  border-bottom: 2px solid #dee2e6;
  padding-bottom: 0.5rem;
}

.info-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-label {
  font-weight: 600;
  color: #6c757d;
}

.info-value {
  color: #2c3e50;
}

.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  line-height: 1;
}

.stat-label {
  font-size: 0.8rem;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.image-description {
  color: #2c3e50;
  line-height: 1.6;
  margin: 0;
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag {
  background: #007bff;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn {
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

/* Responsive design */
@media (max-width: 1200px) {
  .image-modal {
    width: 95vw;
  }
  
  .modal-content {
    flex-direction: column;
  }
  
  .image-details {
    width: 100%;
    border-left: none;
    border-top: 1px solid #dee2e6;
  }
}

@media (max-width: 768px) {
  .image-modal-overlay {
    padding: 0.5rem;
  }
  
  .modal-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .image-meta {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .modal-actions {
    align-self: flex-end;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>

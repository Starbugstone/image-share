<template>
  <AppLayout>
    <div class="album-create-page">
      <div class="container">
        <div class="page-header">
          <h1>
            <i class="fas fa-folder-plus"></i>
            Create New Album
          </h1>
          <p>Organize your photos into beautiful collections</p>
        </div>

        <div class="create-form-container">
          <form @submit.prevent="createAlbum" class="album-form">
            <!-- Basic Information -->
            <div class="form-section">
              <h3>
                <i class="fas fa-info-circle"></i>
                Basic Information
              </h3>
              
              <div class="form-group">
                <label for="album-name">Album Name *</label>
                <input
                  id="album-name"
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'error': errors.name }"
                  placeholder="Enter album name"
                  required
                  maxlength="100"
                />
                <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
                <small class="form-help">Choose a descriptive name for your album</small>
              </div>

              <div class="form-group">
                <label for="album-description">Description</label>
                <textarea
                  id="album-description"
                  v-model="form.description"
                  class="form-control"
                  :class="{ 'error': errors.description }"
                  placeholder="Describe your album (optional)"
                  rows="3"
                  maxlength="500"
                ></textarea>
                <span v-if="errors.description" class="error-message">{{ errors.description }}</span>
                <small class="form-help">Add context about the photos in this album</small>
              </div>
            </div>

            <!-- Privacy Settings -->
            <div class="form-section">
              <h3>
                <i class="fas fa-shield-alt"></i>
                Privacy Settings
              </h3>
              
              <div class="form-group">
                <label for="album-privacy">Privacy Level *</label>
                <select
                  id="album-privacy"
                  v-model="form.privacy"
                  class="form-control"
                  required
                >
                  <option value="private">
                    <i class="fas fa-lock"></i> Private - Only you can see
                  </option>
                  <option value="friends">
                    <i class="fas fa-users"></i> Friends Only - Your friends can see
                  </option>
                  <option value="public">
                    <i class="fas fa-globe"></i> Public - Anyone can see
                  </option>
                </select>
                <small class="form-help">Control who can view your album</small>
              </div>

              <div class="form-group">
                <label class="checkbox-label">
                  <input
                    v-model="form.allowComments"
                    type="checkbox"
                    class="form-checkbox"
                  />
                  <span class="checkmark"></span>
                  Allow comments on this album
                </label>
                <small class="form-help">Let others leave comments on your photos</small>
              </div>
            </div>

            <!-- Cover Image -->
            <div class="form-section">
              <h3>
                <i class="fas fa-image"></i>
                Cover Image
              </h3>
              
              <div class="cover-image-section">
                <div v-if="!form.coverImage" class="cover-placeholder">
                  <div class="cover-upload-area" @click="openCoverSelector">
                    <i class="fas fa-plus"></i>
                    <p>Select Cover Image</p>
                    <small>Choose a photo to represent your album</small>
                  </div>
                </div>
                
                <div v-else class="cover-preview">
                  <img :src="form.coverImage.thumbnailUrl" :alt="form.coverImage.name" />
                  <div class="cover-actions">
                    <button type="button" @click="openCoverSelector" class="btn btn-secondary btn-sm">
                      <i class="fas fa-edit"></i>
                      Change
                    </button>
                    <button type="button" @click="removeCoverImage" class="btn btn-danger btn-sm">
                      <i class="fas fa-trash"></i>
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Initial Images -->
            <div class="form-section">
              <h3>
                <i class="fas fa-images"></i>
                Add Initial Images (Optional)
              </h3>
              
              <div class="initial-images-section">
                <div v-if="selectedImages.length === 0" class="no-images">
                  <p>No images selected yet</p>
                  <button type="button" @click="openImageSelector" class="btn btn-secondary">
                    <i class="fas fa-plus"></i>
                    Select Images
                  </button>
                </div>
                
                <div v-else class="selected-images">
                  <div class="selected-header">
                    <h4>{{ selectedImages.length }} images selected</h4>
                    <button type="button" @click="openImageSelector" class="btn btn-secondary btn-sm">
                      <i class="fas fa-edit"></i>
                      Change Selection
                    </button>
                  </div>
                  
                  <div class="images-grid">
                    <div 
                      v-for="image in selectedImages" 
                      :key="image.id"
                      class="selected-image"
                    >
                      <img :src="image.thumbnailUrl" :alt="image.name" />
                      <div class="image-info">
                        <span class="image-name">{{ image.name }}</span>
                        <span class="image-date">{{ formatDate(image.createdAt) }}</span>
                      </div>
                      <button 
                        type="button" 
                        @click="removeImage(image.id)"
                        class="remove-image"
                        title="Remove image"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <router-link to="/albums" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Cancel
              </router-link>
              
              <button 
                type="submit" 
                class="btn btn-primary"
                :disabled="isCreating || !isFormValid"
              >
                <i v-if="isCreating" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-save"></i>
                {{ isCreating ? 'Creating Album...' : 'Create Album' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Cover Image Selector Modal -->
      <div v-if="showCoverSelector" class="modal-overlay" @click="closeCoverSelector">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Select Cover Image</h3>
            <button @click="closeCoverSelector" class="modal-close">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="modal-body">
            <div class="image-search">
              <input
                v-model="coverSearchQuery"
                type="text"
                placeholder="Search your images..."
                class="search-input"
              />
            </div>
            
            <div class="cover-images-grid">
              <div 
                v-for="image in coverImageResults" 
                :key="image.id"
                class="cover-image-option"
                :class="{ 'selected': form.coverImage?.id === image.id }"
                @click="selectCoverImage(image)"
              >
                <img :src="image.thumbnailUrl" :alt="image.name" />
                <div class="image-overlay">
                  <i class="fas fa-check"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Image Selector Modal -->
      <div v-if="showImageSelector" class="modal-overlay" @click="closeImageSelector">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Select Images for Album</h3>
            <button @click="closeImageSelector" class="modal-close">
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
            
            <div class="images-selection-grid">
              <div 
                v-for="image in imageSelectionResults" 
                :key="image.id"
                class="image-selection-option"
                :class="{ 'selected': isImageSelected(image.id) }"
                @click="toggleImageSelection(image)"
              >
                <img :src="image.thumbnailUrl" :alt="image.name" />
                <div class="selection-overlay">
                  <i class="fas fa-check"></i>
                </div>
              </div>
            </div>
            
            <div class="selection-summary">
              <p>{{ selectedImages.length }} images selected</p>
              <button @click="confirmImageSelection" class="btn btn-primary">
                Confirm Selection
              </button>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications.js'
import AppLayout from '../components/AppLayout.vue'
import AlbumService from '../services/AlbumService'
import ImageService from '../services/ImageService'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Form data
const form = ref({
  name: '',
  description: '',
  privacy: 'private',
  allowComments: false,
  coverImage: null
})

// Form validation errors
const errors = ref({})

// UI state
const isCreating = ref(false)
const showCoverSelector = ref(false)
const showImageSelector = ref(false)

// Image selection
const selectedImages = ref([])
const coverSearchQuery = ref('')
const imageSearchQuery = ref('')
const coverImageResults = ref([])
const imageSelectionResults = ref([])

// Computed properties
const isFormValid = computed(() => {
  return form.value.name.trim().length > 0 && 
         form.value.name.trim().length <= 100 &&
         form.value.description.length <= 500
})

// Methods
const validateForm = () => {
  errors.value = {}
  
  if (!form.value.name.trim()) {
    errors.value.name = 'Album name is required'
  } else if (form.value.name.trim().length > 100) {
    errors.value.name = 'Album name must be 100 characters or less'
  }
  
  if (form.value.description.length > 500) {
    errors.value.description = 'Description must be 500 characters or less'
  }
  
  return Object.keys(errors.value).length === 0
}

const createAlbum = async () => {
  if (!validateForm()) return
  
  isCreating.value = true
  
  try {
    const albumData = {
      name: form.value.name.trim(),
      description: form.value.description.trim(),
      privacy: form.value.privacy,
      allowComments: form.value.allowComments,
      coverImageId: form.value.coverImage?.id
    }
    
    const album = await AlbumService.createAlbum(albumData)
    
    // Add initial images if any selected
    if (selectedImages.value.length > 0) {
      const imageIds = selectedImages.value.map(img => img.id)
      await AlbumService.addImagesToAlbum(album.id, imageIds)
    }
    
    notificationStore.showSuccess('Album created successfully!')
    
    // Redirect to the new album
    router.push(`/albums/${album.id}`)
    
  } catch (error) {
    console.error('Error creating album:', error)
    notificationStore.error('Failed to create album. Please try again.')
  } finally {
    isCreating.value = false
  }
}

// Cover image methods
const openCoverSelector = () => {
  showCoverSelector.value = true
  loadCoverImageResults()
}

const closeCoverSelector = () => {
  showCoverSelector.value = false
  coverSearchQuery.value = ''
}

const selectCoverImage = (image) => {
  form.value.coverImage = image
  closeCoverSelector()
}

const removeCoverImage = () => {
  form.value.coverImage = null
}

const loadCoverImageResults = async () => {
  try {
    const response = await ImageService.getUserImages({ limit: 50 })
    if (response.success && response.images) {
      coverImageResults.value = response.images
    }
  } catch (error) {
    console.error('Error loading images for cover selection:', error)
  }
}

// Image selection methods
const openImageSelector = () => {
  showImageSelector.value = true
  loadImageSelectionResults()
}

const closeImageSelector = () => {
  showImageSelector.value = false
  imageSearchQuery.value = ''
}

const toggleImageSelection = (image) => {
  const index = selectedImages.value.findIndex(img => img.id === image.id)
  if (index > -1) {
    selectedImages.value.splice(index, 1)
  } else {
    selectedImages.value.push(image)
  }
}

const isImageSelected = (imageId) => {
  return selectedImages.value.some(img => img.id === imageId)
}

const removeImage = (imageId) => {
  const index = selectedImages.value.findIndex(img => img.id === imageId)
  if (index > -1) {
    selectedImages.value.splice(index, 1)
  }
}

const confirmImageSelection = () => {
  closeImageSelector()
}

const loadImageSelectionResults = async () => {
  try {
    const response = await ImageService.getUserImages({ limit: 100 })
    if (response.success && response.images) {
      imageSelectionResults.value = response.images
    }
  } catch (error) {
    console.error('Error loading images for selection:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  // Initialize form with user preferences
  if (authStore.user?.preferences?.defaultAlbumPrivacy) {
    form.value.privacy = authStore.user.preferences.defaultAlbumPrivacy
  }
})
</script>

<style scoped>
.album-create-page {
  padding: 2rem 0;
  min-height: 100vh;
  background: var(--bg-secondary);
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2.5rem;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.page-header p {
  color: var(--text-secondary);
  font-size: 1.1rem;
}

.create-form-container {
  max-width: 800px;
  margin: 0 auto;
}

.album-form {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-lg);
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--border-color);
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.form-section h3 {
  color: var(--text-primary);
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.25rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--text-primary);
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: var(--transition);
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-control.error {
  border-color: var(--danger-color);
}

.error-message {
  color: var(--danger-color);
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

.form-help {
  color: var(--text-light);
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  font-weight: 500;
  color: var(--text-primary);
}

.form-checkbox {
  width: 18px;
  height: 18px;
  accent-color: var(--primary-color);
}

.cover-image-section {
  margin-top: 1rem;
}

.cover-placeholder {
  text-align: center;
}

.cover-upload-area {
  border: 2px dashed var(--border-color);
  border-radius: var(--border-radius);
  padding: 2rem;
  cursor: pointer;
  transition: var(--transition);
}

.cover-upload-area:hover {
  border-color: var(--primary-color);
  background: var(--bg-secondary);
}

.cover-upload-area i {
  font-size: 2rem;
  color: var(--text-light);
  margin-bottom: 0.5rem;
}

.cover-upload-area p {
  color: var(--text-primary);
  margin-bottom: 0.25rem;
  font-weight: 600;
}

.cover-upload-area small {
  color: var(--text-secondary);
}

.cover-preview {
  position: relative;
  max-width: 300px;
}

.cover-preview img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: var(--border-radius);
}

.cover-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.initial-images-section {
  margin-top: 1rem;
}

.no-images {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.selected-images {
  margin-top: 1rem;
}

.selected-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.selected-header h4 {
  margin: 0;
  color: var(--text-primary);
}

.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
}

.selected-image {
  position: relative;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.selected-image img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.image-info {
  padding: 0.75rem;
}

.image-name {
  display: block;
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.image-date {
  color: var(--text-secondary);
  font-size: 0.75rem;
}

.remove-image {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: var(--danger-color);
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.8rem;
}

.remove-image:hover {
  background: var(--danger-dark);
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 2rem;
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

.btn-primary:hover:not(:disabled) {
  background: var(--primary-dark);
}

.btn-primary:disabled {
  background: var(--text-light);
  cursor: not-allowed;
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
  max-width: 90vw;
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

.cover-images-grid,
.images-selection-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.cover-image-option,
.image-selection-option {
  position: relative;
  cursor: pointer;
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 2px solid transparent;
  transition: var(--transition);
}

.cover-image-option:hover,
.image-selection-option:hover {
  border-color: var(--primary-color);
}

.cover-image-option.selected,
.image-selection-option.selected {
  border-color: var(--primary-color);
}

.cover-image-option img,
.image-selection-option img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.image-overlay,
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

.cover-image-option.selected .image-overlay,
.image-selection-option.selected .selection-overlay {
  opacity: 1;
}

.image-overlay i,
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

/* Responsive design */
@media (max-width: 768px) {
  .album-form {
    padding: 1.5rem;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .cover-actions {
    flex-direction: column;
  }
  
  .images-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
  
  .cover-images-grid,
  .images-selection-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  }
}
</style>

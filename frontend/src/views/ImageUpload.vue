<template>
  <div class="upload-page">
    <AppLayout>
      <div class="container">
        <div class="upload-header">
          <h1>Upload Images</h1>
          <p>Share your photos with friends and family</p>
        </div>

        <!-- Upload Area -->
        <div class="upload-area">
          <div 
            class="drop-zone"
            :class="{ 
              'drag-over': isDragOver, 
              'has-files': selectedFiles.length > 0 
            }"
            @drop="handleDrop"
            @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false"
            @click="triggerFileInput"
          >
            <div v-if="selectedFiles.length === 0" class="drop-zone-content">
              <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
              </div>
              <h3>Drag & Drop Images Here</h3>
              <p>or click to browse files</p>
              <p class="file-types">Supported: JPG, PNG, GIF, WebP (Max 10MB)</p>
            </div>
            
            <div v-else class="files-preview">
              <h3>Selected Files ({{ selectedFiles.length }})</h3>
              <div class="files-grid">
                <div 
                  v-for="(file, index) in selectedFiles" 
                  :key="index"
                  class="file-preview"
                >
                  <div class="preview-image">
                    <img 
                      v-if="file.preview" 
                      :src="file.preview" 
                      :alt="file.name"
                    />
                    <div v-else class="no-preview">
                      <i class="fas fa-file-image"></i>
                    </div>
                  </div>
                  <div class="file-info">
                    <div class="file-name">{{ file.name }}</div>
                    <div class="file-size">{{ formatFileSize(file.size) }}</div>
                    <div class="file-status" :class="file.status">
                      <span v-if="file.status === 'pending'">Pending</span>
                      <span v-if="file.status === 'uploading'">Uploading...</span>
                      <span v-if="file.status === 'success'">✓ Uploaded</span>
                      <span v-if="file.status === 'error'">✗ Failed</span>
                    </div>
                  </div>
                  <button 
                    v-if="file.status === 'pending'"
                    class="remove-file" 
                    @click="removeFile(index)"
                    title="Remove file"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Hidden file input -->
          <input
            ref="fileInput"
            type="file"
            multiple
            accept="image/*"
            @change="handleFileSelect"
            style="display: none"
          />

          <!-- Upload Actions -->
          <div v-if="selectedFiles.length > 0" class="upload-actions">
            <div class="upload-options">
              <div class="form-group">
                <label for="album-select">Add to Album (Optional):</label>
                <select 
                  id="album-select" 
                  v-model="selectedAlbum"
                  class="form-control"
                >
                  <option value="">No album</option>
                  <option 
                    v-for="album in userAlbums" 
                    :key="album.id" 
                    :value="album.id"
                  >
                    {{ album.name }}
                  </option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="privacy-select">Privacy:</label>
                <select 
                  id="privacy-select" 
                  v-model="privacy"
                  class="form-control"
                >
                  <option value="private">Private</option>
                  <option value="friends">Friends Only</option>
                  <option value="public">Public</option>
                </select>
              </div>
            </div>

            <div class="upload-buttons">
              <button 
                class="btn btn-secondary"
                @click="clearFiles"
                :disabled="isUploading"
              >
                Clear All
              </button>
              <button 
                class="btn btn-primary"
                @click="uploadFiles"
                :disabled="isUploading || !hasValidFiles"
              >
                <i v-if="isUploading" class="fas fa-spinner fa-spin"></i>
                {{ isUploading ? 'Uploading...' : `Upload ${validFilesCount} Images` }}
              </button>
            </div>
          </div>
        </div>

        <!-- Upload Progress -->
        <div v-if="isUploading" class="upload-progress">
          <div class="progress-header">
            <h3>Upload Progress</h3>
            <span class="progress-text">{{ uploadedCount }} / {{ totalFilesCount }}</span>
          </div>
          <div class="progress-bar">
            <div 
              class="progress-fill" 
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
        </div>

        <!-- Recent Uploads -->
        <div v-if="recentUploads.length > 0" class="recent-uploads">
          <h3>Recent Uploads</h3>
          <div class="recent-grid">
            <div 
              v-for="image in recentUploads" 
              :key="image.id"
              class="recent-item"
            >
              <img :src="image.thumbnailUrl" :alt="image.name" />
              <div class="recent-info">
                <div class="recent-name">{{ image.name }}</div>
                <div class="recent-date">{{ formatDate(image.uploadedAt) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notification'
import AppLayout from '../components/AppLayout.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import ImageService from '../services/ImageService'

// Router and stores
const router = useRouter()
const authStore = useAuthStore()
const notificationsStore = useNotificationStore()

// File handling
const fileInput = ref(null)
const selectedFiles = ref([])
const isDragOver = ref(false)

// Upload state
const isUploading = ref(false)
const uploadProgress = ref(0)
const uploadedCount = ref(0)
const totalFilesCount = ref(0)

// Form options
const selectedAlbum = ref('')
const privacy = ref('private')
const userAlbums = ref([])
const recentUploads = ref([])

// Computed properties
const validFilesCount = computed(() => 
  selectedFiles.value.filter(file => file.status !== 'error').length
)

const hasValidFiles = computed(() => 
  selectedFiles.value.some(file => file.status === 'pending')
)

// File validation
const validateFile = (file) => {
  return ImageService.validateImageFile(file)
}

// File handling methods
const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  addFiles(files)
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false
  
  const files = Array.from(event.dataTransfer.files)
  addFiles(files)
}

// Paste handling (support pasting images from clipboard)
const handlePaste = (event) => {
  const items = event.clipboardData?.items || []
  const files = []
  for (let i = 0; i < items.length; i++) {
    const item = items[i]
    if (item.kind === 'file') {
      const file = item.getAsFile()
      if (file && file.type.startsWith('image/')) {
        files.push(file)
      }
    }
  }
  if (files.length > 0) {
    addFiles(files)
  }
}

const addFiles = (files) => {
  files.forEach(file => {
    const validation = validateFile(file)
    
    if (validation.valid) {
      const fileObj = {
        file,
        name: file.name,
        size: file.size,
        type: file.type,
        status: 'pending',
        preview: null
      }
      
      // Create preview
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          fileObj.preview = e.target.result
        }
        reader.readAsDataURL(file)
      }
      
      selectedFiles.value.push(fileObj)
    } else {
      notificationsStore.showError(`File "${file.name}": ${validation.error}`)
    }
  })
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
}

const clearFiles = () => {
  selectedFiles.value = []
  selectedAlbum.value = ''
  privacy.value = 'private'
}

// Upload methods
const uploadFiles = async () => {
  if (!hasValidFiles.value) return
  
  isUploading.value = true
  uploadProgress.value = 0
  uploadedCount.value = 0
  totalFilesCount.value = validFilesCount.value
  
  const filesToUpload = selectedFiles.value.filter(file => file.status === 'pending')
  
  try {
    for (let i = 0; i < filesToUpload.length; i++) {
      const fileObj = filesToUpload[i]
      fileObj.status = 'uploading'
      
      // Simulate upload progress (replace with actual API call)
      await uploadSingleFile(fileObj)
      
      fileObj.status = 'success'
      uploadedCount.value++
      uploadProgress.value = (uploadedCount.value / totalFilesCount.value) * 100
      
      // Add to recent uploads
      recentUploads.value.unshift({
        id: Date.now() + i,
        name: fileObj.name,
        thumbnailUrl: fileObj.preview,
        uploadedAt: new Date()
      })
    }
    
    notificationsStore.showSuccess(`Successfully uploaded ${uploadedCount.value} images`)
    
    // Clear files after successful upload
    setTimeout(() => {
      clearFiles()
    }, 2000)
    
  } catch (error) {
    console.error('Upload error:', error)
    notificationsStore.showError('Upload failed. Please try again.')
  } finally {
    isUploading.value = false
  }
}

const uploadSingleFile = async (fileObj) => {
  try {
    const options = {
      albumId: selectedAlbum.value || undefined,
      privacy: privacy.value,
      onProgress: (progress) => {
        // Update individual file progress if needed
        console.log(`File ${fileObj.name}: ${progress}%`)
      }
    }
    
    const result = await ImageService.uploadImage(fileObj.file, options)
    return result
  } catch (error) {
    throw new Error(`Upload failed for ${fileObj.name}: ${error.message}`)
  }
}

// Utility methods
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

// Load user albums on mount
onMounted(async () => {
  // TODO: Load user albums from API
  userAlbums.value = [
    { id: 1, name: 'Vacation 2024' },
    { id: 2, name: 'Family Photos' },
    { id: 3, name: 'Work Projects' }
  ]
  
  // TODO: Load recent uploads from API
  recentUploads.value = []

  // Enable paste-to-upload globally
  window.addEventListener('paste', handlePaste)
})

onUnmounted(() => {
  window.removeEventListener('paste', handlePaste)
})
</script>

<style scoped>
.upload-page {
  min-height: 100vh;
  background: #f8f9fa;
}

.upload-header {
  text-align: center;
  margin-bottom: 2rem;
}

.upload-header h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.upload-header p {
  color: #6c757d;
  font-size: 1.1rem;
}

.upload-area {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 2rem;
}

.drop-zone {
  border: 3px dashed #dee2e6;
  border-radius: 8px;
  margin: 1.5rem;
  transition: all 0.3s ease;
  cursor: pointer;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.drop-zone:hover {
  border-color: #007bff;
  background-color: #f8f9ff;
}

.drop-zone.drag-over {
  border-color: #28a745;
  background-color: #f8fff9;
  transform: scale(1.02);
}

.drop-zone.has-files {
  min-height: auto;
  padding: 1rem;
}

.drop-zone-content {
  text-align: center;
  padding: 2rem;
}

.upload-icon {
  font-size: 4rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.drop-zone-content h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.drop-zone-content p {
  color: #6c757d;
  margin-bottom: 0.5rem;
}

.file-types {
  font-size: 0.9rem;
  color: #adb5bd;
}

.files-preview {
  width: 100%;
}

.files-preview h3 {
  margin-bottom: 1rem;
  color: #2c3e50;
}

.files-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.file-preview {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  position: relative;
  border: 1px solid #dee2e6;
}

.preview-image {
  width: 100%;
  height: 120px;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 0.5rem;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preview-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-preview {
  color: #6c757d;
  font-size: 2rem;
}

.file-info {
  text-align: center;
}

.file-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
  font-size: 0.9rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-size {
  color: #6c757d;
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
}

.file-status {
  font-size: 0.8rem;
  font-weight: 600;
}

.file-status.pending {
  color: #6c757d;
}

.file-status.uploading {
  color: #007bff;
}

.file-status.success {
  color: #28a745;
}

.file-status.error {
  color: #dc3545;
}

.remove-file {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: #dc3545;
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

.remove-file:hover {
  background: #c82333;
}

.upload-actions {
  padding: 1.5rem;
  border-top: 1px solid #dee2e6;
  background: #f8f9fa;
}

.upload-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.form-control {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 0.9rem;
}

.upload-buttons {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-primary:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #545b62;
}

.upload-progress {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.progress-header h3 {
  margin: 0;
  color: #2c3e50;
}

.progress-text {
  color: #6c757d;
  font-weight: 600;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #28a745;
  transition: width 0.3s ease;
}

.recent-uploads {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
}

.recent-uploads h3 {
  margin-bottom: 1rem;
  color: #2c3e50;
}

.recent-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
}

.recent-item {
  text-align: center;
}

.recent-item img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.recent-name {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.recent-date {
  color: #6c757d;
  font-size: 0.8rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .upload-options {
    grid-template-columns: 1fr;
  }
  
  .upload-buttons {
    flex-direction: column;
  }
  
  .files-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }
  
  .recent-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>

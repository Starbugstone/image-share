<template>
  <div v-if="show" class="share-modal-overlay" @click="closeModal">
    <div class="share-modal" @click.stop>
      <!-- Modal Header -->
      <div class="modal-header">
        <h2>
          <i class="fas fa-share-alt"></i>
          Share {{ itemType === 'image' ? 'Image' : 'Album' }}
        </h2>
        <button @click="closeModal" class="close-btn" title="Close">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <!-- Item Preview -->
        <div class="item-preview">
          <div class="preview-image">
            <img 
              v-if="itemData?.thumbnailUrl" 
              :src="itemData.thumbnailUrl" 
              :alt="itemData.name || itemData.title"
            />
            <div v-else class="no-preview">
              <i :class="itemType === 'image' ? 'fas fa-image' : 'fas fa-folder'"></i>
            </div>
          </div>
          <div class="preview-info">
            <h3>{{ itemData?.name || itemData?.title || 'Untitled' }}</h3>
            <p v-if="itemData?.description">{{ itemData.description }}</p>
            <span class="item-type">{{ itemType === 'image' ? 'Image' : 'Album' }}</span>
          </div>
        </div>

        <!-- Share Options -->
        <div class="share-options">
          <div class="option-tabs">
            <button 
              @click="activeTab = 'users'"
              class="tab-btn"
              :class="{ 'active': activeTab === 'users' }"
            >
              <i class="fas fa-users"></i>
              Share with Users
            </button>
            <button 
              @click="activeTab = 'public'"
              class="tab-btn"
              :class="{ 'active': activeTab === 'public' }"
            >
              <i class="fas fa-globe"></i>
              Public Link
            </button>
            <button 
              @click="activeTab = 'templates'"
              class="tab-btn"
              :class="{ 'active': activeTab === 'templates' }"
            >
              <i class="fas fa-save"></i>
              Templates
            </button>
          </div>

          <!-- Share with Users Tab -->
          <div v-if="activeTab === 'users'" class="tab-content">
            <!-- User Search -->
            <div class="user-search">
              <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input
                  v-model="userSearchQuery"
                  type="text"
                  placeholder="Search users by username or email..."
                  class="search-input"
                  @input="searchUsers"
                />
                <button 
                  v-if="userSearchQuery" 
                  @click="clearUserSearch" 
                  class="clear-search"
                  title="Clear search"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>

            <!-- Search Results -->
            <div v-if="userSearchResults.length > 0" class="search-results">
              <h4>Search Results</h4>
              <div class="users-list">
                <div 
                  v-for="user in userSearchResults" 
                  :key="user.id"
                  class="user-item"
                  :class="{ 'selected': selectedUsers.some(u => u.id === user.id) }"
                  @click="toggleUserSelection(user)"
                >
                  <div class="user-avatar">
                    <img 
                      v-if="user.profile?.avatarUrl" 
                      :src="user.profile.avatarUrl" 
                      :alt="user.username"
                    />
                    <div v-else class="default-avatar">
                      <i class="fas fa-user"></i>
                    </div>
                  </div>
                  <div class="user-info">
                    <span class="username">{{ user.username }}</span>
                    <span v-if="user.email" class="email">{{ user.email }}</span>
                  </div>
                  <div class="selection-indicator">
                    <i v-if="selectedUsers.some(u => u.id === user.id)" class="fas fa-check"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Selected Users -->
            <div v-if="selectedUsers.length > 0" class="selected-users">
              <h4>Selected Users ({{ selectedUsers.length }})</h4>
              <div class="selected-users-list">
                <div 
                  v-for="user in selectedUsers" 
                  :key="user.id"
                  class="selected-user"
                >
                  <div class="user-avatar">
                    <img 
                      v-if="user.profile?.avatarUrl" 
                      :src="user.profile.avatarUrl" 
                      :alt="user.username"
                    />
                    <div v-else class="default-avatar">
                      <i class="fas fa-user"></i>
                    </div>
                  </div>
                  <span class="username">{{ user.username }}</span>
                  <button 
                    @click="removeUser(user.id)"
                    class="remove-user-btn"
                    title="Remove user"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- No Results -->
            <div v-else-if="userSearchQuery && userSearchResults.length === 0" class="no-results">
              <p>No users found matching "{{ userSearchQuery }}"</p>
              <button @click="clearUserSearch" class="btn btn-secondary">
                Clear Search
              </button>
            </div>

            <!-- Start Search Prompt -->
            <div v-else-if="!userSearchQuery" class="start-search">
              <div class="search-prompt">
                <i class="fas fa-search"></i>
                <p>Start typing to search for users</p>
              </div>
            </div>
          </div>

          <!-- Public Link Tab -->
          <div v-if="activeTab === 'public'" class="tab-content">
            <div class="public-link-section">
              <div class="link-status">
                <div class="status-indicator">
                  <i class="fas fa-circle" :class="publicLinkStatus"></i>
                  <span>{{ publicLinkStatusText }}</span>
                </div>
              </div>

              <div v-if="publicLink" class="public-link-info">
                <div class="link-display">
                  <input 
                    :value="publicLink" 
                    readonly 
                    class="link-input"
                    @click="$event.target.select()"
                  />
                  <button @click="copyPublicLink" class="copy-btn" title="Copy link">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
                <div class="link-options">
                  <label class="checkbox-label">
                    <input 
                      v-model="publicLinkOptions.allowDownload" 
                      type="checkbox"
                      class="form-checkbox"
                    />
                    Allow downloads
                  </label>
                  <label class="checkbox-label">
                    <input 
                      v-model="publicLinkOptions.allowComments" 
                      type="checkbox"
                      class="form-checkbox"
                    />
                    Allow comments
                  </label>
                  <label class="checkbox-label">
                    <input 
                      v-model="publicLinkOptions.expires" 
                      type="checkbox"
                      class="form-checkbox"
                    />
                    Set expiration date
                  </label>
                </div>
                <div v-if="publicLinkOptions.expires" class="expiration-date">
                  <label>Expires on:</label>
                  <input 
                    v-model="publicLinkOptions.expirationDate" 
                    type="date"
                    class="date-input"
                  />
                </div>
                <button @click="updatePublicLink" class="btn btn-primary">
                  Update Settings
                </button>
              </div>

              <div v-else class="create-public-link">
                <p>Create a public link that anyone can access</p>
                <div class="link-options">
                  <label class="checkbox-label">
                    <input 
                      v-model="publicLinkOptions.allowDownload" 
                      type="checkbox"
                      class="form-checkbox"
                    />
                    Allow downloads
                  </label>
                  <label class="checkbox-label">
                    <input 
                      v-model="publicLinkOptions.allowComments" 
                      type="checkbox"
                      class="form-checkbox"
                    />
                    Allow comments
                  </label>
                </div>
                <button @click="createPublicLink" class="btn btn-primary">
                  Create Public Link
                </button>
              </div>
            </div>
          </div>

          <!-- Templates Tab -->
          <div v-if="activeTab === 'templates'" class="tab-content">
            <div class="templates-section">
              <div class="templates-header">
                <h4>Share Templates</h4>
                <button @click="showCreateTemplate = true" class="btn btn-secondary btn-sm">
                  <i class="fas fa-plus"></i>
                  New Template
                </button>
              </div>

              <div v-if="shareTemplates.length > 0" class="templates-list">
                <div 
                  v-for="template in shareTemplates" 
                  :key="template.id"
                  class="template-item"
                  @click="useTemplate(template)"
                >
                  <div class="template-info">
                    <h5>{{ template.name }}</h5>
                    <p>{{ template.description }}</p>
                  </div>
                  <div class="template-actions">
                    <button @click.stop="editTemplate(template)" class="btn btn-secondary btn-sm">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click.stop="deleteTemplate(template.id)" class="btn btn-danger btn-sm">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div v-else class="no-templates">
                <p>No share templates created yet</p>
                <button @click="showCreateTemplate = true" class="btn btn-primary">
                  Create Your First Template
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Share Message -->
        <div class="share-message">
          <label for="share-message">Message (optional):</label>
          <textarea
            id="share-message"
            v-model="shareMessage"
            placeholder="Add a personal message..."
            rows="3"
            maxlength="500"
            class="message-input"
          ></textarea>
          <div class="message-counter">
            {{ shareMessage.length }}/500
          </div>
        </div>

        <!-- Permission Settings -->
        <div class="permission-settings">
          <h4>Permission Settings</h4>
          <div class="permissions-grid">
            <label class="checkbox-label">
              <input 
                v-model="permissions.view" 
                type="checkbox"
                class="form-checkbox"
              />
              <span>View</span>
            </label>
            <label class="checkbox-label">
              <input 
                v-model="permissions.download" 
                type="checkbox"
                class="form-checkbox"
              />
              <span>Download</span>
            </label>
            <label class="checkbox-label">
              <input 
                v-model="permissions.comment" 
                type="checkbox"
                class="form-checkbox"
              />
              <span>Comment</span>
            </label>
            <label class="checkbox-label">
              <input 
                v-model="permissions.share" 
                type="checkbox"
                class="form-checkbox"
              />
              <span>Share</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button @click="closeModal" class="btn btn-secondary">
          Cancel
        </button>
        <button 
          @click="shareItem"
          class="btn btn-primary"
          :disabled="!canShare || isSharing"
        >
          <i v-if="isSharing" class="fas fa-spinner fa-spin"></i>
          <i v-else class="fas fa-share"></i>
          {{ isSharing ? 'Sharing...' : 'Share' }}
        </button>
      </div>

      <!-- Create Template Modal -->
      <div v-if="showCreateTemplate" class="template-modal-overlay" @click="closeTemplateModal">
        <div class="template-modal" @click.stop>
          <div class="template-modal-header">
            <h3>Create Share Template</h3>
            <button @click="closeTemplateModal" class="close-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="template-modal-body">
            <div class="form-group">
              <label for="template-name">Template Name:</label>
              <input
                id="template-name"
                v-model="newTemplate.name"
                type="text"
                placeholder="Enter template name"
                class="form-control"
                maxlength="100"
              />
            </div>
            
            <div class="form-group">
              <label for="template-description">Description:</label>
              <textarea
                id="template-description"
                v-model="newTemplate.description"
                placeholder="Describe this template"
                rows="3"
                maxlength="200"
                class="form-control"
              ></textarea>
            </div>
            
            <div class="form-group">
              <label class="checkbox-label">
                <input 
                  v-model="newTemplate.savePermissions" 
                  type="checkbox"
                  class="form-checkbox"
                />
                Save current permission settings
              </label>
            </div>
          </div>
          
          <div class="template-modal-footer">
            <button @click="closeTemplateModal" class="btn btn-secondary">
              Cancel
            </button>
            <button @click="saveTemplate" class="btn btn-primary">
              Save Template
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useNotificationStore } from '../stores/notifications.js'
import ShareService from '../services/ShareService'
import UserService from '../services/UserService'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  itemType: {
    type: String,
    required: true,
    validator: (value) => ['image', 'album'].includes(value)
  },
  itemId: {
    type: [String, Number],
    required: true
  },
  itemData: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'shared'])

const notificationStore = useNotificationStore()

// UI state
const activeTab = ref('users')
const showCreateTemplate = ref(false)
const isSharing = ref(false)

// User search
const userSearchQuery = ref('')
const userSearchResults = ref([])
const selectedUsers = ref([])

// Share options
const shareMessage = ref('')
const permissions = ref({
  view: true,
  download: false,
  comment: false,
  share: false
})

// Public link
const publicLink = ref('')
const publicLinkOptions = ref({
  allowDownload: false,
  allowComments: false,
  expires: false,
  expirationDate: ''
})

// Templates
const shareTemplates = ref([])
const newTemplate = ref({
  name: '',
  description: '',
  savePermissions: true
})

// Computed properties
const canShare = computed(() => {
  if (activeTab.value === 'users') {
    return selectedUsers.value.length > 0
  } else if (activeTab.value === 'public') {
    return true
  }
  return false
})

const publicLinkStatus = computed(() => {
  return publicLink.value ? 'active' : 'inactive'
})

const publicLinkStatusText = computed(() => {
  return publicLink.value ? 'Active' : 'Inactive'
})

// Methods
const closeModal = () => {
  emit('close')
  resetForm()
}

const resetForm = () => {
  activeTab.value = 'users'
  userSearchQuery.value = ''
  userSearchResults.value = []
  selectedUsers.value = []
  shareMessage.value = ''
  permissions.value = {
    view: true,
    download: false,
    comment: false,
    share: false
  }
  showCreateTemplate.value = false
}

// User search
const searchUsers = async () => {
  if (!userSearchQuery.value.trim()) {
    userSearchResults.value = []
    return
  }

  try {
    const response = await UserService.searchUsers(userSearchQuery.value)
    if (response.success && response.users) {
      userSearchResults.value = response.users
    }
  } catch (error) {
    console.error('Error searching users:', error)
    notificationStore.error('Failed to search users')
  }
}

const clearUserSearch = () => {
  userSearchQuery.value = ''
  userSearchResults.value = []
}

const toggleUserSelection = (user) => {
  const index = selectedUsers.value.findIndex(u => u.id === user.id)
  if (index > -1) {
    selectedUsers.value.splice(index, 1)
  } else {
    selectedUsers.value.push(user)
  }
}

const removeUser = (userId) => {
  const index = selectedUsers.value.findIndex(u => u.id === userId)
  if (index > -1) {
    selectedUsers.value.splice(index, 1)
  }
}

// Public link
const createPublicLink = async () => {
  try {
    const response = await ShareService.getPublicShareLink(
      props.itemType, 
      props.itemId, 
      publicLinkOptions.value
    )
    
    if (response.success) {
      publicLink.value = response.shareUrl
      notificationStore.showSuccess('Public link created successfully!')
    }
  } catch (error) {
    console.error('Error creating public link:', error)
    notificationStore.error('Failed to create public link')
  }
}

const updatePublicLink = async () => {
  try {
    await ShareService.updateItemSharePermissions(
      props.itemType, 
      props.itemId, 
      publicLinkOptions.value
    )
    
    notificationStore.showSuccess('Public link settings updated!')
  } catch (error) {
    console.error('Error updating public link:', error)
    notificationStore.error('Failed to update public link settings')
  }
}

const copyPublicLink = async () => {
  try {
    await navigator.clipboard.writeText(publicLink.value)
    notificationStore.showSuccess('Link copied to clipboard!')
  } catch (error) {
    console.error('Error copying link:', error)
    notificationStore.error('Failed to copy link')
  }
}

// Templates
const loadTemplates = async () => {
  try {
    const response = await ShareService.getShareTemplates()
    if (response.success) {
      shareTemplates.value = response.templates || []
    }
  } catch (error) {
    console.error('Error loading templates:', error)
  }
}

const useTemplate = (template) => {
  if (template.permissions) {
    permissions.value = { ...template.permissions }
  }
  if (template.message) {
    shareMessage.value = template.message
  }
  activeTab.value = 'users'
  notificationStore.showSuccess('Template applied!')
}

const closeTemplateModal = () => {
  showCreateTemplate.value = false
  newTemplate.value = {
    name: '',
    description: '',
    savePermissions: true
  }
}

const saveTemplate = async () => {
  if (!newTemplate.value.name.trim()) {
    notificationStore.error('Template name is required')
    return
  }

  try {
    const templateData = {
      name: newTemplate.value.name.trim(),
      description: newTemplate.value.description.trim(),
      permissions: newTemplate.value.savePermissions ? { ...permissions.value } : null,
      message: shareMessage.value || null
    }

    await ShareService.saveShareTemplate(templateData)
    
    notificationStore.showSuccess('Template saved successfully!')
    closeTemplateModal()
    loadTemplates()
  } catch (error) {
    console.error('Error saving template:', error)
    notificationStore.error('Failed to save template')
  }
}

const editTemplate = (template) => {
  // TODO: Implement template editing
  notificationStore.showInfo('Template editing coming soon!')
}

const deleteTemplate = async (templateId) => {
  if (!confirm('Are you sure you want to delete this template?')) return

  try {
    await ShareService.deleteShareTemplate(templateId)
    
    notificationStore.showSuccess('Template deleted successfully!')
    loadTemplates()
  } catch (error) {
    console.error('Error deleting template:', error)
    notificationStore.error('Failed to delete template')
  }
}

// Share item
const shareItem = async () => {
  if (!canShare.value) return

  isSharing.value = true

  try {
    if (activeTab.value === 'users') {
      const shareData = {
        type: props.itemType,
        itemId: props.itemId,
        userIds: selectedUsers.value.map(u => u.id),
        message: shareMessage.value.trim() || undefined,
        permissions: permissions.value
      }

      const response = await ShareService.createShare(shareData)
      
      if (response.success) {
        notificationStore.showSuccess(`Successfully shared with ${selectedUsers.value.length} user(s)!`)
        emit('shared', response.share)
        closeModal()
      }
    } else if (activeTab.value === 'public') {
      if (!publicLink.value) {
        await createPublicLink()
      } else {
        await updatePublicLink()
      }
    }
  } catch (error) {
    console.error('Error sharing item:', error)
    notificationStore.error('Failed to share item')
  } finally {
    isSharing.value = false
  }
}

// Load initial data
const loadInitialData = async () => {
  await Promise.all([
    loadTemplates(),
    loadPublicLink()
  ])
}

const loadPublicLink = async () => {
  try {
    const response = await ShareService.getItemSharePermissions(props.itemType, props.itemId)
    if (response.success && response.publicLink) {
      publicLink.value = response.publicLink.url
      publicLinkOptions.value = {
        allowDownload: response.publicLink.allowDownload || false,
        allowComments: response.publicLink.allowComments || false,
        expires: !!response.publicLink.expiresAt,
        expirationDate: response.publicLink.expiresAt ? response.publicLink.expiresAt.split('T')[0] : ''
      }
    }
  } catch (error) {
    console.error('Error loading public link:', error)
  }
}

// Lifecycle
onMounted(() => {
  loadInitialData()
})

// Watch for modal show/hide
watch(() => props.show, (newValue) => {
  if (newValue) {
    loadInitialData()
  }
})
</script>

<style scoped>
.share-modal-overlay {
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

.share-modal {
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

.modal-header h2 {
  margin: 0;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-light);
  cursor: pointer;
  padding: 0.5rem;
}

.close-btn:hover {
  color: var(--text-primary);
}

.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

/* Item Preview */
.item-preview {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
}

.preview-image {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
}

.preview-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--border-radius);
}

.no-preview {
  width: 100%;
  height: 100%;
  background: var(--bg-tertiary);
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: var(--text-light);
}

.preview-info {
  flex: 1;
}

.preview-info h3 {
  margin: 0 0 0.5rem 0;
  color: var(--text-primary);
}

.preview-info p {
  margin: 0 0 0.5rem 0;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.item-type {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  background: var(--primary-color);
  color: white;
  border-radius: var(--border-radius);
  font-size: 0.75rem;
  font-weight: 600;
}

/* Share Options */
.share-options {
  margin-bottom: 1.5rem;
}

.option-tabs {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 1.5rem;
}

.tab-btn {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.tab-btn:first-child {
  border-radius: var(--border-radius) 0 0 var(--border-radius);
}

.tab-btn:last-child {
  border-radius: 0 var(--border-radius) var(--border-radius) 0;
}

.tab-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.tab-content {
  min-height: 200px;
}

/* User Search */
.user-search {
  margin-bottom: 1.5rem;
}

.search-input-wrapper {
  position: relative;
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

/* Search Results */
.search-results h4 {
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.users-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
  border: 2px solid transparent;
}

.user-item:hover {
  background: var(--bg-tertiary);
}

.user-item.selected {
  border-color: var(--primary-color);
  background: var(--primary-light);
}

.user-avatar {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.default-avatar {
  width: 100%;
  height: 100%;
  background: var(--bg-tertiary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-light);
}

.user-info {
  flex: 1;
}

.username {
  display: block;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.email {
  display: block;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.selection-indicator {
  color: var(--primary-color);
  font-size: 1.25rem;
}

/* Selected Users */
.selected-users {
  margin-bottom: 1.5rem;
}

.selected-users h4 {
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.selected-users-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.selected-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background: var(--primary-light);
  border: 1px solid var(--primary-color);
  border-radius: var(--border-radius);
}

.selected-user .user-avatar {
  width: 24px;
  height: 24px;
}

.selected-user .username {
  font-size: 0.875rem;
  margin: 0;
}

.remove-user-btn {
  background: none;
  border: none;
  color: var(--danger-color);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 50%;
  transition: var(--transition);
}

.remove-user-btn:hover {
  background: var(--danger-light);
}

/* No Results & Start Search */
.no-results,
.start-search {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.search-prompt {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.search-prompt i {
  font-size: 2rem;
  color: var(--text-light);
}

/* Public Link */
.public-link-section {
  padding: 1rem;
}

.link-status {
  margin-bottom: 1.5rem;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.status-indicator i.active {
  color: var(--success-color);
}

.status-indicator i.inactive {
  color: var(--text-light);
}

.public-link-info {
  background: var(--bg-secondary);
  padding: 1rem;
  border-radius: var(--border-radius);
}

.link-display {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.link-input {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-family: monospace;
  font-size: 0.875rem;
}

.copy-btn {
  padding: 0.5rem;
  background: var(--secondary-color);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
}

.copy-btn:hover {
  background: var(--secondary-dark);
}

.link-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.expiration-date {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.expiration-date label {
  font-weight: 600;
  color: var(--text-primary);
}

.date-input {
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.create-public-link {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.create-public-link p {
  margin-bottom: 1.5rem;
}

/* Templates */
.templates-section {
  padding: 1rem;
}

.templates-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.templates-header h4 {
  margin: 0;
  color: var(--text-primary);
}

.templates-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.template-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
}

.template-item:hover {
  background: var(--bg-tertiary);
}

.template-info h5 {
  margin: 0 0 0.25rem 0;
  color: var(--text-primary);
}

.template-info p {
  margin: 0;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.template-actions {
  display: flex;
  gap: 0.5rem;
}

.no-templates {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.no-templates p {
  margin-bottom: 1.5rem;
}

/* Share Message */
.share-message {
  margin-bottom: 1.5rem;
}

.share-message label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--text-primary);
}

.message-input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 1rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
  resize: vertical;
}

.message-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.message-counter {
  text-align: right;
  font-size: 0.875rem;
  color: var(--text-light);
  margin-top: 0.25rem;
}

/* Permission Settings */
.permission-settings {
  margin-bottom: 1.5rem;
}

.permission-settings h4 {
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
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

/* Modal Footer */
.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

/* Template Modal */
.template-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.template-modal {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  max-width: 500px;
  width: 90vw;
  box-shadow: var(--shadow-xl);
}

.template-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.template-modal-header h3 {
  margin: 0;
  color: var(--text-primary);
}

.template-modal-body {
  padding: 1.5rem;
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
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
}

.template-modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

/* Buttons */
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

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-dark);
}

.btn-secondary {
  background: var(--secondary-color);
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: var(--secondary-dark);
}

.btn-danger {
  background: var(--danger-color);
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: var(--danger-dark);
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .share-modal {
    width: 95vw;
    max-height: 95vh;
  }
  
  .option-tabs {
    flex-direction: column;
  }
  
  .tab-btn:first-child,
  .tab-btn:last-child {
    border-radius: var(--border-radius);
  }
  
  .item-preview {
    flex-direction: column;
    text-align: center;
  }
  
  .preview-image {
    width: 120px;
    height: 120px;
    margin: 0 auto;
  }
  
  .permissions-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .template-modal {
    width: 95vw;
  }
}
</style>



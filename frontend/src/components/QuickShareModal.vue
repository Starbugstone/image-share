<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>
          <i class="fas fa-share"></i>
          Share {{ type === 'image' ? 'Image' : 'Album' }}
        </h2>
        <button @click="closeModal" class="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="share-info">
          <h3>{{ itemTitle }}</h3>
          <p>Select users to share this {{ type }} with:</p>
        </div>

        <div class="users-search">
          <div class="search-input">
            <i class="fas fa-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search users..."
              @input="filterUsers"
            >
          </div>
        </div>

        <div class="users-list">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading users...</p>
          </div>
          <div v-else-if="filteredUsers.length === 0" class="empty-state">
            <p>{{ searchQuery ? 'No users found matching your search.' : 'No users available for sharing.' }}</p>
          </div>
          <div v-else class="users-grid">
            <div 
              v-for="user in filteredUsers" 
              :key="user.id" 
              class="user-item"
              :class="{ selected: selectedUsers.includes(user.id) }"
              @click="toggleUserSelection(user.id)"
            >
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
              <div class="selection-indicator">
                <i v-if="selectedUsers.includes(user.id)" class="fas fa-check-circle"></i>
                <i v-else class="fas fa-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <div v-if="selectedUsers.length > 0" class="selected-summary">
          <p>{{ selectedUsers.length }} user{{ selectedUsers.length > 1 ? 's' : '' }} selected</p>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="closeModal" class="btn btn-secondary">
          Cancel
        </button>
        <button 
          @click="shareItem" 
          class="btn btn-primary"
          :disabled="selectedUsers.length === 0 || sharing"
        >
          <span v-if="sharing" class="spinner"></span>
          <span v-else>Share {{ type === 'image' ? 'Image' : 'Album' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNotificationStore } from '../stores/notifications.js'
import { api, endpoints } from '../services/api'

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['image', 'album'].includes(value)
  },
  itemId: {
    type: [String, Number],
    required: true
  },
  itemTitle: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['close', 'shared'])

const notificationStore = useNotificationStore()

// Reactive data
const users = ref([])
const selectedUsers = ref([])
const searchQuery = ref('')
const loading = ref(false)
const sharing = ref(false)

// Computed properties
const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  
  return users.value.filter(user => 
    user.username.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
const loadUsers = async () => {
  try {
    loading.value = true
    const response = await api.get(endpoints.availableUsers)
    users.value = response.data.users || []
  } catch (error) {
    console.error('Error loading users:', error)
    notificationStore.error('Failed to load users')
  } finally {
    loading.value = false
  }
}

const filterUsers = () => {
  // Filtering is handled by computed property
}

const toggleUserSelection = (userId) => {
  const index = selectedUsers.value.indexOf(userId)
  if (index > -1) {
    selectedUsers.value.splice(index, 1)
  } else {
    selectedUsers.value.push(userId)
  }
}

const shareItem = async () => {
  if (selectedUsers.value.length === 0) return
  
  try {
    sharing.value = true
    
    const shareData = {
      itemType: props.type,
      itemId: props.itemId,
      userIds: selectedUsers.value
    }
    
    const endpoint = props.type === 'image' ? endpoints.imageShare : endpoints.albumShare
    await api.post(endpoint, shareData)
    
    notificationStore.showSuccess(`${props.type === 'image' ? 'Image' : 'Album'} shared successfully!`)
    emit('shared')
  } catch (error) {
    console.error('Error sharing item:', error)
    notificationStore.error('Failed to share item')
  } finally {
    sharing.value = false
  }
}

const closeModal = () => {
  emit('close')
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
  loadUsers()
})
</script>

<style scoped>
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
  padding: 1rem;
}

.modal-content {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  max-width: 600px;
  width: 100%;
  max-height: 80vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  margin-bottom: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-light);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: var(--transition);
}

.modal-close:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.share-info {
  margin-bottom: 1.5rem;
  text-align: center;
}

.share-info h3 {
  color: var(--primary-color);
  margin-bottom: 0.5rem;
}

.users-search {
  margin-bottom: 1.5rem;
}

.search-input {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input i {
  position: absolute;
  left: 1rem;
  color: var(--text-light);
}

.search-input input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: var(--transition);
}

.search-input input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.users-list {
  min-height: 300px;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--text-secondary);
}

.users-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
  border: 2px solid transparent;
}

.user-item:hover {
  background: var(--bg-tertiary);
  border-color: var(--primary-color);
}

.user-item.selected {
  background: rgba(102, 126, 234, 0.1);
  border-color: var(--primary-color);
}

.user-avatar {
  position: relative;
  width: 50px;
  height: 50px;
  background: var(--bg-tertiary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: var(--text-light);
}

.verification-badge {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
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

.user-info {
  flex: 1;
}

.user-info h4 {
  margin-bottom: 0.25rem;
  font-size: 1rem;
}

.user-info p {
  margin-bottom: 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.selection-indicator {
  color: var(--primary-color);
  font-size: 1.25rem;
}

.selected-summary {
  margin-top: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  text-align: center;
  color: var(--text-secondary);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

@media (max-width: 768px) {
  .modal-content {
    max-height: 90vh;
  }
  
  .modal-header, .modal-body, .modal-footer {
    padding: 1rem;
  }
  
  .user-item {
    padding: 0.75rem;
  }
  
  .user-avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%;
  }
}
</style>

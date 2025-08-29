<template>
  <div class="notification-container">
    <TransitionGroup 
      name="notification" 
      tag="div" 
      class="notifications-list"
    >
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="notification"
        :class="[
          `notification-${notification.type}`,
          { 'notification-persistent': notification.persistent }
        ]"
      >
        <!-- Notification Icon -->
        <div class="notification-icon">
          <i :class="getNotificationIcon(notification.type)"></i>
        </div>

        <!-- Notification Content -->
        <div class="notification-content">
          <div v-if="notification.title" class="notification-title">
            {{ notification.title }}
          </div>
          <div class="notification-message">
            {{ notification.message }}
          </div>
        </div>

        <!-- Notification Actions -->
        <div v-if="notification.actions && notification.actions.length > 0" class="notification-actions">
          <button
            v-for="action in notification.actions"
            :key="action.label"
            @click="handleAction(notification.id, action)"
            class="notification-action"
            :class="`action-${action.variant || 'secondary'}`"
          >
            {{ action.label }}
          </button>
        </div>

        <!-- Input Field (for prompt notifications) -->
        <div v-if="notification.input" class="notification-input">
          <input
            :type="notification.input.type || 'text'"
            :placeholder="notification.input.placeholder"
            :required="notification.input.required"
            v-model="inputValues[notification.id]"
            @keyup.enter="handleInputSubmit(notification.id)"
            class="input-field"
          />
        </div>

        <!-- Close Button -->
        <button 
          v-if="!notification.persistent" 
          @click="removeNotification(notification.id)"
          class="notification-close"
          aria-label="Close notification"
        >
          <i class="fas fa-times"></i>
        </button>

        <!-- Progress Bar -->
        <div 
          v-if="!notification.persistent && notification.duration > 0" 
          class="notification-progress"
        >
          <div 
            class="progress-bar" 
            :style="{ animationDuration: `${notification.duration}ms` }"
          ></div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useNotificationStore } from '../stores/notifications.js'

const notificationStore = useNotificationStore()
const inputValues = ref({})

// Computed
const notifications = computed(() => notificationStore.notifications)

// Methods
const getNotificationIcon = (type) => {
  const icons = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle',
    loading: 'fas fa-spinner fa-spin',
    confirm: 'fas fa-question-circle',
    prompt: 'fas fa-edit'
  }
  return icons[type] || 'fas fa-bell'
}

const removeNotification = (id) => {
  notificationStore.removeNotification(id)
  delete inputValues.value[id]
}

const handleAction = (notificationId, action) => {
  if (action.action) {
    action.action(inputValues.value[notificationId])
  }
  removeNotification(notificationId)
}

const handleInputSubmit = (notificationId) => {
  const notification = notifications.value.find(n => n.id === notificationId)
  if (notification && notification.actions) {
    const submitAction = notification.actions.find(a => a.label === 'Submit')
    if (submitAction) {
      handleAction(notificationId, submitAction)
    }
  }
}

// Event listeners for global notifications
const handleShowNotification = (event) => {
  const { type, message, title, duration, persistent, actions, input } = event.detail
  
  if (type === 'confirm' || type === 'prompt') {
    // For confirm/prompt, we need to handle the response
    if (type === 'prompt' && input) {
      inputValues.value[Date.now()] = ''
    }
  }
}

const handleRedirectToLogin = () => {
  // Handle redirect to login
  window.location.href = '/login'
}

// Lifecycle
onMounted(() => {
  window.addEventListener('show-notification', handleShowNotification)
  window.addEventListener('redirect-to-login', handleRedirectToLogin)
})

onUnmounted(() => {
  window.removeEventListener('show-notification', handleShowNotification)
  window.removeEventListener('redirect-to-login', handleRedirectToLogin)
})
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 100px;
  right: 20px;
  z-index: 9999;
  pointer-events: none;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  pointer-events: auto;
}

.notification {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  min-width: 350px;
  max-width: 450px;
  padding: 1rem 1.5rem;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  border-left: 4px solid var(--primary-color);
  overflow: hidden;
  pointer-events: auto;
}

.notification-success {
  border-left-color: var(--success-color);
}

.notification-error {
  border-left-color: var(--error-color);
}

.notification-warning {
  border-left-color: var(--warning-color);
}

.notification-info {
  border-left-color: var(--info-color);
}

.notification-loading {
  border-left-color: var(--primary-color);
}

.notification-confirm,
.notification-prompt {
  border-left-color: var(--warning-color);
}

.notification-icon {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.notification-success .notification-icon {
  color: var(--success-color);
}

.notification-error .notification-icon {
  color: var(--error-color);
}

.notification-warning .notification-icon {
  color: var(--warning-color);
}

.notification-info .notification-icon {
  color: var(--info-color);
}

.notification-loading .notification-icon {
  color: var(--primary-color);
}

.notification-confirm .notification-icon,
.notification-prompt .notification-icon {
  color: var(--warning-color);
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  color: var(--text-color);
  margin-bottom: 0.25rem;
  font-size: 0.95rem;
}

.notification-message {
  color: var(--text-color);
  line-height: 1.4;
  font-size: 0.9rem;
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.notification-action {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-primary {
  background: var(--primary-color);
  color: white;
}

.action-primary:hover {
  background: var(--primary-color-dark);
}

.action-secondary {
  background: var(--background-light);
  color: var(--text-color);
}

.action-secondary:hover {
  background: var(--background-dark);
}

.action-danger {
  background: var(--error-color);
  color: white;
}

.action-danger:hover {
  background: var(--error-color-dark);
}

.notification-input {
  margin-top: 0.75rem;
}

.input-field {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s ease;
}

.input-field:focus {
  border-color: var(--primary-color);
}

.notification-close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 0.25rem;
  transition: all 0.2s ease;
  font-size: 0.9rem;
}

.notification-close:hover {
  color: var(--text-color);
  background: var(--background-light);
}

.notification-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: var(--border-light);
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: var(--primary-color);
  animation: progressShrink linear forwards;
}

@keyframes progressShrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Transition animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Responsive design */
@media (max-width: 768px) {
  .notification-container {
    top: 80px;
    right: 10px;
    left: 10px;
  }
  
  .notification {
    min-width: auto;
    max-width: none;
  }
  
  .notifications-list {
    gap: 0.75rem;
  }
}

@media (max-width: 480px) {
  .notification {
    padding: 0.75rem 1rem;
  }
  
  .notification-actions {
    flex-direction: column;
  }
  
  .notification-action {
    width: 100%;
    text-align: center;
  }
}
</style>

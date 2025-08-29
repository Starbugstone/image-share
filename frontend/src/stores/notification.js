import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  const show = ref(false)
  const message = ref('')
  const type = ref('info')
  const timeout = ref(null)

  const showNotification = (msg, notificationType = 'info', duration = 5000) => {
    // Clear existing timeout
    if (timeout.value) {
      clearTimeout(timeout.value)
    }

    message.value = msg
    type.value = notificationType
    show.value = true

    // Auto-hide after duration
    timeout.value = setTimeout(() => {
      hide()
    }, duration)
  }

  const showSuccess = (msg, duration) => {
    showNotification(msg, 'success', duration)
  }

  const showError = (msg, duration) => {
    showNotification(msg, 'error', duration)
  }

  const showInfo = (msg, duration) => {
    showNotification(msg, 'info', duration)
  }

  const hide = () => {
    show.value = false
    if (timeout.value) {
      clearTimeout(timeout.value)
      timeout.value = null
    }
  }

  return {
    show,
    message,
    type,
    showNotification,
    showSuccess,
    showError,
    showInfo,
    hide
  }
})

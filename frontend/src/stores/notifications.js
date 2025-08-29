import { defineStore } from 'pinia'
import { ref } from 'vue'

/**
 * Notifications Store
 * Manages toast notifications, error messages, and user feedback
 */
export const useNotificationStore = defineStore('notifications', () => {
  // State
  const notifications = ref([])
  const maxNotifications = 5

  // Actions
  /**
   * Add a new notification
   * @param {Object} notification - Notification object
   */
  function addNotification(notification) {
    const id = Date.now() + Math.random()
    const newNotification = {
      id,
      type: 'info',
      title: '',
      message: '',
      duration: 5000,
      persistent: false,
      actions: [],
      ...notification
    }

    // Add to beginning of array
    notifications.value.unshift(newNotification)

    // Limit number of notifications
    if (notifications.value.length > maxNotifications) {
      notifications.value = notifications.value.slice(0, maxNotifications)
    }

    // Auto-remove after duration (unless persistent)
    if (!newNotification.persistent && newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, newNotification.duration)
    }

    return id
  }

  /**
   * Remove a notification by ID
   * @param {number} id - Notification ID
   */
  function removeNotification(id) {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  /**
   * Clear all notifications
   */
  function clearAll() {
    notifications.value = []
  }

  /**
   * Clear notifications by type
   * @param {string} type - Notification type
   */
  function clearByType(type) {
    notifications.value = notifications.value.filter(n => n.type !== type)
  }

  /**
   * Show success notification
   * @param {string} message - Success message
   * @param {string} title - Optional title
   * @param {Object} options - Additional options
   */
  function success(message, title = '', options = {}) {
    return addNotification({
      type: 'success',
      title,
      message,
      icon: 'check-circle',
      ...options
    })
  }

  /**
   * Show error notification
   * @param {string} message - Error message
   * @param {string} title - Optional title
   * @param {Object} options - Additional options
   */
  function error(message, title = '', options = {}) {
    return addNotification({
      type: 'error',
      title,
      message,
      icon: 'alert-circle',
      duration: 8000, // Errors stay longer
      ...options
    })
  }

  /**
   * Show warning notification
   * @param {string} message - Warning message
   * @param {string} title - Optional title
   * @param {Object} options - Additional options
   */
  function warning(message, title = '', options = {}) {
    return addNotification({
      type: 'warning',
      title,
      message,
      icon: 'alert-triangle',
      ...options
    })
  }

  /**
   * Show info notification
   * @param {string} message - Info message
   * @param {string} title - Optional title
   * @param {Object} options - Additional options
   */
  function info(message, title = '', options = {}) {
    return addNotification({
      type: 'info',
      title,
      message,
      icon: 'info',
      ...options
    })
  }

  /**
   * Show loading notification
   * @param {string} message - Loading message
   * @param {string} title - Optional title
   * @param {Object} options - Additional options
   */
  function loading(message, title = '', options = {}) {
    return addNotification({
      type: 'loading',
      title,
      message,
      icon: 'loader',
      persistent: true, // Loading notifications don't auto-remove
      ...options
    })
  }

  /**
   * Update loading notification to success/error
   * @param {number} id - Notification ID
   * @param {string} type - New type (success, error, etc.)
   * @param {string} message - New message
   * @param {Object} options - Additional options
   */
  function updateNotification(id, type, message, options = {}) {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      Object.assign(notification, {
        type,
        message,
        persistent: false,
        duration: type === 'error' ? 8000 : 5000,
        ...options
      })

      // Auto-remove after duration
      if (notification.duration > 0) {
        setTimeout(() => {
          removeNotification(id)
        }, notification.duration)
      }
    }
  }

  /**
   * Show validation errors
   * @param {Object} errors - Validation errors object
   * @param {string} title - Optional title
   */
  function validationErrors(errors, title = 'Validation Errors') {
    if (typeof errors === 'string') {
      return error(errors, title)
    }

    if (Array.isArray(errors)) {
      return error(errors.join('\n'), title)
    }

    if (typeof errors === 'object') {
      const errorMessages = Object.values(errors).flat()
      return error(errorMessages.join('\n'), title)
    }

    return error('Please check your input and try again.', title)
  }

  /**
   * Show API error
   * @param {Error} error - API error object
   * @param {string} fallbackMessage - Fallback message if error details unavailable
   */
  function apiError(error, fallbackMessage = 'An error occurred. Please try again.') {
    let message = fallbackMessage
    let title = 'Error'

    if (error.response?.data?.message) {
      message = error.response.data.message
    } else if (error.response?.data?.error) {
      message = error.response.data.error
    } else if (error.message) {
      message = error.message
    }

    if (error.response?.status === 422) {
      title = 'Validation Error'
      if (error.response.data.errors) {
        return validationErrors(error.response.data.errors, title)
      }
    } else if (error.response?.status === 401) {
      title = 'Authentication Error'
    } else if (error.response?.status === 403) {
      title = 'Access Denied'
    } else if (error.response?.status === 404) {
      title = 'Not Found'
    } else if (error.response?.status === 429) {
      title = 'Too Many Requests'
    } else if (error.response?.status >= 500) {
      title = 'Server Error'
    }

    return this.error(message, title)
  }

  /**
   * Show network error
   * @param {Error} error - Network error object
   */
  function networkError(error) {
    if (error.code === 'NETWORK_ERROR' || error.message.includes('Network Error')) {
      return this.error(
        'Unable to connect to the server. Please check your internet connection and try again.',
        'Connection Error'
      )
    }
    
    return this.apiError(error, 'Network error occurred. Please try again.')
  }

  /**
   * Show confirmation dialog
   * @param {string} message - Confirmation message
   * @param {string} title - Optional title
   * @param {Object} options - Additional options
   */
  function confirm(message, title = 'Confirm Action', options = {}) {
    return new Promise((resolve) => {
      const id = addNotification({
        type: 'confirm',
        title,
        message,
        icon: 'help-circle',
        persistent: true,
        actions: [
          {
            label: 'Cancel',
            variant: 'secondary',
            action: () => {
              removeNotification(id)
              resolve(false)
            }
          },
          {
            label: 'Confirm',
            variant: 'primary',
            action: () => {
              removeNotification(id)
              resolve(true)
            }
          }
        ],
        ...options
      })
    })
  }

  /**
   * Show input prompt
   * @param {string} message - Prompt message
   * @param {string} title - Optional title
   * @param {Object} options - Additional options
   */
  function prompt(message, title = 'Input Required', options = {}) {
    return new Promise((resolve) => {
      const id = addNotification({
        type: 'prompt',
        title,
        message,
        icon: 'edit',
        persistent: true,
        input: {
          placeholder: options.placeholder || 'Enter your input...',
          type: options.inputType || 'text',
          required: options.required !== false
        },
        actions: [
          {
            label: 'Cancel',
            variant: 'secondary',
            action: () => {
              removeNotification(id)
              resolve(null)
            }
          },
          {
            label: 'Submit',
            variant: 'primary',
            action: (inputValue) => {
              removeNotification(id)
              resolve(inputValue)
            }
          }
        ],
        ...options
      })
    })
  }

  return {
    // State
    notifications,
    
    // Actions
    addNotification,
    removeNotification,
    clearAll,
    clearByType,
    success,
    error,
    warning,
    info,
    loading,
    updateNotification,
    validationErrors,
    apiError,
    networkError,
    confirm,
    prompt
  }
})

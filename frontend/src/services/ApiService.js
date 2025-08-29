import axios from 'axios'

/**
 * Base API Service for handling HTTP requests
 * Provides centralized configuration, interceptors, and error handling
 */
class ApiService {
  constructor() {
    // Create axios instance with base configuration
    this.client = axios.create({
      baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080',
      timeout: 30000,
      withCredentials: true, // Include cookies for CSRF and session
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    })

    this.setupInterceptors()
  }

  /**
   * Setup request and response interceptors
   */
  setupInterceptors() {
    // Request interceptor - add CSRF token and auth headers
    this.client.interceptors.request.use(
      (config) => {
        // Add CSRF token if available
        const csrfToken = this.getCsrfToken()
        if (csrfToken) {
          config.headers['X-CSRF-TOKEN'] = csrfToken
        }

        // Add auth token if available
        const authToken = this.getAuthToken()
        if (authToken) {
          config.headers['Authorization'] = `Bearer ${authToken}`
          console.log('ApiService: Adding auth header with token:', authToken.substring(0, 10) + '...')
        } else {
          console.log('ApiService: No auth token available')
        }

        return config
      },
      (error) => {
        return Promise.reject(error)
      }
    )

    // Response interceptor - handle common responses and errors
    this.client.interceptors.response.use(
      (response) => {
        return response
      },
      (error) => {
        return this.handleResponseError(error)
      }
    )
  }

  /**
   * Get CSRF token from meta tag or cookie
   */
  getCsrfToken() {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]')
    if (metaTag) {
      return metaTag.getAttribute('content')
    }

    // Fallback to cookie
    const cookies = document.cookie.split(';')
    const csrfCookie = cookies.find(cookie => cookie.trim().startsWith('csrf_token='))
    if (csrfCookie) {
      return csrfCookie.split('=')[1]
    }

    return null
  }

  /**
   * Get authentication token from localStorage
   */
  getAuthToken() {
    return localStorage.getItem('auth_token')
  }

  /**
   * Set authentication token
   */
  setAuthToken(token) {
    if (token) {
      localStorage.setItem('auth_token', token)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  /**
   * Handle response errors with user-friendly messages
   */
  handleResponseError(error) {
    const { response, request, message } = error

    if (response) {
      // Server responded with error status
      const { status, data } = response
      
      switch (status) {
        case 401:
          // Unauthorized - clear auth and redirect to login
          this.setAuthToken(null)
          this.redirectToLogin()
          break
        case 403:
          // Forbidden - show access denied message
          this.showError('Access denied. You don\'t have permission to perform this action.')
          break
        case 404:
          // Not found
          this.showError('The requested resource was not found.')
          break
        case 422:
          // Validation errors
          if (data.errors) {
            this.showValidationErrors(data.errors)
          } else {
            this.showError('Please check your input and try again.')
          }
          break
        case 429:
          // Rate limited
          this.showError('Too many requests. Please wait a moment and try again.')
          break
        case 500:
          // Server error
          this.showError('Something went wrong on our end. Please try again later.')
          break
        default:
          // Other errors
          const errorMessage = data.message || data.error || 'An unexpected error occurred.'
          this.showError(errorMessage)
      }
    } else if (request) {
      // Request was made but no response received
      this.showError('Unable to connect to the server. Please check your internet connection.')
    } else {
      // Something else happened
      this.showError('An unexpected error occurred. Please try again.')
    }

    return Promise.reject(error)
  }

  /**
   * Show error notification
   */
  showError(message) {
    // Dispatch custom event for notification system
    const event = new CustomEvent('show-notification', {
      detail: {
        type: 'error',
        message,
        duration: 5000
      }
    })
    window.dispatchEvent(event)
  }

  /**
   * Show validation errors
   */
  showValidationErrors(errors) {
    // Format validation errors for display
    const errorMessages = Object.values(errors).flat()
    const message = errorMessages.join('\n')
    this.showError(message)
  }

  /**
   * Redirect to login page
   */
  redirectToLogin() {
    // Dispatch custom event for navigation
    const event = new CustomEvent('redirect-to-login')
    window.dispatchEvent(event)
  }

  /**
   * Generic GET request
   */
  async get(url, config = {}) {
    try {
      const response = await this.client.get(url, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * Generic POST request
   */
  async post(url, data = {}, config = {}) {
    try {
      const response = await this.client.post(url, data, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * Generic PUT request
   */
  async put(url, data = {}, config = {}) {
    try {
      const response = await this.client.put(url, data, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * Generic PATCH request
   */
  async patch(url, data = {}, config = {}) {
    try {
      const response = await this.client.patch(url, data, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * Generic DELETE request
   */
  async delete(url, config = {}) {
    try {
      const response = await this.client.delete(url, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * File upload with progress tracking
   */
  async upload(url, file, onProgress, config = {}) {
    const formData = new FormData()
    formData.append('file', file)

    try {
      const response = await this.client.post(url, formData, {
        ...config,
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
          if (onProgress && progressEvent.total) {
            const percentCompleted = Math.round(
              (progressEvent.loaded * 100) / progressEvent.total
            )
            onProgress(percentCompleted)
          }
        }
      })
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return !!this.getAuthToken()
  }

  /**
   * Clear authentication data
   */
  clearAuth() {
    this.setAuthToken(null)
    localStorage.removeItem('user_data')
  }
}

// Create and export singleton instance
const apiService = new ApiService()
export default apiService

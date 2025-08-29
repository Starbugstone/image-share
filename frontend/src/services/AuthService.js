import apiService from './ApiService.js'

/**
 * Authentication Service
 * Handles user authentication, registration, and session management
 */
class AuthService {
  constructor() {
    this.currentUser = null
    this.isLoading = false
  }

  /**
   * User login
   * @param {string} email - User email
   * @param {string} password - User password
   * @param {boolean} rememberMe - Remember user session
   * @returns {Promise<Object>} User data and token
   */
  async login(email, password, rememberMe = false) {
    try {
      this.isLoading = true
      
      const response = await apiService.post('/api/login', {
        email,
        password,
        remember_me: rememberMe
      })

      if (response.token) {
        // Store authentication token
        console.log('AuthService: Storing token:', response.token)
        apiService.setAuthToken(response.token)
        
        // Store user data
        this.currentUser = response.user
        localStorage.setItem('user_data', JSON.stringify(response.user))
        
        // Set remember me if requested
        if (rememberMe) {
          localStorage.setItem('remember_me', 'true')
        }
        
        console.log('AuthService: Token stored, current token:', apiService.getAuthToken())
      } else {
        console.error('AuthService: No token in response:', response)
      }

      return response
    } catch (error) {
      throw error
    } finally {
      this.isLoading = false
    }
  }

  /**
   * User registration
   * @param {Object} userData - User registration data
   * @returns {Promise<Object>} Registration result
   */
  async register(userData) {
    try {
      this.isLoading = true
      
      const response = await apiService.post('/api/register', userData)
      
      // If auto-login is enabled, store the token
      if (response.token) {
        apiService.setAuthToken(response.token)
        this.currentUser = response.user
        localStorage.setItem('user_data', JSON.stringify(response.user))
      }

      return response
    } catch (error) {
      throw error
    } finally {
      this.isLoading = false
    }
  }

  /**
   * User logout
   * @returns {Promise<void>}
   */
  async logout() {
    try {
      // Call logout endpoint to invalidate server-side session
      await apiService.post('/api/logout')
    } catch (error) {
      // Continue with local cleanup even if server call fails
      console.warn('Logout server call failed:', error)
    } finally {
      // Clear local authentication data
      this.clearLocalAuth()
    }
  }

  /**
   * Clear local authentication data
   */
  clearLocalAuth() {
    this.currentUser = null
    apiService.clearAuth()
    localStorage.removeItem('remember_me')
  }

  /**
   * Check if user is authenticated
   * @returns {boolean}
   */
  isAuthenticated() {
    return apiService.isAuthenticated() && !!this.currentUser
  }

  /**
   * Get current user data
   * @returns {Object|null}
   */
  getCurrentUser() {
    return this.currentUser
  }

  /**
   * Set current user data
   * @param {Object} userData
   */
  setCurrentUser(userData) {
    this.currentUser = userData
    if (userData) {
      localStorage.setItem('user_data', JSON.stringify(userData))
    }
  }

  /**
   * Refresh user data from server
   * @returns {Promise<Object>}
   */
  async refreshUserData() {
    try {
      if (!this.isAuthenticated()) {
        throw new Error('User not authenticated')
      }

      const response = await apiService.get('/api/user/profile')
      this.currentUser = response.user
      localStorage.setItem('user_data', JSON.stringify(response.user))
      
      return response.user
    } catch (error) {
      // If refresh fails, clear auth and redirect to login
      this.clearLocalAuth()
      throw error
    }
  }

  /**
   * Initialize authentication state on app startup
   * @returns {Promise<boolean>} Whether user is authenticated
   */
  async initializeAuth() {
    try {
      console.log('AuthService: initializeAuth called')
      // Check if we have stored auth data
      const token = apiService.getAuthToken()
      const userData = localStorage.getItem('user_data')
      const rememberMe = localStorage.getItem('remember_me')
      
      console.log('AuthService: Found token:', !!token, 'userData:', !!userData, 'rememberMe:', rememberMe)

      if (token && userData) {
        // Try to restore user session
        this.currentUser = JSON.parse(userData)
        console.log('AuthService: Restored user from localStorage:', this.currentUser)
        
        // If remember me is not set, validate token with server
        if (!rememberMe) {
          try {
            console.log('AuthService: Validating token with server...')
            await this.refreshUserData()
            console.log('AuthService: Token validation successful')
            return true
          } catch (error) {
            console.log('AuthService: Token validation failed, clearing auth')
            // Token is invalid, clear auth
            this.clearLocalAuth()
            return false
          }
        }
        
        console.log('AuthService: Using remembered session')
        return true
      }

      console.log('AuthService: No stored auth data found')
      return false
    } catch (error) {
      console.error('AuthService: Auth initialization failed:', error)
      this.clearLocalAuth()
      return false
    }
  }

  /**
   * Request password reset
   * @param {string} email - User email
   * @returns {Promise<Object>}
   */
  async requestPasswordReset(email) {
    try {
      this.isLoading = true
      
      const response = await apiService.post('/api/password/reset', { email })
      return response
    } catch (error) {
      throw error
    } finally {
      this.isLoading = false
    }
  }

  /**
   * Reset password with token
   * @param {string} token - Reset token
   * @param {string} password - New password
   * @param {string} passwordConfirmation - Password confirmation
   * @returns {Promise<Object>}
   */
  async resetPassword(token, password, passwordConfirmation) {
    try {
      this.isLoading = true
      
      const response = await apiService.post('/api/password/reset/confirm', {
        token,
        password,
        password_confirmation: passwordConfirmation
      })
      
      return response
    } catch (error) {
      throw error
    } finally {
      this.isLoading = false
    }
  }

  /**
   * Verify email address
   * @param {string} token - Verification token
   * @returns {Promise<Object>}
   */
  async verifyEmail(token) {
    try {
      this.isLoading = true
      
      const response = await apiService.post('/api/email/verify', { token })
      
      // Update user data if verification successful
      if (response.user && this.currentUser) {
        this.currentUser = { ...this.currentUser, ...response.user }
        localStorage.setItem('user_data', JSON.stringify(this.currentUser))
      }
      
      return response
    } catch (error) {
      throw error
    } finally {
      this.isLoading = false
    }
  }

  /**
   * Resend email verification
   * @returns {Promise<Object>}
   */
  async resendEmailVerification() {
    try {
      this.isLoading = true
      
      const response = await apiService.post('/api/email/verification-notification')
      return response
    } catch (error) {
      throw error
    } finally {
      this.isLoading = false
    }
  }

  /**
   * Update user password
   * @param {string} currentPassword - Current password
   * @param {string} newPassword - New password
   * @param {string} newPasswordConfirmation - New password confirmation
   * @returns {Promise<Object>}
   */
  async updatePassword(currentPassword, newPassword, newPasswordConfirmation) {
    try {
      this.isLoading = true
      
      const response = await apiService.put('/api/user/password', {
        current_password: currentPassword,
        password: newPassword,
        password_confirmation: newPasswordConfirmation
      })
      
      return response
    } catch (error) {
      throw error
    } finally {
      this.isLoading = false
    }
  }

  /**
   * Get authentication loading state
   * @returns {boolean}
   */
  getLoadingState() {
    return this.isLoading
  }

  /**
   * Check if user needs email verification
   * @returns {boolean}
   */
  needsEmailVerification() {
    return this.currentUser && !this.currentUser.email_verified_at
  }

  /**
   * Check if user has specific permission
   * @param {string} permission - Permission name
   * @returns {boolean}
   */
  hasPermission(permission) {
    if (!this.currentUser || !this.currentUser.permissions) {
      return false
    }
    
    return this.currentUser.permissions.includes(permission)
  }

  /**
   * Check if user has specific role
   * @param {string} role - Role name
   * @returns {boolean}
   */
  hasRole(role) {
    if (!this.currentUser || !this.currentUser.roles) {
      return false
    }
    
    return this.currentUser.roles.includes(role)
  }
}

// Create and export singleton instance
const authService = new AuthService()
export default authService

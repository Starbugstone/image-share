import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '../services/AuthService.js'

/**
 * Authentication Store
 * Manages user authentication state and actions
 */
export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const isInitialized = ref(false)

  // Getters
  const currentUser = computed(() => user.value)
  const userStatus = computed(() => {
    if (!user.value) return 'offline'
    return user.value.last_seen_at ? 'online' : 'offline'
  })
  
  const userStatusClass = computed(() => {
    return userStatus.value === 'online' ? 'status-online' : 'status-offline'
  })
  
  const userStatusText = computed(() => {
    return userStatus.value === 'online' ? 'Online' : 'Offline'
  })

  const needsEmailVerification = computed(() => {
    return user.value && !user.value.email_verified_at
  })

  const hasProfileImage = computed(() => {
    return user.value?.profile?.image_url
  })

  const profileImageUrl = computed(() => {
    const url = user.value?.profile?.image_url || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwODYgMTIgMTIgMTJaIiBmaWxsPSIjNjY2NjY2Ii8+CjxwYXRoIGQ9Ik0xMiAxNEM5LjMzIDE0IDUgMTUuMDkgNSAxN1YyMEgxOVYxN0MxOSAxNS4wOSAxNC42NyAxNCAxMiAxNFoiIGZpbGw9IiM2NjY2NjYiLz4KPC9zdmc+'
    return url
  })

  const displayName = computed(() => {
    if (!user.value) return ''
    return user.value.profile?.display_name || user.value.username || user.value.email
  })

  const userStats = computed(() => {
    if (!user.value) return {}
    
    return {
      total_images: user.value.stats?.total_images || 0,
      total_albums: user.value.stats?.total_albums || 0,
      shared_items: user.value.stats?.shared_items || 0,
      received_shares: user.value.stats?.received_shares || 0
    }
  })

  // Actions
  /**
   * Initialize authentication state
   */
  async function initializeAuth() {
    console.log('AuthStore: initializeAuth called, isInitialized:', isInitialized.value)
    if (isInitialized.value) return
    
    try {
      console.log('AuthStore: Starting auth initialization...')
      isLoading.value = true
      const authenticated = await authService.initializeAuth()
      console.log('AuthStore: Auth service returned:', authenticated)
      
      if (authenticated) {
        user.value = authService.getCurrentUser()
        isAuthenticated.value = true
        console.log('AuthStore: User authenticated:', user.value)
      } else {
        console.log('AuthStore: No user authenticated')
      }
    } catch (error) {
      console.error('AuthStore: Auth initialization failed:', error)
    } finally {
      isLoading.value = false
      isInitialized.value = true
      console.log('AuthStore: Initialization completed, isInitialized:', isInitialized.value)
    }
  }

  /**
   * User login
   */
  async function login(credentials) {
    try {
      isLoading.value = true
      const response = await authService.login(
        credentials.email,
        credentials.password,
        credentials.rememberMe
      )
      
      if (response && response.user) {
        user.value = response.user
        isAuthenticated.value = true
        return response
      } else {
        throw new Error('Login failed: No user data received')
      }
    } catch (error) {
      // Re-throw the error so the component can handle it
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * User registration
   */
  async function register(userData) {
    try {
      isLoading.value = true
      const response = await authService.register(userData)
      
      if (response && response.success) {
        if (response.user) {
          user.value = response.user
          isAuthenticated.value = true
        }
        return response
      } else {
        throw new Error(response?.error || 'Registration failed')
      }
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * User logout
   */
  async function logout() {
    try {
      isLoading.value = true
      await authService.logout()
      
      // Clear local state
      user.value = null
      isAuthenticated.value = false
      
      // Redirect to home page
      window.location.href = '/'
    } catch (error) {
      console.error('Logout error:', error)
      // Force clear state even if server call fails
      user.value = null
      isAuthenticated.value = false
      window.location.href = '/'
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Refresh user data
   */
  async function refreshUserData() {
    try {
      if (!isAuthenticated.value) return
      
      const userData = await authService.refreshUserData()
      user.value = userData
      
      return userData
    } catch (error) {
      console.error('Failed to refresh user data:', error)
      await logout()
      throw error
    }
  }

  /**
   * Update user profile
   */
  async function updateProfile(profileData) {
    try {
      isLoading.value = true
      
      // Update local user data optimistically
      const updatedUser = { ...user.value, profile: { ...user.value.profile, ...profileData } }
      user.value = updatedUser
      
      return updatedUser
    } catch (error) {
      // Revert optimistic update
      await refreshUserData()
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update user avatar
   */
  async function updateAvatar(imageFile) {
    try {
      isLoading.value = true
      // TODO: Implement avatar upload
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Request password reset
   */
  async function requestPasswordReset(email) {
    try {
      isLoading.value = true
      await authService.requestPasswordReset(email)
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Reset password
   */
  async function resetPassword(token, password, passwordConfirmation) {
    try {
      isLoading.value = true
      await authService.resetPassword(token, password, passwordConfirmation)
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Verify email
   */
  async function verifyEmail(token) {
    try {
      isLoading.value = true
      const response = await authService.verifyEmail(token)
      
      if (response.user) {
        user.value = response.user
      }
      
      return response
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Resend email verification
   */
  async function resendEmailVerification() {
    try {
      isLoading.value = true
      await authService.resendEmailVerification()
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update password
   */
  async function updatePassword(currentPassword, newPassword, newPasswordConfirmation) {
    try {
      isLoading.value = true
      await authService.updatePassword(currentPassword, newPassword, newPasswordConfirmation)
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Check if user has permission
   */
  function hasPermission(permission) {
    return authService.hasPermission(permission)
  }

  /**
   * Check if user has role
   */
  function hasRole(role) {
    return authService.hasRole(role)
  }

  return {
    // State
    user,
    isAuthenticated,
    isLoading,
    isInitialized,
    
    // Getters
    currentUser,
    userStatus,
    userStatusClass,
    userStatusText,
    needsEmailVerification,
    hasProfileImage,
    profileImageUrl,
    displayName,
    userStats,
    
    // Actions
    initializeAuth,
    login,
    register,
    logout,
    refreshUserData,
    updateProfile,
    updateAvatar,
    requestPasswordReset,
    resetPassword,
    verifyEmail,
    resendEmailVerification,
    updatePassword,
    hasPermission,
    hasRole
  }
})

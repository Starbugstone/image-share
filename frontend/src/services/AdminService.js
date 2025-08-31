import apiService from './ApiService.js'

/**
 * Admin Service
 * Provides admin-specific API calls
 */
export default {
  /**
   * Get list of users with statistics
   * @returns {Promise<Array>}
   */
  async getUsers() {
    return await apiService.get('/api/admin/users')
  },

  /**
   * Get detailed user data including images and albums
   * @param {number|string} id
   * @returns {Promise<Object>}
   */
  async getUser(id) {
    return await apiService.get(`/api/admin/users/${id}`)
  }
}

import apiService from './ApiService'

class DashboardService {
  constructor() {
    this.api = apiService
  }

  /**
   * Get dashboard statistics
   * @returns {Promise<Object>} Dashboard stats
   */
  async getDashboardStats() {
    try {
      const response = await this.api.get('/api/dashboard/stats')
      return response
    } catch (error) {
      throw new Error(`Failed to fetch dashboard stats: ${error.message}`)
    }
  }

  /**
   * Get available users for sharing
   * @returns {Promise<Object>} Available users
   */
  async getAvailableUsers() {
    try {
      const response = await this.api.get('/api/users/available')
      return response
    } catch (error) {
      throw new Error(`Failed to fetch available users: ${error.message}`)
    }
  }

  /**
   * Get user's images
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @returns {Promise<Object>} Images response with pagination
   */
  async getUserImages(params = {}) {
    const queryParams = new URLSearchParams()
    
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
        queryParams.append(key, params[key])
      }
    })

    try {
      const response = await this.api.get(`/api/images?${queryParams.toString()}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch images: ${error.message}`)
    }
  }

  /**
   * Get user's albums
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @returns {Promise<Object>} Albums response with pagination
   */
  async getUserAlbums(params = {}) {
    const queryParams = new URLSearchParams()
    
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
        queryParams.append(key, params[key])
      }
    })

    try {
      const response = await this.api.get(`/api/albums?${queryParams.toString()}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch albums: ${error.message}`)
    }
  }

  /**
   * Get user's shares
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @returns {Promise<Object>} Shares response with pagination
   */
  async getUserShares(params = {}) {
    const queryParams = new URLSearchParams()
    
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
        queryParams.append(key, params[key])
      }
    })

    try {
      const response = await this.api.get(`/api/shares?${queryParams.toString()}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch shares: ${error.message}`)
    }
  }
}

export default new DashboardService()

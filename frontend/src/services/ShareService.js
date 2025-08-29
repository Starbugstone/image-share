import apiService from './ApiService'

class ShareService {
  constructor() {
    this.api = apiService
  }

  /**
   * Create a new share
   * @param {Object} shareData - Share data
   * @param {string} shareData.type - Type of share (image, album)
   * @param {number} shareData.itemId - ID of the item being shared
   * @param {Array<number>} shareData.userIds - Array of user IDs to share with
   * @param {string} shareData.message - Optional message
   * @param {Object} shareData.permissions - Sharing permissions
   * @returns {Promise<Object>} Created share data
   */
  async createShare(shareData) {
    try {
      const response = await this.api.post('/api/shares', shareData)
      return response
    } catch (error) {
      throw new Error(`Failed to create share: ${error.message}`)
    }
  }

  /**
   * Get user's shares (items shared by me)
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @param {string} params.type - Filter by share type
   * @param {string} params.status - Filter by share status
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
      const response = await this.api.get(`/api/shares/outgoing?${queryParams.toString()}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch user shares: ${error.message}`)
    }
  }

  /**
   * Get items shared with user
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @param {string} params.type - Filter by share type
   * @param {string} params.status - Filter by share status
   * @returns {Promise<Object>} Shared items response with pagination
   */
  async getSharedWithMe(params = {}) {
    const queryParams = new URLSearchParams()
    
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
        queryParams.append(key, params[key])
      }
    })

    try {
      const response = await this.api.get(`/api/shares/incoming?${queryParams.toString()}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch shared items: ${error.message}`)
    }
  }

  /**
   * Get a single share by ID
   * @param {number} shareId - Share ID
   * @returns {Promise<Object>} Share data
   */
  async getShare(shareId) {
    try {
      const response = await this.api.get(`/api/shares/${shareId}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch share: ${error.message}`)
    }
  }

  /**
   * Update share details
   * @param {number} shareId - Share ID
   * @param {Object} updates - Fields to update
   * @returns {Promise<Object>} Updated share data
   */
  async updateShare(shareId, updates) {
    try {
      const response = await this.api.put(`/api/shares/${shareId}`, updates)
      return response
    } catch (error) {
      throw new Error(`Failed to update share: ${error.message}`)
    }
  }

  /**
   * Delete a share
   * @param {number} shareId - Share ID
   * @returns {Promise<boolean>} Success status
   */
  async deleteShare(shareId) {
    try {
      await this.api.delete(`/api/shares/${shareId}`)
      return true
    } catch (error) {
      throw new Error(`Failed to delete share: ${error.message}`)
    }
  }

  /**
   * Accept a share
   * @param {number} shareId - Share ID
   * @returns {Promise<Object>} Updated share data
   */
  async acceptShare(shareId) {
    try {
      const response = await this.api.post(`/api/shares/${shareId}/accept`)
      return response.data
    } catch (error) {
      throw new Error(`Failed to accept share: ${error.message}`)
    }
  }

  /**
   * Decline a share
   * @param {number} shareId - Share ID
   * @param {string} reason - Optional reason for declining
   * @returns {Promise<Object>} Updated share data
   */
  async declineShare(shareId, reason = '') {
    try {
      const response = await this.api.post(`/api/shares/${shareId}/decline`, { reason })
      return response
    } catch (error) {
      throw new Error(`Failed to decline share: ${error.message}`)
    }
  }

  /**
   * Revoke a share
   * @param {number} shareId - Share ID
   * @returns {Promise<boolean>} Success status
   */
  async revokeShare(shareId) {
    try {
      await this.api.post(`/api/shares/${shareId}/revoke`)
      return true
    } catch (error) {
      throw new Error(`Failed to revoke share: ${error.message}`)
    }
  }

  /**
   * Get share statistics
   * @param {number} shareId - Share ID
   * @returns {Promise<Object>} Share statistics
   */
  async getShareStats(shareId) {
    try {
      const response = await this.api.get(`/api/shares/${shareId}/stats`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch share stats: ${error.message}`)
    }
  }

  /**
   * Get user's sharing statistics
   * @returns {Promise<Object>} User sharing statistics
   */
  async getUserShareStats() {
    try {
      const response = await this.api.get('/api/shares/stats')
      return response
    } catch (error) {
      throw new Error(`Failed to fetch user share stats: ${error.message}`)
    }
  }

  /**
   * Search shares
   * @param {string} query - Search query
   * @param {Object} filters - Additional filters
   * @returns {Promise<Array>} Search results
   */
  async searchShares(query, filters = {}) {
    try {
      const params = { q: query, ...filters }
      const response = await this.api.get('/api/shares/search', { params })
      return response
    } catch (error) {
      throw new Error(`Search failed: ${error.message}`)
    }
  }

  /**
   * Get share notifications
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @param {boolean} params.unreadOnly - Only unread notifications
   * @returns {Promise<Object>} Notifications response
   */
  async getShareNotifications(params = {}) {
    const queryParams = new URLSearchParams()
    
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
        queryParams.append(key, params[key])
      }
    })

    try {
      const response = await this.api.get(`/api/shares/notifications?${queryParams.toString()}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch share notifications: ${error.message}`)
    }
  }

  /**
   * Mark share notification as read
   * @param {number} notificationId - Notification ID
   * @returns {Promise<Object>} Updated notification
   */
  async markNotificationRead(notificationId) {
    try {
      const response = await this.api.put(`/api/shares/notifications/${notificationId}/read`)
      return response
    } catch (error) {
      throw new Error(`Failed to mark notification as read: ${error.message}`)
    }
  }

  /**
   * Mark all share notifications as read
   * @returns {Promise<boolean>} Success status
   */
  async markAllNotificationsRead() {
    try {
      await this.api.put('/api/shares/notifications/read-all')
      return true
    } catch (error) {
      throw new Error(`Failed to mark all notifications as read: ${error.message}`)
    }
  }

  /**
   * Get share permissions for an item
   * @param {string} type - Item type (image, album)
   * @param {number} itemId - Item ID
   * @returns {Promise<Object>} Share permissions
   */
  async getItemSharePermissions(type, itemId) {
    try {
      const response = await this.api.get(`/api/shares/permissions/${type}/${itemId}`)
      return response.data
    } catch (error) {
      throw new Error(`Failed to fetch share permissions: ${error.message}`)
    }
  }

  /**
   * Update share permissions for an item
   * @param {string} type - Item type (image, album)
   * @param {number} itemId - Item ID
   * @param {Object} permissions - New permissions
   * @returns {Promise<Object>} Updated permissions
   */
  async updateItemSharePermissions(type, itemId, permissions) {
    try {
      const response = await this.api.put(`/api/shares/permissions/${type}/${itemId}`, permissions)
      return response.data
    } catch (error) {
      throw new Error(`Failed to update share permissions: ${error.message}`)
    }
  }

  /**
   * Get public share link
   * @param {string} type - Item type (image, album)
   * @param {number} itemId - Item ID
   * @param {Object} options - Share options
   * @returns {Promise<Object>} Public share data
   */
  async getPublicShareLink(type, itemId, options = {}) {
    try {
      const response = await this.api.post(`/api/shares/public/${type}/${itemId}`, options)
      return response.data
    } catch (error) {
      throw new Error(`Failed to create public share link: ${error.message}`)
    }
  }

  /**
   * Revoke public share link
   * @param {string} type - Item type (image, album)
   * @param {number} itemId - Item ID
   * @returns {Promise<boolean>} Success status
   */
  async revokePublicShareLink(type, itemId) {
    try {
      await this.api.delete(`/api/shares/public/${type}/${itemId}`)
      return true
    } catch (error) {
      throw new Error(`Failed to revoke public share link: ${error.message}`)
    }
  }

  /**
   * Get share activity log
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @param {string} params.type - Filter by activity type
   * @returns {Promise<Object>} Activity log response
   */
  async getShareActivityLog(params = {}) {
    const queryParams = new URLSearchParams()
    
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
        queryParams.append(key, params[key])
      }
    })

    try {
      const response = await this.api.get(`/api/shares/activity?${queryParams.toString()}`)
      return response.data
    } catch (error) {
      throw new Error(`Failed to fetch share activity log: ${error.message}`)
    }
  }

  /**
   * Bulk share items
   * @param {Array<Object>} items - Array of items to share
   * @param {Array<number>} userIds - Array of user IDs to share with
   * @param {Object} options - Sharing options
   * @returns {Promise<Array>} Array of created shares
   */
  async bulkShare(items, userIds, options = {}) {
    try {
      const shareData = {
        items,
        userIds,
        ...options
      }
      
      const response = await this.api.post('/api/shares/bulk', shareData)
      return response.data
    } catch (error) {
      throw new Error(`Failed to bulk share items: ${error.message}`)
    }
  }

  /**
   * Get share templates
   * @returns {Promise<Array>} Share templates
   */
  async getShareTemplates() {
    try {
      const response = await this.api.get('/api/shares/templates')
      return response.data
    } catch (error) {
      throw new Error(`Failed to fetch share templates: ${error.message}`)
    }
  }

  /**
   * Save share template
   * @param {Object} template - Template data
   * @returns {Promise<Object>} Saved template
   */
  async saveShareTemplate(template) {
    try {
      const response = await this.api.post('/api/shares/templates', template)
      return response.data
    } catch (error) {
      throw new Error(`Failed to save share template: ${error.message}`)
    }
  }

  /**
   * Delete share template
   * @param {number} templateId - Template ID
   * @returns {Promise<boolean>} Success status
   */
  async deleteShareTemplate(templateId) {
    try {
      await this.api.delete(`/api/shares/templates/${templateId}`)
      return true
    } catch (error) {
      throw new Error(`Failed to delete share template: ${error.message}`)
    }
  }
}

export default new ShareService()



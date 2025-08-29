import apiService from './ApiService'

class AlbumService {
  constructor() {
    this.api = apiService
  }

  /**
   * Create a new album
   * @param {Object} albumData - Album data
   * @param {string} albumData.name - Album name
   * @param {string} albumData.description - Album description
   * @param {string} albumData.privacy - Privacy setting (private, friends, public)
   * @param {number} albumData.coverImageId - Optional cover image ID
   * @returns {Promise<Object>} Created album data
   */
  async createAlbum(albumData) {
    try {
      const response = await this.api.post('/api/albums', albumData)
      return response
    } catch (error) {
      throw new Error(`Failed to create album: ${error.message}`)
    }
  }

  /**
   * Get user's albums with pagination and filters
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @param {string} params.sort - Sort field
   * @param {string} params.order - Sort order (asc/desc)
   * @param {string} params.search - Search query
   * @param {string} params.privacy - Filter by privacy
   * @param {string} params.dateFrom - Filter by date from
   * @param {string} params.dateTo - Filter by date to
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
   * Get a single album by ID
   * @param {number} albumId - Album ID
   * @returns {Promise<Object>} Album data with images
   */
  async getAlbum(albumId) {
    try {
      const response = await this.api.get(`/api/albums/${albumId}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch album: ${error.message}`)
    }
  }

  /**
   * Get images in an album
   * @param {number} albumId - Album ID
   * @param {Object} params - Query parameters
   * @returns {Promise<Object>} Images response
   */
  async getAlbumImages(albumId, params = {}) {
    try {
      const queryParams = new URLSearchParams()
      
      Object.keys(params).forEach(key => {
        if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
          queryParams.append(key, params[key])
        }
      })

      const response = await this.api.get(`/api/albums/${albumId}/images?${queryParams.toString()}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch album images: ${error.message}`)
    }
  }

  /**
   * Update album details
   * @param {number} albumId - Album ID
   * @param {Object} updates - Fields to update
   * @returns {Promise<Object>} Updated album data
   */
  async updateAlbum(albumId, updates) {
    try {
      const response = await this.api.put(`/api/albums/${albumId}`, updates)
      return response
    } catch (error) {
      throw new Error(`Failed to update album: ${error.message}`)
    }
  }

  /**
   * Delete an album
   * @param {number} albumId - Album ID
   * @returns {Promise<boolean>} Success status
   */
  async deleteAlbum(albumId) {
    try {
      await this.api.delete(`/api/albums/${albumId}`)
      return true
    } catch (error) {
      throw new Error(`Failed to delete album: ${error.message}`)
    }
  }

  /**
   * Add images to album
   * @param {number} albumId - Album ID
   * @param {Array<number>} imageIds - Array of image IDs to add
   * @returns {Promise<Object>} Updated album data
   */
  async addImagesToAlbum(albumId, imageIds) {
    try {
      const response = await this.api.post(`/api/albums/${albumId}/images`, {
        imageIds
      })
      return response
    } catch (error) {
      throw new Error(`Failed to add images to album: ${error.message}`)
    }
  }

  /**
   * Remove images from album
   * @param {number} albumId - Album ID
   * @param {Array<number>} imageIds - Array of image IDs to remove
   * @returns {Promise<Object>} Updated album data
   */
  async removeImagesFromAlbum(albumId, imageIds) {
    try {
      const response = await this.api.delete(`/api/albums/${albumId}/images`, {
        data: { imageIds }
      })
      return response
    } catch (error) {
      throw new Error(`Failed to remove images from album: ${error.message}`)
    }
  }

  /**
   * Set album cover image
   * @param {number} albumId - Album ID
   * @param {number} imageId - Image ID to set as cover
   * @returns {Promise<Object>} Updated album data
   */
  async setAlbumCover(albumId, imageId) {
    try {
      const response = await this.api.put(`/api/albums/${albumId}/cover`, {
        imageId
      })
      return response
    } catch (error) {
      throw new Error(`Failed to set album cover: ${error.message}`)
    }
  }

  /**
   * Get album statistics
   * @param {number} albumId - Album ID
   * @returns {Promise<Object>} Album statistics
   */
  async getAlbumStats(albumId) {
    try {
      const response = await this.api.get(`/api/albums/${albumId}/stats`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch album stats: ${error.message}`)
    }
  }

  /**
   * Get user's album statistics
   * @returns {Promise<Object>} User album statistics
   */
  async getUserAlbumStats() {
    try {
      const response = await this.api.get('/api/albums/stats')
      return response
    } catch (error) {
      throw new Error(`Failed to fetch user album stats: ${error.message}`)
    }
  }

  /**
   * Search albums across all user's albums
   * @param {string} query - Search query
   * @param {Object} filters - Additional filters
   * @returns {Promise<Array>} Search results
   */
  async searchAlbums(query, filters = {}) {
    try {
      const params = { q: query, ...filters }
      const response = await this.api.get('/api/albums/search', { params })
      return response
    } catch (error) {
      throw new Error(`Search failed: ${error.message}`)
    }
  }

  /**
   * Get album sharing information
   * @param {number} albumId - Album ID
   * @returns {Promise<Object>} Sharing information
   */
  async getAlbumSharing(albumId) {
    try {
      const response = await this.api.get(`/api/albums/${albumId}/sharing`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch sharing info: ${error.message}`)
    }
  }

  /**
   * Update album sharing settings
   * @param {number} albumId - Album ID
   * @param {Object} sharingSettings - New sharing settings
   * @returns {Promise<Object>} Updated sharing information
   */
  async updateAlbumSharing(albumId, sharingSettings) {
    try {
      const response = await this.api.put(`/api/albums/${albumId}/sharing`, sharingSettings)
      return response
    } catch (error) {
      throw new Error(`Failed to update sharing settings: ${error.message}`)
    }
  }

  /**
   * Get recent albums
   * @param {number} limit - Number of recent albums to fetch
   * @returns {Promise<Array>} Recent albums
   */
  async getRecentAlbums(limit = 10) {
    try {
      const response = await this.api.get('/api/albums/recent', {
        params: { limit }
      })
      return response
    } catch (error) {
      throw new Error(`Failed to fetch recent albums: ${error.message}`)
    }
  }

  /**
   * Get popular albums
   * @param {number} limit - Number of popular albums to fetch
   * @param {string} period - Time period (day, week, month, year)
   * @returns {Promise<Array>} Popular albums
   */
  async getPopularAlbums(limit = 10, period = 'month') {
    try {
      const response = await this.api.get('/api/albums/popular', {
        params: { limit, period }
      })
      return response
    } catch (error) {
      throw new Error(`Failed to fetch popular albums: ${error.message}`)
    }
  }

  /**
   * Reorder images in album
   * @param {number} albumId - Album ID
   * @param {Array<number>} imageOrder - Array of image IDs in desired order
   * @returns {Promise<Object>} Updated album data
   */
  async reorderAlbumImages(albumId, imageOrder) {
    try {
      const response = await this.api.put(`/api/albums/${albumId}/order`, {
        imageOrder
      })
      return response
    } catch (error) {
      throw new Error(`Failed to reorder album images: ${error.message}`)
    }
  }

  /**
   * Duplicate album
   * @param {number} albumId - Album ID to duplicate
   * @param {Object} options - Duplication options
   * @param {string} options.name - New album name
   * @param {boolean} options.includeImages - Whether to include images
   * @param {boolean} options.includeSharing - Whether to include sharing settings
   * @returns {Promise<Object>} New album data
   */
  async duplicateAlbum(albumId, options = {}) {
    try {
      const response = await this.api.post(`/api/albums/${albumId}/duplicate`, options)
      return response
    } catch (error) {
      throw new Error(`Failed to duplicate album: ${error.message}`)
    }
  }

  /**
   * Export album
   * @param {number} albumId - Album ID
   * @param {Object} options - Export options
   * @param {string} options.format - Export format (zip, pdf)
   * @param {string} options.quality - Image quality (low, medium, high)
   * @returns {Promise<string>} Download URL
   */
  async exportAlbum(albumId, options = {}) {
    try {
      const response = await this.api.post(`/api/albums/${albumId}/export`, options)
      return response.downloadUrl
    } catch (error) {
      throw new Error(`Failed to export album: ${error.message}`)
    }
  }
}

export default new AlbumService()

import apiService from './ApiService'

class ImageService {
  constructor() {
    this.api = apiService
  }

  /**
   * Upload a single image
   * @param {File} file - Image file to upload
   * @param {Object} options - Upload options
   * @param {number} options.albumId - Optional album ID
   * @param {string} options.privacy - Privacy setting (private, friends, public)
   * @param {string} options.description - Image description
   * @param {Array} options.tags - Image tags
   * @returns {Promise<Object>} Upload response
   */
  async uploadImage(file, options = {}) {
    const formData = new FormData()
    formData.append('image', file)
    
    if (options.albumId) {
      formData.append('album', options.albumId)
    }
    
    if (options.privacy) {
      formData.append('privacy', options.privacy)
    }
    
    if (options.description) {
      formData.append('description', options.description)
    }
    
    if (options.tags && options.tags.length > 0) {
      formData.append('tags', JSON.stringify(options.tags))
    }

    try {
      const response = await this.api.post('/api/images/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          if (options.onProgress) {
            const percentCompleted = Math.round(
              (progressEvent.loaded * 100) / progressEvent.total
            )
            options.onProgress(percentCompleted)
          }
        }
      })
      
      return response.data
    } catch (error) {
      throw new Error(`Upload failed: ${error.message}`)
    }
  }

  /**
   * Upload multiple images
   * @param {Array<File>} files - Array of image files
   * @param {Object} options - Upload options
   * @returns {Promise<Array>} Array of upload responses
   */
  async uploadMultipleImages(files, options = {}) {
    const uploadPromises = files.map((file, index) => {
      return this.uploadImage(file, {
        ...options,
        onProgress: (progress) => {
          if (options.onProgress) {
            options.onProgress(index, progress)
          }
        }
      })
    })

    try {
      const results = await Promise.all(uploadPromises)
      return results
    } catch (error) {
      throw new Error(`Multiple upload failed: ${error.message}`)
    }
  }

  /**
   * Get user's images with pagination and filters
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.limit - Items per page
   * @param {string} params.sort - Sort field
   * @param {string} params.order - Sort order (asc/desc)
   * @param {string} params.search - Search query
   * @param {Array} params.tags - Filter by tags
   * @param {string} params.privacy - Filter by privacy
   * @param {string} params.dateFrom - Filter by date from
   * @param {string} params.dateTo - Filter by date to
   * @returns {Promise<Object>} Images response with pagination
   */
  async getUserImages(params = {}) {
    const queryParams = new URLSearchParams()
    
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
        if (Array.isArray(params[key])) {
          params[key].forEach(value => queryParams.append(key, value))
        } else {
          queryParams.append(key, params[key])
        }
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
   * Get a single image by ID
   * @param {number} imageId - Image ID
   * @returns {Promise<Object>} Image data
   */
  async getImage(imageId) {
    try {
      const response = await this.api.get(`/api/images/${imageId}`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch image: ${error.message}`)
    }
  }

  /**
   * Update image details
   * @param {number} imageId - Image ID
   * @param {Object} updates - Fields to update
   * @returns {Promise<Object>} Updated image data
   */
  async updateImage(imageId, updates) {
    try {
      const response = await this.api.put(`/api/images/${imageId}`, updates)
      return response
    } catch (error) {
      throw new Error(`Failed to update image: ${error.message}`)
    }
  }

  /**
   * Delete an image
   * @param {number} imageId - Image ID
   * @returns {Promise<boolean>} Success status
   */
  async deleteImage(imageId) {
    try {
      await this.api.delete(`/api/images/${imageId}`)
      return true
    } catch (error) {
      throw new Error(`Failed to delete image: ${error.message}`)
    }
  }

  /**
   * Toggle favorite status for an image
   * @param {number} imageId - Image ID
   * @returns {Promise<Object>} Updated image data
   */
  async toggleFavorite(imageId) {
    try {
      const response = await this.api.post(`/api/images/${imageId}/favorite`)
      return response
    } catch (error) {
      throw new Error(`Failed to toggle favorite: ${error.message}`)
    }
  }

  /**
   * Get image statistics
   * @param {number} imageId - Image ID
   * @returns {Promise<Object>} Image statistics
   */
  async getImageStats(imageId) {
    try {
      const response = await this.api.get(`/api/images/${imageId}/stats`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch image stats: ${error.message}`)
    }
  }

  /**
   * Get user's image statistics
   * @returns {Promise<Object>} User image statistics
   */
  async getUserImageStats() {
    try {
      const response = await this.api.get('/api/images/stats')
      return response
    } catch (error) {
      throw new Error(`Failed to fetch user image stats: ${error.message}`)
    }
  }

  /**
   * Search images across all user's images
   * @param {string} query - Search query
   * @param {Object} filters - Additional filters
   * @returns {Promise<Array>} Search results
   */
  async searchImages(query, filters = {}) {
    try {
      const params = { q: query, ...filters }
      const response = await this.api.get('/api/images/search', { params })
      return response
    } catch (error) {
      throw new Error(`Search failed: ${error.message}`)
    }
  }

  /**
   * Get image download URL
   * @param {number} imageId - Image ID
   * @param {string} size - Image size (thumbnail, medium, large, original)
   * @returns {Promise<string>} Download URL
   */
  async getImageDownloadUrl(imageId, size = 'original') {
    try {
      const response = await this.api.get(`/api/images/${imageId}/download`, {
        params: { size }
      })
      return response.downloadUrl
    } catch (error) {
      throw new Error(`Failed to get download URL: ${error.message}`)
    }
  }

  /**
   * Move image to album
   * @param {number} imageId - Image ID
   * @param {number} albumId - Target album ID
   * @returns {Promise<Object>} Updated image data
   */
  async moveToAlbum(imageId, albumId) {
    try {
      const response = await this.api.put(`/api/images/${imageId}/album`, {
        albumId
      })
      return response
    } catch (error) {
      throw new Error(`Failed to move image to album: ${error.message}`)
    }
  }

  /**
   * Get image sharing information
   * @param {number} imageId - Image ID
   * @returns {Promise<Object>} Sharing information
   */
  async getImageSharing(imageId) {
    try {
      const response = await this.api.get(`/api/images/${imageId}/sharing`)
      return response
    } catch (error) {
      throw new Error(`Failed to fetch sharing info: ${error.message}`)
    }
  }

  /**
   * Update image sharing settings
   * @param {number} imageId - Image ID
   * @param {Object} sharingSettings - New sharing settings
   * @returns {Promise<Object>} Updated sharing information
   */
  async updateImageSharing(imageId, sharingSettings) {
    try {
      const response = await this.api.put(`/api/images/${imageId}/sharing`, sharingSettings)
      return response
    } catch (error) {
      throw new Error(`Failed to update sharing settings: ${error.message}`)
    }
  }

  /**
   * Get recent uploads
   * @param {number} limit - Number of recent uploads to fetch
   * @returns {Promise<Array>} Recent uploads
   */
  async getRecentUploads(limit = 10) {
    try {
      const response = await this.api.get('/api/images/recent', {
        params: { limit }
      })
      return response
    } catch (error) {
      throw new Error(`Failed to fetch recent uploads: ${error.message}`)
    }
  }

  /**
   * Get popular images
   * @param {number} limit - Number of popular images to fetch
   * @param {string} period - Time period (day, week, month, year)
   * @returns {Promise<Array>} Popular images
   */
  async getPopularImages(limit = 10, period = 'month') {
    try {
      const response = await this.api.get('/api/images/popular', {
        params: { limit, period }
      })
      return response
    } catch (error) {
      throw new Error(`Failed to fetch popular images: ${error.message}`)
    }
  }

  /**
   * Validate image file before upload
   * @param {File} file - File to validate
   * @returns {Object} Validation result
   */
  validateImageFile(file) {
    const errors = []
    const maxSize = 10 * 1024 * 1024 // 10MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    
    if (!allowedTypes.includes(file.type)) {
      errors.push('File type not supported. Please use JPG, PNG, GIF, or WebP.')
    }
    
    if (file.size > maxSize) {
      errors.push('File size exceeds 10MB limit.')
    }
    
    return {
      valid: errors.length === 0,
      errors
    }
  }

  /**
   * Generate image thumbnail URL
   * @param {string} imageUrl - Original image URL
   * @param {string} size - Thumbnail size (small, medium, large)
   * @returns {string} Thumbnail URL
   */
  getThumbnailUrl(imageUrl, size = 'medium') {
    // This would typically be handled by the backend
    // For now, return the original URL
    return imageUrl
  }

  /**
   * Get image dimensions from file
   * @param {File} file - Image file
   * @returns {Promise<Object>} Image dimensions
   */
  getImageDimensions(file) {
    return new Promise((resolve, reject) => {
      const img = new Image()
      img.onload = () => {
        resolve({
          width: img.naturalWidth,
          height: img.naturalHeight
        })
      }
      img.onerror = () => {
        reject(new Error('Failed to load image'))
      }
      img.src = URL.createObjectURL(file)
    })
  }
}

export default new ImageService()

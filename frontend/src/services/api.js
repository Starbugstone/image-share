import axios from 'axios'

// Create axios instance
export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8080',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle common errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Unauthorized - clear token and redirect to login
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// API endpoints
export const endpoints = {
  // Auth
  login: '/api/login',
  register: '/api/register',
  logout: '/api/logout',
  
  // User
  userProfile: '/api/user/profile',
  userUpdate: '/api/user/profile',
  
  // Images
  images: '/api/images',
  imageUpload: '/api/images/upload',
  imageShare: '/api/images/share',
  
  // Albums
  albums: '/api/albums',
  albumCreate: '/api/albums/create',
  albumShare: '/api/albums/share',
  
  // Sharing
  shares: '/api/shares',
  availableUsers: '/api/users/available',
  
  // Dashboard
  dashboard: '/api/dashboard',
  dashboardStats: '/api/dashboard/stats'
}

export default api

<template>
  <div class="forgot-password-page">
    <div class="container">
      <div class="forgot-password-container">
        <div class="forgot-password-card">
          <div class="forgot-password-header">
            <h1>
              <i class="fas fa-key"></i>
              Forgot Password?
            </h1>
            <p>No worries! Enter your email address and we'll send you a link to reset your password.</p>
          </div>

          <!-- Success Message -->
          <div v-if="emailSent" class="success-message">
            <div class="success-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="success-content">
              <h3>Email Sent Successfully!</h3>
              <p>{{ successMessage }}</p>
              <div class="success-details">
                <p><strong>Sent to:</strong> {{ form.email }}</p>
                <p><strong>Note:</strong> The reset link will expire in 1 hour. Check your spam folder if you don't see the email.</p>
              </div>
            </div>
          </div>

          <!-- Form -->
          <form v-if="!emailSent" @submit.prevent="handleSubmit" class="forgot-password-form">
            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-input"
                :class="{ error: errors.email }"
                placeholder="Enter your email address"
                required
              >
              <div v-if="errors.email" class="form-error">{{ errors.email }}</div>
            </div>

            <!-- Error Message -->
            <div v-if="errors.general" class="error-message">
              <div class="error-icon">
                <i class="fas fa-exclamation-circle"></i>
              </div>
              <div class="error-content">
                <h4>Error</h4>
                <p>{{ errors.general }}</p>
              </div>
            </div>

            <button 
              type="submit" 
              class="btn btn-primary btn-lg forgot-password-btn"
              :disabled="isLoading"
            >
              <span v-if="isLoading" class="spinner"></span>
              <span v-else>Send Reset Link</span>
            </button>

            <div class="forgot-password-footer">
              <router-link to="/login" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to login
              </router-link>
            </div>
          </form>

          <!-- After email sent -->
          <div v-if="emailSent" class="post-send-actions">
            <button @click="resetForm" class="btn btn-secondary">
              Send another reset link
            </button>
            <router-link to="/login" class="back-link">
              <i class="fas fa-arrow-left"></i>
              Back to login
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/AuthService.js'
import { useNotificationStore } from '../stores/notifications.js'

const router = useRouter()
const notifications = useNotificationStore()

const isLoading = ref(false)
const emailSent = ref(false)
const successMessage = ref('')

const form = reactive({
  email: ''
})

const errors = reactive({
  email: '',
  general: ''
})

const clearErrors = () => {
  errors.email = ''
  errors.general = ''
}

const validateForm = () => {
  clearErrors()
  let isValid = true

  // Trim whitespace
  form.email = form.email.trim()

  if (!form.email) {
    errors.email = 'Email address is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  } else if (form.email.length > 255) {
    errors.email = 'Email address is too long'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) {
    // Show validation error notification
    notifications.error('Please fix the errors below', 'Validation Error')
    return
  }

  isLoading.value = true
  clearErrors()

  try {
    const response = await authService.requestPasswordReset(form.email)
    
    if (response.success) {
      emailSent.value = true
      successMessage.value = response.message
      
      // Show success notification
      notifications.success(
        `Password reset instructions have been sent to ${form.email}`,
        'Email Sent Successfully',
        { duration: 8000 }
      )
    } else {
      errors.general = response.message || 'Failed to send reset email'
      notifications.error(errors.general, 'Reset Failed')
    }
  } catch (error) {
    console.error('Password reset request failed:', error)
    
    let errorMessage = 'An error occurred while sending the reset email. Please try again.'
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.status === 429) {
      errorMessage = 'Too many reset requests. Please wait a few minutes before trying again.'
    } else if (error.response?.status >= 500) {
      errorMessage = 'Server error. Please try again later.'
    } else if (error.code === 'NETWORK_ERROR') {
      errorMessage = 'Network error. Please check your connection and try again.'
    }
    
    errors.general = errorMessage
    notifications.error(errorMessage, 'Reset Failed', { duration: 10000 })
  } finally {
    isLoading.value = false
  }
}

const resetForm = () => {
  emailSent.value = false
  form.email = ''
  clearErrors()
}
</script>

<style scoped>
.forgot-password-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  padding: 2rem 0;
}

.forgot-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.forgot-password-card {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  padding: 3rem;
  width: 100%;
  max-width: 450px;
}

.forgot-password-header {
  text-align: center;
  margin-bottom: 2rem;
}

.forgot-password-header h1 {
  color: var(--primary-color);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 1.75rem;
}

.forgot-password-header p {
  color: var(--text-secondary);
  margin-bottom: 0;
  line-height: 1.5;
}

.forgot-password-form {
  margin-bottom: 2rem;
}

.success-message {
  background: #f0f9ff;
  border: 1px solid #22c55e;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 2rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.success-icon {
  color: #22c55e;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.success-content h3 {
  color: #166534;
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
  font-weight: 600;
}

.success-content p {
  color: #166534;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.success-details {
  font-size: 0.875rem;
  color: #15803d;
}

.success-details p {
  margin-bottom: 0.25rem;
}

.error-message {
  background: #fef2f2;
  border: 1px solid #ef4444;
  border-radius: var(--border-radius);
  padding: 1rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.error-icon {
  color: #ef4444;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.error-content h4 {
  color: #991b1b;
  margin-bottom: 0.25rem;
  font-size: 0.95rem;
  font-weight: 600;
}

.error-content p {
  color: #991b1b;
  margin-bottom: 0;
  font-size: 0.9rem;
  line-height: 1.4;
}

.forgot-password-btn {
  width: 100%;
  margin-bottom: 1.5rem;
}

.forgot-password-footer {
  text-align: center;
}

.back-link {
  color: var(--primary-color);
  text-decoration: none;
  font-size: 0.875rem;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.back-link:hover {
  text-decoration: underline;
}

.post-send-actions {
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.post-send-actions .btn {
  width: 100%;
}

@media (max-width: 768px) {
  .forgot-password-card {
    padding: 2rem;
    margin: 1rem;
  }
  
  .success-message,
  .error-message {
    padding: 1rem;
  }
}
</style><
/style>
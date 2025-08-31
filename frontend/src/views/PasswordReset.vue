<template>
  <div class="password-reset-page">
    <div class="container">
      <div class="password-reset-container">
        <div class="password-reset-card">
          <div class="password-reset-header">
            <h1>
              <i class="fas fa-lock"></i>
              Reset Your Password
            </h1>
            <p>Enter your new password below</p>
          </div>

          <!-- Success Message -->
          <div v-if="resetSuccess" class="success-message">
            <div class="success-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="success-content">
              <h3>Password Reset Successful!</h3>
              <p>{{ successMessage }}</p>
            </div>
            <button @click="goToLogin" class="btn btn-primary btn-lg">
              Go to Login
            </button>
          </div>

          <!-- Form -->
          <form v-if="!resetSuccess" @submit.prevent="handleSubmit" class="password-reset-form">
            <div class="form-group">
              <label for="password" class="form-label">New Password</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-input"
                :class="{ error: errors.password }"
                placeholder="Enter your new password"
                required
              >
              <div v-if="errors.password" class="form-error">{{ errors.password }}</div>
              <div class="password-requirements">
                <p>Password requirements:</p>
                <ul>
                  <li>At least 8 characters long</li>
                  <li>Contains uppercase and lowercase letters</li>
                  <li>Contains at least one number</li>
                </ul>
              </div>
            </div>

            <div class="form-group">
              <label for="password_confirmation" class="form-label">Confirm New Password</label>
              <input
                id="password_confirmation"
                v-model="form.passwordConfirmation"
                type="password"
                class="form-input"
                :class="{ error: errors.passwordConfirmation }"
                placeholder="Confirm your new password"
                required
              >
              <div v-if="errors.passwordConfirmation" class="form-error">{{ errors.passwordConfirmation }}</div>
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
              class="btn btn-primary btn-lg password-reset-btn"
              :disabled="isLoading"
            >
              <span v-if="isLoading" class="spinner"></span>
              <span v-else>Reset Password</span>
            </button>

            <div class="password-reset-footer">
              <router-link to="/login" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to login
              </router-link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import authService from '../services/AuthService.js'
import { useNotificationStore } from '../stores/notifications.js'

const router = useRouter()
const route = useRoute()
const notifications = useNotificationStore()

const isLoading = ref(false)
const resetSuccess = ref(false)
const successMessage = ref('')
const resetToken = ref('')

const form = reactive({
  password: '',
  passwordConfirmation: ''
})

const errors = reactive({
  password: '',
  passwordConfirmation: '',
  general: ''
})

onMounted(() => {
  // Get token from URL query parameter
  resetToken.value = route.query.token || ''
  
  if (!resetToken.value) {
    errors.general = 'Invalid or missing reset token. Please request a new password reset.'
  }
})

const clearErrors = () => {
  errors.password = ''
  errors.passwordConfirmation = ''
  errors.general = ''
}

const validateForm = () => {
  clearErrors()
  let isValid = true

  if (!form.password) {
    errors.password = 'Password is required'
    isValid = false
  } else if (form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters long'
    isValid = false
  } else if (form.password.length > 255) {
    errors.password = 'Password is too long'
    isValid = false
  } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(form.password)) {
    errors.password = 'Password must contain at least one uppercase letter, one lowercase letter, and one number'
    isValid = false
  }

  if (!form.passwordConfirmation) {
    errors.passwordConfirmation = 'Password confirmation is required'
    isValid = false
  } else if (form.password !== form.passwordConfirmation) {
    errors.passwordConfirmation = 'Passwords do not match'
    isValid = false
  }

  if (!resetToken.value) {
    errors.general = 'Invalid or missing reset token'
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
    const response = await authService.resetPassword(
      resetToken.value,
      form.password,
      form.passwordConfirmation
    )
    
    if (response.success) {
      resetSuccess.value = true
      successMessage.value = response.message
      
      // Show success notification
      notifications.success(
        'Your password has been successfully reset. You can now log in with your new password.',
        'Password Reset Complete',
        { duration: 8000 }
      )
    } else {
      errors.general = response.message || 'Failed to reset password'
      notifications.error(errors.general, 'Reset Failed')
    }
  } catch (error) {
    console.error('Password reset failed:', error)
    
    let errorMessage = 'An error occurred while resetting your password. Please try again.'
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.status === 400) {
      errorMessage = 'Invalid or expired reset token. Please request a new password reset.'
    } else if (error.response?.status === 422) {
      errorMessage = 'Please check your password requirements and try again.'
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

const goToLogin = () => {
  router.push('/login')
}
</script>

<style scoped>
.password-reset-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  padding: 2rem 0;
}

.password-reset-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.password-reset-card {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  padding: 3rem;
  width: 100%;
  max-width: 450px;
}

.password-reset-header {
  text-align: center;
  margin-bottom: 2rem;
}

.password-reset-header h1 {
  color: var(--primary-color);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 1.75rem;
}

.password-reset-header p {
  color: var(--text-secondary);
  margin-bottom: 0;
  line-height: 1.5;
}

.password-reset-form {
  margin-bottom: 2rem;
}

.success-message {
  background: #f0f9ff;
  border: 1px solid #22c55e;
  border-radius: var(--border-radius);
  padding: 2rem;
  margin-bottom: 2rem;
  text-align: center;
}

.success-icon {
  color: #22c55e;
  font-size: 3rem;
  margin-bottom: 1rem;
}

.success-content h3 {
  color: #166534;
  margin-bottom: 0.5rem;
  font-size: 1.25rem;
  font-weight: 600;
}

.success-content p {
  color: #166534;
  margin-bottom: 1.5rem;
  line-height: 1.4;
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

.password-requirements {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.password-requirements p {
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.password-requirements ul {
  list-style-type: disc;
  list-style-position: inside;
  margin-left: 0.5rem;
}

.password-requirements li {
  margin-bottom: 0.125rem;
}

.password-reset-btn {
  width: 100%;
  margin-bottom: 1.5rem;
}

.password-reset-footer {
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

@media (max-width: 768px) {
  .password-reset-card {
    padding: 2rem;
    margin: 1rem;
  }
  
  .success-message,
  .error-message {
    padding: 1rem;
  }
  
  .success-icon {
    font-size: 2rem;
  }
}
</style>
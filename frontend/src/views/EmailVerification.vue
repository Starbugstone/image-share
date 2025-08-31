<template>
  <div class="verification-page">
    <div class="container">
      <div class="verification-container">
        <div class="verification-card">
          <!-- Loading State -->
          <div v-if="loading" class="verification-loading">
            <div class="spinner-large"></div>
            <h2>Verifying your email...</h2>
            <p>Please wait while we confirm your email address.</p>
          </div>

          <!-- Success State -->
          <div v-else-if="success" class="verification-success">
            <div class="success-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <h2>Email Verified Successfully!</h2>
            <p>Your email address has been confirmed. You can now access all features of ImageShare.</p>
            
            <div class="verification-actions">
              <router-link to="/login" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt"></i>
                Sign In to Your Account
              </router-link>
              <router-link to="/dashboard" class="btn btn-secondary">
                <i class="fas fa-tachometer-alt"></i>
                Go to Dashboard
              </router-link>
            </div>
          </div>

          <!-- Error State -->
          <div v-else class="verification-error">
            <div class="error-icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2>Verification Failed</h2>
            <p class="error-message">{{ errorMessage }}</p>
            
            <div class="error-details" v-if="errorDetails">
              <h4>What went wrong?</h4>
              <ul>
                <li v-if="errorDetails.expired">The verification link has expired</li>
                <li v-if="errorDetails.invalid">The verification link is invalid or malformed</li>
                <li v-if="errorDetails.alreadyVerified">This email address is already verified</li>
                <li v-if="errorDetails.userNotFound">User account not found</li>
              </ul>
            </div>

            <div class="verification-actions">
              <button @click="resendVerification" class="btn btn-primary" :disabled="resending">
                <span v-if="resending" class="spinner"></span>
                <i v-else class="fas fa-envelope"></i>
                {{ resending ? 'Sending...' : 'Resend Verification Email' }}
              </button>
              <router-link to="/register" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i>
                Create New Account
              </router-link>
              <router-link to="/login" class="btn btn-outline">
                <i class="fas fa-sign-in-alt"></i>
                Back to Login
              </router-link>
            </div>
          </div>
        </div>

        <!-- Help Section -->
        <div class="verification-help">
          <h3>Need Help?</h3>
          <div class="help-grid">
            <div class="help-item">
              <i class="fas fa-clock"></i>
              <h4>Link Expired?</h4>
              <p>Verification links expire after 1 hour for security. Request a new one above.</p>
            </div>
            <div class="help-item">
              <i class="fas fa-envelope"></i>
              <h4>No Email Received?</h4>
              <p>Check your spam folder or contact support if you're still having issues.</p>
            </div>
            <div class="help-item">
              <i class="fas fa-shield-alt"></i>
              <h4>Security</h4>
              <p>Email verification helps keep your account secure and enables all features.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useNotificationStore } from '../stores/notifications.js'
import apiService from '../services/ApiService.js'

const route = useRoute()
const router = useRouter()
const notificationStore = useNotificationStore()

// State
const loading = ref(true)
const success = ref(false)
const errorMessage = ref('')
const errorDetails = ref(null)
const resending = ref(false)

// Methods
const verifyEmail = async () => {
  try {
    loading.value = true
    
    // Get verification parameters from URL
    const params = {
      id: route.query.id,
      token: route.query.token,
      expires: route.query.expires,
      signature: route.query.signature
    }

    // Validate required parameters
    if (!params.id || !params.token || !params.expires || !params.signature) {
      throw new Error('Invalid verification link. Missing required parameters.')
    }

    // Make verification request to backend
    const response = await apiService.post('/api/verify-email', params)
    
    if (response.success) {
      success.value = true
      notificationStore.success('Email verified successfully! You can now sign in.')
    } else {
      throw new Error(response.error || 'Verification failed')
    }

  } catch (error) {
    console.error('Email verification error:', error)
    
    success.value = false
    errorMessage.value = error.response?.data?.message || error.message || 'An error occurred during verification'
    
    // Parse error details for better user experience
    errorDetails.value = parseErrorDetails(error)
    
  } finally {
    loading.value = false
  }
}

const parseErrorDetails = (error) => {
  const message = error.response?.data?.message || error.message || ''
  
  return {
    expired: message.includes('expired') || message.includes('timeout'),
    invalid: message.includes('invalid') || message.includes('malformed'),
    alreadyVerified: message.includes('already verified') || message.includes('already confirmed'),
    userNotFound: message.includes('user not found') || message.includes('not found')
  }
}

const resendVerification = async () => {
  try {
    resending.value = true
    
    // Extract user ID from the original verification link
    const userId = route.query.id
    if (!userId) {
      throw new Error('Cannot resend verification email. User ID not found.')
    }

    const response = await apiService.post('/api/resend-verification', { userId })
    
    if (response.success) {
      notificationStore.success('Verification email sent! Please check your inbox.')
    } else {
      throw new Error(response.error || 'Failed to resend verification email')
    }

  } catch (error) {
    console.error('Resend verification error:', error)
    notificationStore.error(error.response?.data?.message || error.message || 'Failed to resend verification email')
  } finally {
    resending.value = false
  }
}

// Initialize verification on component mount
onMounted(() => {
  verifyEmail()
})
</script>

<style scoped>
.verification-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  padding: 2rem 0;
}

.verification-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  gap: 2rem;
}

.verification-card {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  padding: 3rem;
  width: 100%;
  max-width: 600px;
  text-align: center;
}

.verification-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.spinner-large {
  width: 60px;
  height: 60px;
  border: 4px solid var(--border-color);
  border-top: 4px solid var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.verification-success .success-icon {
  font-size: 4rem;
  color: var(--success-color);
  margin-bottom: 1rem;
}

.verification-error .error-icon {
  font-size: 4rem;
  color: var(--error-color);
  margin-bottom: 1rem;
}

.verification-success h2 {
  color: var(--success-color);
  margin-bottom: 1rem;
}

.verification-error h2 {
  color: var(--error-color);
  margin-bottom: 1rem;
}

.error-message {
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
  font-size: 1.1rem;
}

.error-details {
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin: 1.5rem 0;
  text-align: left;
}

.error-details h4 {
  color: var(--text-primary);
  margin-bottom: 1rem;
}

.error-details ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.error-details li {
  padding: 0.5rem 0;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
}

.error-details li::before {
  content: '•';
  color: var(--error-color);
  margin-right: 0.5rem;
}

.verification-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 2rem;
}

.verification-actions .btn {
  justify-content: center;
  gap: 0.5rem;
}

.verification-help {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  padding: 2rem;
  width: 100%;
}

.verification-help h3 {
  text-align: center;
  color: var(--text-primary);
  margin-bottom: 1.5rem;
}

.help-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.help-item {
  text-align: center;
  padding: 1rem;
}

.help-item i {
  font-size: 2rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.help-item h4 {
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.help-item p {
  color: var(--text-secondary);
  font-size: 0.9rem;
  line-height: 1.4;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .verification-card {
    padding: 2rem;
    margin: 1rem;
  }
  
  .verification-actions {
    gap: 0.75rem;
  }
  
  .help-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>
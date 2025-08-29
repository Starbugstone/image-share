<template>
  <div class="register-page">
    <div class="container">
      <div class="register-container">
        <div class="register-card">
          <div class="register-header">
            <h1>
              <i class="fas fa-user-plus"></i>
              Create Account
            </h1>
            <p>Join ImageShare and start sharing your moments</p>
          </div>

          <form @submit.prevent="handleRegister" class="register-form">
            <div class="form-group">
              <label for="username" class="form-label">Username</label>
              <input
                id="username"
                v-model="form.username"
                type="text"
                class="form-input"
                :class="{ error: errors.username }"
                placeholder="Choose a username"
                required
              >
              <div v-if="errors.username" class="form-error">{{ errors.username }}</div>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-input"
                :class="{ error: errors.email }"
                placeholder="Enter your email"
                required
              >
              <div v-if="errors.email" class="form-error">{{ errors.email }}</div>
            </div>

            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <div class="password-input">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-input"
                  :class="{ error: errors.password }"
                  placeholder="Create a password"
                  required
                >
                <button 
                  type="button" 
                  class="password-toggle"
                  @click="togglePassword"
                >
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
              <div v-if="errors.password" class="form-error">{{ errors.password }}</div>
            </div>

            <div class="form-group">
              <label for="confirmPassword" class="form-label">Confirm Password</label>
              <input
                id="confirmPassword"
                v-model="form.confirmPassword"
                type="password"
                class="form-input"
                :class="{ error: errors.confirmPassword }"
                placeholder="Confirm your password"
                required
              >
              <div v-if="errors.confirmPassword" class="form-error">{{ errors.confirmPassword }}</div>
            </div>

            <div class="form-options">
              <label class="checkbox-label">
                <input 
                  v-model="form.agreeTerms" 
                  type="checkbox"
                  class="checkbox-input"
                  required
                >
                <span class="checkbox-custom"></span>
                I agree to the <router-link to="/terms" class="terms-link">Terms of Service</router-link>
              </label>
            </div>

            <button 
              type="submit" 
              class="btn btn-primary btn-lg register-btn"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner"></span>
              <span v-else>Create Account</span>
            </button>
          </form>

          <div class="register-footer">
            <p>
              Already have an account? 
              <router-link to="/login" class="login-link">Sign in</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notification'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Form data
const form = reactive({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  agreeTerms: false
})

// UI state
const loading = ref(false)
const showPassword = ref(false)
const errors = reactive({})

// Methods
const handleRegister = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key])
  
  // Validation
  if (!form.username) {
    errors.username = 'Username is required'
    return
  }
  
  if (!form.email) {
    errors.email = 'Email is required'
    return
  }
  
  if (!form.password) {
    errors.password = 'Password is required'
    return
  }
  
  if (form.password !== form.confirmPassword) {
    errors.confirmPassword = 'Passwords do not match'
    return
  }
  
  if (!form.agreeTerms) {
    notificationStore.showError('You must agree to the Terms of Service')
    return
  }
  
  try {
    loading.value = true
    
    const result = await authStore.register({
      username: form.username,
      email: form.email,
      password: form.password
    })
    
    if (result.success) {
      notificationStore.showSuccess('Account created successfully! Please check your email to verify your account.')
      router.push('/login')
    } else {
      notificationStore.showError(result.error)
    }
  } catch (error) {
    console.error('Registration error:', error)
    notificationStore.showError('An unexpected error occurred')
  } finally {
    loading.value = false
  }
}

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  padding: 2rem 0;
}

.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.register-card {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  padding: 3rem;
  width: 100%;
  max-width: 500px;
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.register-header h1 {
  color: var(--primary-color);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.register-header p {
  color: var(--text-secondary);
  margin-bottom: 0;
}

.register-form {
  margin-bottom: 2rem;
}

.password-input {
  position: relative;
  display: flex;
  align-items: center;
}

.password-toggle {
  position: absolute;
  right: 1rem;
  background: none;
  border: none;
  color: var(--text-light);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: var(--transition);
}

.password-toggle:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.form-options {
  margin-bottom: 1.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.checkbox-input {
  display: none;
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  border: 2px solid var(--border-color);
  border-radius: 4px;
  display: inline-block;
  position: relative;
  transition: var(--transition);
}

.checkbox-input:checked + .checkbox-custom {
  background: var(--primary-color);
  border-color: var(--primary-color);
}

.checkbox-input:checked + .checkbox-custom::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 12px;
  font-weight: bold;
}

.terms-link {
  color: var(--primary-color);
  text-decoration: none;
  transition: var(--transition);
}

.terms-link:hover {
  text-decoration: underline;
}

.register-btn {
  width: 100%;
  margin-bottom: 1rem;
}

.register-footer {
  text-align: center;
}

.register-footer p {
  margin-bottom: 0;
  color: var(--text-secondary);
}

.login-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  transition: var(--transition);
}

.login-link:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .register-card {
    padding: 2rem;
    margin: 1rem;
  }
}
</style>

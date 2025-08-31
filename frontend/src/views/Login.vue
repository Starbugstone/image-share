<template>
  <div class="login-page">
    <div class="container">
      <div class="login-container">
        <div class="login-card">
          <div class="login-header">
            <h1>
              <i class="fas fa-images"></i>
              Welcome Back
            </h1>
            <p>Sign in to your ImageShare account</p>
          </div>

          <form @submit.prevent="handleLogin" class="login-form">
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
                  placeholder="Enter your password"
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

                         <div class="form-options">
               <label class="checkbox-label">
                 <input 
                   v-model="form.rememberMe" 
                   type="checkbox"
                   class="checkbox-input"
                 >
                 <span class="checkbox-custom"></span>
                 Remember me
               </label>
               <router-link to="/password/forgot" class="forgot-link">
                 Forgot password?
               </router-link>
             </div>

            <button 
              type="submit" 
              class="btn btn-primary btn-lg login-btn"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner"></span>
              <span v-else>Sign In</span>
            </button>
          </form>

          <div class="login-divider">
            <span>or</span>
          </div>

          <div class="social-login">
            <button class="btn btn-secondary social-btn">
              <i class="fab fa-google"></i>
              Continue with Google
            </button>
            <button class="btn btn-secondary social-btn">
              <i class="fab fa-facebook"></i>
              Continue with Facebook
            </button>
          </div>

          <div class="login-footer">
            <p>
              Don't have an account? 
              <router-link to="/register" class="register-link">Sign up</router-link>
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
import { useNotificationStore } from '../stores/notifications.js'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Form data
const form = reactive({
  email: '',
  password: '',
  rememberMe: false
})

// UI state
const loading = ref(false)
const showPassword = ref(false)
const errors = reactive({})

// Methods
const handleLogin = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key])
  
  // Validation
  if (!form.email) {
    errors.email = 'Email is required'
    return
  }
  
  if (!form.password) {
    errors.password = 'Password is required'
    return
  }
  
  try {
    loading.value = true
    
    const result = await authStore.login({
      email: form.email,
      password: form.password
    })
    
    if (result && result.user) {
      notificationStore.success('Welcome back!')
      router.push('/dashboard')
    }
  } catch (error) {
    console.error('Login error:', error)
    
    // Handle specific error messages from the server
    let errorMessage = 'An unexpected error occurred'
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.data?.error) {
      errorMessage = error.response.data.error
    } else if (error.response?.status === 401) {
      errorMessage = 'Invalid email or password. Please check your credentials and try again.'
    } else if (error.response?.status === 422) {
      errorMessage = 'Please check your input and try again.'
    } else if (error.message) {
      errorMessage = error.message
    }
    
    // Show error with longer duration for login errors
    notificationStore.error(errorMessage, 'Login Failed', { duration: 10000 })
  } finally {
    loading.value = false
  }
}

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  padding: 2rem 0;
}

.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.login-card {
  background: var(--bg-primary);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  padding: 3rem;
  width: 100%;
  max-width: 450px;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  color: var(--primary-color);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.login-header p {
  color: var(--text-secondary);
  margin-bottom: 0;
}

.login-form {
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
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.forgot-link {
  color: var(--primary-color);
  text-decoration: none;
  font-size: 0.875rem;
  transition: var(--transition);
}

.forgot-link:hover {
  text-decoration: underline;
}

.login-btn {
  width: 100%;
  margin-bottom: 1rem;
}

.login-divider {
  text-align: center;
  margin: 2rem 0;
  position: relative;
}

.login-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: var(--border-color);
}

.login-divider span {
  background: var(--bg-primary);
  padding: 0 1rem;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.social-login {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.social-btn {
  width: 100%;
  justify-content: center;
  gap: 0.5rem;
}

.login-footer {
  text-align: center;
}

.login-footer p {
  margin-bottom: 0;
  color: var(--text-secondary);
}

.register-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  transition: var(--transition);
}

.register-link:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .login-card {
    padding: 2rem;
    margin: 1rem;
  }
  
  .form-options {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}
</style>

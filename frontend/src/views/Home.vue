<template>
  <div class="home-page">
    <div class="hero-section">
      <div class="container">
        <div class="hero-content">
          <h1>Share Your Moments with the World</h1>
          <p>ImageShare is the ultimate platform for sharing, organizing, and discovering amazing photos. Connect with friends, create albums, and build your visual story.</p>
          <div class="hero-actions">
            <router-link to="/register" class="btn btn-primary btn-lg">
              Get Started
            </router-link>
            <router-link to="/login" class="btn btn-secondary btn-lg">
              Sign In
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div class="features-section">
      <div class="container">
        <h2 class="section-title">Why Choose ImageShare?</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Secure Sharing</h3>
            <p>Your photos are protected with enterprise-grade security. Share only with who you choose.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-users"></i>
            </div>
            <h3>Social Network</h3>
            <p>Connect with friends and family. Discover new photographers and amazing content.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-folder-tree"></i>
            </div>
            <h3>Smart Organization</h3>
            <p>Create albums, add tags, and organize your photos with intelligent categorization.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-mobile-alt"></i>
            </div>
            <h3>Mobile First</h3>
            <p>Optimized for all devices. Upload, share, and browse from anywhere, anytime.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  console.log('Home.vue: Component mounted successfully')
})
</script>

<style scoped>
.home-page {
  min-height: 100vh;
}

.hero-section {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
  padding: 6rem 0;
  text-align: center;
}

.hero-content h1 {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
  color: white;
}

.hero-content p {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  color: rgba(255, 255, 255, 0.9);
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.features-section {
  padding: 5rem 0;
  background: var(--bg-secondary);
}

.section-title {
  text-align: center;
  margin-bottom: 3rem;
  color: var(--text-color);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: var(--bg-primary);
  padding: 2rem;
  border-radius: var(--border-radius);
  text-align: center;
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
}

.feature-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.feature-icon {
  font-size: 3rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.feature-card h3 {
  margin-bottom: 1rem;
  color: var(--text-color);
}

.feature-card p {
  color: var(--text-muted);
  margin-bottom: 0;
}

@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 2.5rem;
  }
  
  .hero-content p {
    font-size: 1.125rem;
  }
  
  .hero-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .hero-actions .btn {
    width: 200px;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
}
</style>

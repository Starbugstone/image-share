<template>
  <div class="loading-spinner" :class="[`spinner-${size}`, `spinner-${variant}`]">
    <div class="spinner-ring"></div>
    <div v-if="showText" class="spinner-text">{{ text || 'Loading...' }}</div>
  </div>
</template>

<script setup>
// Props
defineProps({
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'error'].includes(value)
  },
  text: {
    type: String,
    default: ''
  },
  showText: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.spinner-ring {
  border: 3px solid transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Size variants */
.spinner-small .spinner-ring {
  width: 20px;
  height: 20px;
  border-width: 2px;
}

.spinner-medium .spinner-ring {
  width: 32px;
  height: 32px;
  border-width: 3px;
}

.spinner-large .spinner-ring {
  width: 48px;
  height: 48px;
  border-width: 4px;
}

/* Color variants */
.spinner-primary .spinner-ring {
  border-top-color: var(--primary-color);
}

.spinner-secondary .spinner-ring {
  border-top-color: var(--secondary-color);
}

.spinner-success .spinner-ring {
  border-top-color: var(--success-color);
}

.spinner-warning .spinner-ring {
  border-top-color: var(--warning-color);
}

.spinner-error .spinner-ring {
  border-top-color: var(--error-color);
}

.spinner-text {
  font-size: 0.9rem;
  color: var(--text-muted);
  text-align: center;
  font-weight: 500;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Pulse variant for loading states */
.loading-spinner.pulse .spinner-ring {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>

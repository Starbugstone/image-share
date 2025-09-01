<template>
  <div class="profile-page">
    <div class="container">
      <h1>Edit Profile</h1>
      <form @submit.prevent="saveProfile" class="mt-4">
        <div class="mb-3">
          <label class="form-label">Profile Image</label>
          <div class="d-flex align-items-center">
            <img v-if="imagePreview" :src="imagePreview" alt="Profile image" class="profile-image rounded-circle me-3">
            <div v-else class="profile-image placeholder rounded-circle me-3"></div>
            <input type="file" class="form-control" accept="image/*" @change="onFileChange">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" :value="user.username" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" :value="user.email" disabled>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Display Name</label>
            <input type="text" class="form-control" v-model="form.displayName" placeholder="Enter your display name">
          </div>
          <div class="col-md-6">
            <label class="form-label">Location</label>
            <input type="text" class="form-control" v-model="form.location" placeholder="City, Country">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Website</label>
            <input type="url" class="form-control" v-model="form.website" placeholder="https://example.com">
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" v-model="form.status">
              <option value="online">Online</option>
              <option value="away">Away</option>
              <option value="offline">Offline</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Bio</label>
          <textarea class="form-control" rows="4" v-model="form.bio" placeholder="Tell us about yourself..."></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="isPublic" v-model="form.isPublic">
          <label class="form-check-label" for="isPublic">Public profile</label>
        </div>

        <button type="submit" class="btn btn-primary" :disabled="saving">
          Save Changes
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useAuthStore } from '../stores/auth.js'

const authStore = useAuthStore()
const user = computed(() => authStore.currentUser)

const form = reactive({
  displayName: user.value?.profile?.displayName || '',
  location: user.value?.profile?.location || '',
  website: user.value?.profile?.website || '',
  status: user.value?.profile?.status || 'offline',
  bio: user.value?.profile?.bio || '',
  isPublic: user.value?.profile?.isPublic ?? true
})

const profileImage = ref(null)
const imagePreview = ref(user.value?.profile?.image_url || '')

function onFileChange(e) {
  const file = e.target.files[0]
  profileImage.value = file
  if (file) {
    imagePreview.value = URL.createObjectURL(file)
  }
}

const saving = ref(false)

async function saveProfile() {
  try {
    saving.value = true
    const formData = new FormData()
    Object.entries(form).forEach(([key, value]) => {
      formData.append(key, value)
    })
    if (profileImage.value) {
      formData.append('profileImage', profileImage.value)
    }
    const updated = await authStore.updateProfile(formData)
    if (updated?.profile?.image_url) {
      imagePreview.value = updated.profile.image_url
    }
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.profile-page {
  padding: 2rem 0;
}

.profile-image {
  width: 100px;
  height: 100px;
  object-fit: cover;
}

.profile-image.placeholder {
  background-color: #e9ecef;
}
</style>

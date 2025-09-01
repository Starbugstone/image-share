<template>
  <AppLayout>
    <div class="profile-page">
      <div class="profile-hero">
        <div class="container">
          <div class="hero-content">
            <div class="avatar-wrapper">
              <img
                :src="imagePreview || authStore.profileImageUrl"
                :alt="authStore.displayName"
                class="avatar"
                @error="handleAvatarError"
              />
              <button class="avatar-edit" @click="openAvatarUpload" title="Update avatar">
                <i class="fas fa-camera"></i>
              </button>
            </div>
            <div class="hero-text">
              <h1>{{ authStore.displayName || user?.username }}</h1>
              <p>
                <span class="username">@{{ user?.username }}</span>
                <span class="dot">•</span>
                <span class="email">{{ user?.email }}</span>
              </p>
              <div class="chips">
                <span class="chip status">
                  <span class="status-dot" :class="userStatusClass"></span>
                  {{ userStatusText }}
                </span>
                <span class="chip" :class="form.isPublic ? 'chip-public' : 'chip-private'">
                  {{ form.isPublic ? 'Public' : 'Private' }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="ornament o1"></div>
        <div class="ornament o2"></div>
      </div>

      <div class="container">
        <div class="grid">
          <div class="col main">
            <div class="card modern">
              <div class="card-header">
                <h2 class="card-title"><i class="fas fa-id-card"></i> Profile Details</h2>
                <p class="card-subtitle">Make it yours — update how others see you.</p>
              </div>
              <form @submit.prevent="saveProfile">
                <div class="form-grid">
                  <div class="form-group">
                    <label class="form-label">Display Name</label>
                    <input type="text" class="form-input" v-model="form.displayName" placeholder="Enter your display name" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-input" v-model="form.location" placeholder="City, Country" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="url" class="form-input" v-model="form.website" placeholder="https://example.com" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" v-model="form.status">
                      <option value="online">Online</option>
                      <option value="away">Away</option>
                      <option value="offline">Offline</option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Bio</label>
                  <textarea class="form-input" rows="4" v-model="form.bio" placeholder="Tell us about yourself..."></textarea>
                </div>

                <label class="toggle">
                  <input type="checkbox" v-model="form.isPublic" />
                  <span class="toggle-slider"></span>
                  <span class="toggle-label">Public profile</span>
                </label>

                <div class="actions">
                  <button type="submit" class="btn btn-primary" :disabled="saving">
                    <i class="fas fa-save" :class="{ 'fa-spin': saving }"></i>
                    <span>Save Changes</span>
                  </button>
                </div>
              </form>
            </div>

            <div class="card modern">
              <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user"></i> Account</h2>
              </div>
              <div class="form-grid single">
                <div class="form-group">
                  <label class="form-label">Username</label>
                  <input type="text" class="form-input" :value="user?.username" disabled />
                </div>
                <div class="form-group">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-input" :value="user?.email" disabled />
                </div>
              </div>
              <div class="hint">Manage email and password from Settings.</div>
            </div>
          </div>

          <div class="col side">
            <div class="card modern">
              <div class="card-header">
                <h2 class="card-title"><i class="fas fa-image"></i> Profile Picture</h2>
                <p class="card-subtitle">Drag & drop to update your avatar</p>
              </div>
              <div class="pond-wrap">
                <FilePond
                  :allow-multiple="false"
                  :accepted-file-types="['image/*']"
                  :label-idle="pondLabel"
                  :image-crop-aspect-ratio="'1:1'"
                  :style-panel-layout="'compact circle'"
                  :image-resize-target-width="512"
                  :image-resize-mode="'cover'"
                  :image-transform-output-quality="0.9"
                  :image-transform-output-strip-image-head="true"
                  @updatefiles="handlePondFiles"
                />
              </div>
            </div>

            <div class="card modern">
              <div class="card-header">
                <h2 class="card-title"><i class="fas fa-chart-line"></i> Quick Stats</h2>
              </div>
              <div class="stats-grid">
                <div class="stat">
                  <div class="stat-value">{{ authStore.userStats.total_images || 0 }}</div>
                  <div class="stat-label">Images</div>
                </div>
                <div class="stat">
                  <div class="stat-value">{{ authStore.userStats.total_albums || 0 }}</div>
                  <div class="stat-label">Albums</div>
                </div>
                <div class="stat">
                  <div class="stat-value">{{ authStore.userStats.shared_items || 0 }}</div>
                  <div class="stat-label">Shared</div>
                </div>
                <div class="stat">
                  <div class="stat-value">{{ authStore.userStats.received_shares || 0 }}</div>
                  <div class="stat-label">Received</div>
                </div>
              </div>
            </div>

            <div class="card modern accent">
              <div class="card-header">
                <h2 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
              </div>
              <div class="actions-quick">
                <router-link to="/upload" class="btn btn-secondary"><i class="fas fa-cloud-upload-alt"></i> Upload Image</router-link>
                <router-link to="/albums/create" class="btn btn-secondary"><i class="fas fa-folder-plus"></i> Create Album</router-link>
                <router-link to="/images" class="btn btn-secondary"><i class="fas fa-images"></i> Manage Images</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { useAuthStore } from '../stores/auth.js'
import vueFilePond from 'vue-filepond'
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import FilePondPluginImageCrop from 'filepond-plugin-image-crop'
import FilePondPluginImageResize from 'filepond-plugin-image-resize'
import FilePondPluginImageTransform from 'filepond-plugin-image-transform'

const FilePond = vueFilePond(
  FilePondPluginImagePreview,
  FilePondPluginFileValidateType,
  FilePondPluginImageCrop,
  FilePondPluginImageResize,
  FilePondPluginImageTransform
)

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
const pondLabel = 'Drop an image or <span class="filepond--label-action">Browse</span>'

function onFileChange(e) {
  const file = e.target.files[0]
  profileImage.value = file
  if (file) {
    imagePreview.value = URL.createObjectURL(file)
  }
}

function handlePondFiles(files) {
  if (files && files.length > 0) {
    const f = files[0].file
    profileImage.value = f
    imagePreview.value = URL.createObjectURL(f)
  } else {
    profileImage.value = null
  }
}

function openAvatarUpload() {
  const el = document.querySelector('.pond-wrap .filepond--root')
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
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
.profile-page { padding-bottom: 2rem; }

.profile-hero {
  position: relative;
  background: linear-gradient(135deg, var(--primary-color-very-light), var(--primary-color-light));
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 2rem;
  overflow: hidden;
}

.hero-content {
  display: flex;
  align-items: center;
  gap: 1.75rem;
  padding: 2rem 0;
}

.avatar-wrapper { position: relative; width: 128px; height: 128px; }
.avatar {
  width: 128px; height: 128px; border-radius: 50%; object-fit: cover;
  border: 4px solid #fff; box-shadow: var(--shadow-md);
}
.avatar-edit {
  position: absolute; bottom: 6px; right: 6px; width: 40px; height: 40px; border-radius: 50%;
  background: var(--primary-color); color: #fff; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-md);
}

.hero-text h1 { margin: 0 0 0.25rem; }
.hero-text p { margin: 0.25rem 0 0.5rem; }
.username { color: var(--text-color); }
.email { color: var(--text-muted); }
.dot { margin: 0 0.5rem; color: var(--text-light); }

.chips { display: flex; gap: 0.5rem; align-items: center; }
.chip {
  display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.6rem;
  border-radius: 999px; font-size: 0.8rem; background: var(--bg-primary); border: 1px solid var(--border-color);
}
.chip-public { background: var(--primary-color-very-light); color: var(--primary-color); border-color: var(--primary-color-light); }
.chip-private { background: #fff3f3; color: var(--error-color); border-color: #ffd6d6; }

.ornament { position: absolute; filter: blur(40px); opacity: 0.35; pointer-events: none; }
.o1 { width: 220px; height: 220px; background: radial-gradient(circle at 30% 30%, #a78bfa, transparent 60%); top: -60px; right: 5%; }
.o2 { width: 260px; height: 260px; background: radial-gradient(circle at 70% 70%, #60a5fa, transparent 60%); bottom: -80px; left: 10%; }

.grid { display: grid; grid-template-columns: 7fr 5fr; gap: 1.75rem; }
.col { display: flex; flex-direction: column; gap: 1.5rem; }
.main { min-width: 0; }
.side { position: sticky; top: 90px; height: fit-content; }

.card.modern {
  background: rgba(255,255,255,0.75);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
  backdrop-filter: blur(10px);
}
.card.modern.accent { border-image: linear-gradient(45deg, var(--primary-color), var(--secondary-color)) 1; }
.card-header { display: flex; align-items: baseline; justify-content: space-between; }
.card-subtitle { margin: 0; color: var(--text-muted); font-size: 0.95rem; }

.form-grid { display: grid; grid-template-columns: repeat(2, minmax(280px, 1fr)); gap: 1.25rem; }
.form-grid.single { grid-template-columns: 1fr; }

.toggle { display: inline-flex; align-items: center; gap: 0.75rem; cursor: pointer; margin-top: 0.5rem; }
.toggle input { display: none; }
.toggle-slider { width: 44px; height: 24px; background: var(--border-color); border-radius: 999px; position: relative; transition: var(--transition); }
.toggle-slider::after { content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background: white; border-radius: 50%; box-shadow: var(--shadow-sm); transition: var(--transition); }
.toggle input:checked + .toggle-slider { background: var(--primary-color); }
.toggle input:checked + .toggle-slider::after { transform: translateX(20px); }
.toggle-label { color: var(--text-color); font-size: 0.95rem; }

.actions { margin-top: 1rem; }

.stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.stat { padding: 0.85rem 1rem; background: var(--bg-tertiary); border-radius: var(--border-radius); }
.stat-value { font-size: 1.25rem; font-weight: 700; color: var(--text-color); }
.stat-label { font-size: 0.85rem; color: var(--text-muted); }

.actions-quick { display: flex; flex-direction: column; gap: 0.75rem; }

.status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.status-online { background: var(--success-color); }
.status-offline { background: var(--text-muted); }

.pond-wrap :deep(.filepond--root) { max-width: 100%; }
.pond-wrap :deep(.filepond--panel-root) { border-radius: var(--border-radius); }
.pond-wrap :deep(.filepond--drop-label) { color: var(--text-muted); }

@media (max-width: 1024px) {
  .grid { grid-template-columns: 1fr; }
  .side { position: static; }
}

@media (max-width: 768px) {
  .hero-content { flex-direction: column; align-items: flex-start; }
  .form-grid { grid-template-columns: 1fr; }
}
</style>

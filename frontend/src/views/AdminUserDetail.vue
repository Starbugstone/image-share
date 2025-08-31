<template>
  <AppLayout>
    <div class="admin-user-detail container" v-if="user">
      <h1>User: {{ user.username }}</h1>
      <div class="tabs">
        <button :class="{ active: activeTab === 'profile' }" @click="activeTab = 'profile'">Profile</button>
        <button :class="{ active: activeTab === 'images' }" @click="activeTab = 'images'">Images</button>
        <button :class="{ active: activeTab === 'albums' }" @click="activeTab = 'albums'">Albums</button>
      </div>

      <div v-if="activeTab === 'profile'" class="tab-content">
        <p><strong>Email:</strong> {{ user.email }}</p>
        <p><strong>Roles:</strong> {{ user.roles?.join(', ') }}</p>
      </div>

      <div v-if="activeTab === 'images'" class="tab-content">
        <ul>
          <li v-for="image in images" :key="image.id">{{ image.title || image.id }}</li>
        </ul>
      </div>

      <div v-if="activeTab === 'albums'" class="tab-content">
        <ul>
          <li v-for="album in albums" :key="album.id">{{ album.title || album.id }}</li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../components/AppLayout.vue'
import adminService from '../services/AdminService.js'

const route = useRoute()
const user = ref(null)
const images = ref([])
const albums = ref([])
const activeTab = ref('profile')

onMounted(async () => {
  try {
    const data = await adminService.getUser(route.params.id)
    user.value = data.user || data
    images.value = data.images || []
    albums.value = data.albums || []
  } catch (e) {
    console.error('Failed to load user details', e)
  }
})
</script>

<style scoped>
.tabs {
  display: flex;
  gap: 1rem;
  margin: 1rem 0;
}
.tabs button {
  padding: 0.5rem 1rem;
  background: var(--bg-secondary);
  border: none;
  cursor: pointer;
}
.tabs button.active {
  background: var(--primary-color);
  color: #fff;
}
.tab-content ul {
  list-style: disc;
  padding-left: 1.5rem;
}
</style>

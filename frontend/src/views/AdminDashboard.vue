<template>
  <AppLayout>
    <div class="admin-dashboard container">
      <h1>Admin Dashboard</h1>
      <table class="admin-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Images</th>
            <th>Albums</th>
            <th>Shared Images</th>
            <th>Shared Albums</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>
              <router-link :to="{ name: 'admin-user-detail', params: { id: user.id } }">
                {{ user.username }}
              </router-link>
            </td>
            <td>{{ user.stats?.total_images || 0 }}</td>
            <td>{{ user.stats?.total_albums || 0 }}</td>
            <td>{{ user.stats?.shared_images || 0 }}</td>
            <td>{{ user.stats?.shared_albums || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import adminService from '../services/AdminService.js'

const users = ref([])

onMounted(async () => {
  try {
    users.value = await adminService.getUsers()
  } catch (e) {
    console.error('Failed to load users', e)
  }
})
</script>

<style scoped>
.admin-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}
.admin-table th,
.admin-table td {
  padding: 0.75rem;
  border: 1px solid var(--border-light);
  text-align: left;
}
.admin-table th {
  background: var(--bg-secondary);
}
</style>

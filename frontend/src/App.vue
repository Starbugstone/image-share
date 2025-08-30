<template>
  <div id="app">
    <router-view />
    <NotificationContainer />
  </div>
</template>

<script setup>
// Main app component - routing is handled by vue-router
import NotificationContainer from './components/NotificationContainer.vue'
</script>

<style>
/* Global styles are in style.css */
</style>

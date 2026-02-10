<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import ChatBot from './components/ChatBot.vue';

const router = useRouter();
const route = useRoute();
const isLoggedIn = ref(false);
const userRole = ref(null);

const checkAuth = () => {
  isLoggedIn.value = !!localStorage.getItem('auth_token');
  const userData = localStorage.getItem('user');
  if (userData) {
    try {
      const user = JSON.parse(userData);
      userRole.value = user.role;
    } catch (e) {
      userRole.value = null;
    }
  } else {
    userRole.value = null;
  }
};

watch(() => route.path, () => checkAuth());
onMounted(checkAuth);

const handleLoginSuccess = () => {
  checkAuth();
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <router-view @login-success="handleLoginSuccess" />

    <div v-if="isLoggedIn && userRole === 'user' && route.path !== '/login'" class="fixed bottom-6 right-6 z-50">
      <ChatBot />
    </div>
  </div>
</template>

<style>
/* Reset for full screen admin layout */
html, body, #app {
  margin: 0;
  padding: 0;
  height: 100%;
  width: 100%;
}
</style>
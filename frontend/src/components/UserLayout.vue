<template>
  <div class="user-layout">
    <header class="top-header">
      <div class="flex items-center space-x-2">
        <div class="logo-box">
          <span class="text-white font-black text-xs">UCC</span>
        </div>
        <h1 class="text-xl font-black tracking-tighter text-gray-900">USER PORTAL</h1>
      </div>

      <div class="flex items-center space-x-4">
        <span class="text-sm font-bold text-gray-400">Welcome, {{ userName }}</span>
        <button @click="handleLogout" class="logout-btn">LOGOUT</button>
      </div>
    </header>

    <div class="content-wrapper">
      <aside class="sidebar">
        <nav class="p-4 space-y-2">

          <router-link to="/dashboard" class="nav-item">
            <span class="icon">🏠</span> Dashboard
          </router-link>

          <router-link to="/eventList" class="nav-item">
            <span class="icon">🌐</span> Events
          </router-link>

          <router-link to="/profile" class="nav-item">
            <span class="icon">👤</span> My Profile
          </router-link>

        </nav>
      </aside>

      <main class="main-content">
        <div class="content-card">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const userName = ref('User');

const handleLogout = async () => {
  const token = localStorage.getItem('auth_token');

  // 1. Megpróbáljuk értesíteni a szervert, ha van token
  if (token) {
    try {
      await fetch('/api/logout', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });
    } catch (err) {
      console.warn("A szerver nem elérhető, vagy a token már lejárt.");
    }
  }

  // 2. Helyi adatok törlése mindenképpen
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');

  // 3. Navigáció a loginra + Teljes oldalfrissítés
  // Ez azért kell, hogy a memóriából is törlődjön minden beragadt hiba/állapot
  window.location.href = '/login';
};

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (user) userName.value = user.name;
});
</script>

<style scoped>
.user-layout { display: flex; flex-direction: column; height: 100vh; width: 100vw; overflow: hidden; background: #f1f5f9; }
.top-header {
  height: 64px; background: white; border-bottom: 1px solid #e2e8f0;
  display: flex; justify-content: space-between; align-items: center; padding: 0 1.5rem; flex-shrink: 0;
}
.logo-box { width: 32px; height: 32px; background: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.content-wrapper { display: flex; flex-grow: 1; overflow: hidden; }
.sidebar { width: 250px; background: white; border-right: 1px solid #e2e8f0; flex-shrink: 0; }
.main-content { flex-grow: 1; padding: 1.5rem; overflow-y: auto; }
.content-card { background: white; border-radius: 16px; min-height: 100%; border: 1px solid #e2e8f0; padding: 2rem; }

.menu-label { font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.5rem 1rem; }
.nav-item {
  display: flex; align-items: center; padding: 0.75rem 1rem; color: #64748b;
  text-decoration: none; font-weight: 700; border-radius: 10px; transition: 0.2s; font-size: 0.9rem;
}
.nav-item:hover { background: #f8fafc; color: #2563eb; }
.router-link-active { background: #eff6ff; color: #2563eb; }
.icon { margin-right: 12px; font-size: 1.1rem; }
.divider { height: 1px; background: #f1f5f9; margin: 1rem 0; }
.logout-btn { background: #f1f5f9; color: #475569; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.7rem; font-weight: 800; border: 1px solid #e2e8f0; }
.logout-btn:hover { background: #fee2e2; color: #dc2626; }
</style>
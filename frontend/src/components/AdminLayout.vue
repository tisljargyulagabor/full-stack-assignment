<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

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

</script>

<template>
  <div class="admin-layout">
    <header class="top-header">
      <div class="flex items-center space-x-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-black text-xs">UCC</span>
        </div>
        <h1 class="text-xl font-black tracking-tighter text-gray-900">PROJECT ADMIN</h1>
      </div>

      <div class="flex items-center space-x-4">
        <button @click="handleLogout" class="logout-btn">
          LOGOUT
        </button>
      </div>
    </header>

    <div class="content-wrapper">
      <aside class="sidebar">
        <nav class="p-4 space-y-2">
          <router-link to="/admin/dashboard" class="nav-item">
            <span class="icon">🏠</span> Dashboard
          </router-link>

          <router-link to="/admin/eventList" class="nav-item">
            <span class="icon">📅</span> Event Management
          </router-link>

          <router-link to="/admin/users" class="nav-item">
            <span class="icon">👥</span> User Management
          </router-link>

          <router-link to="/admin/chat" class="nav-item">
            <span class="icon">💬</span> Help Desk
          </router-link>

          <router-link to="/admin/profile" class="nav-item">
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

<style scoped>
.admin-layout {
  display: flex;
  flex-direction: column;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
}

/* HEADER */
.top-header {
  height: 64px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5rem;
  z-index: 50;
  flex-shrink: 0;
}

.logout-btn {
  background: #f1f5f9;
  color: #475569;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 800;
  transition: 0.2s;
  border: 1px solid #e2e8f0;
}
.logout-btn:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

/* MIDDLE SECTION */
.content-wrapper {
  display: flex;
  flex-grow: 1;
  overflow: hidden;
}

/* SIDEBAR */
.sidebar {
  width: 250px;
  background: #f8fafc;
  border-right: 1px solid #e2e8f0;
  flex-shrink: 0;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #64748b;
  text-decoration: none;
  font-weight: 700;
  border-radius: 10px;
  transition: 0.2s;
  font-size: 0.9rem;
}

.nav-item:hover { background: #f1f5f9; color: #2563eb; }
.nav-item.router-link-active {
  background: #eff6ff;
  color: #2563eb;
  box-shadow: inset 4px 0 0 -2px #2563eb;
}

.icon { margin-right: 12px; font-size: 1.1rem; }
.divider { height: 1px; background: #e2e8f0; margin: 1rem 0; }

/* CONTENT */
.main-content {
  flex-grow: 1;
  background: #f1f5f9;
  padding: 1.5rem;
  overflow-y: auto;
}

.content-card {
  background: white;
  border-radius: 16px;
  min-height: 100%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #e2e8f0;
}
</style>
<script setup>
</script>
<template>
  <div class="p-8">
    <div class="welcome-box">
      <div class="max-w-2xl text-center">
        <h2 class="text-3xl font-black text-white mb-4 tracking-tight">Welcome to the UCC Administration Surface!</h2>
        <p class="text-blue-100 text-lg leading-relaxed">
          Here you can manage incoming AI chats, oversee users, and edit event data.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
      <div class="stat-card">
        <span class="text-4xl mb-4 block">🤖</span>
        <h3 class="text-gray-500 font-bold uppercase text-xs tracking-widest">AI Responses</h3>
        <p class="text-3xl font-black text-gray-900 mt-2">Active</p>
      </div>

      <div class="stat-card border-blue-100 bg-blue-50/30">
        <span class="text-4xl mb-4 block">👥</span>
        <h3 class="text-blue-500 font-bold uppercase text-xs tracking-widest">Users</h3>
        <p class="text-3xl font-black text-blue-900 mt-2">Ready to manage</p>
      </div>

      <div class="stat-card">
        <span class="text-4xl mb-4 block">🛡️</span>
        <h3 class="text-gray-500 font-bold uppercase text-xs tracking-widest">System Status</h3>
        <p class="text-3xl font-black text-green-600 mt-2">Online</p>
      </div>

      <div class="stat-card">
        <span class="text-4xl mb-4 block">👤</span>
        <h3 class="text-gray-500 font-bold uppercase text-xs tracking-widest">Account</h3>
        <p class="text-3xl font-black text-purple-600 mt-2 mb-4">Edit Profile</p>
        <router-link to="/profile" class="card-link text-purple-600">Settings →</router-link>
      </div>

    </div>
  </div>
</template>

<style scoped>
.welcome-box {
  background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
  padding: 3rem;
  border-radius: 24px;
  box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.2);

  /* Flexbox to center content horizontally and vertically */
  display: flex;
  justify-content: center;
  align-items: center;
}

.stat-card {
  background: white;
  padding: 2rem;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  transition: 0.3s transform;
}

.stat-card:hover {
  transform: translateY(-5px);
}
</style>
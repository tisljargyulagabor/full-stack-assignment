<template>
  <div class="max-w-4xl mx-auto p-6 text-gray-800">
    <div class="flex items-center justify-between mb-8 border-b-2 border-gray-100 pb-6">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Users</h1>
        <p class="text-sm text-gray-500 mt-1">Manage system users and permissions</p>
      </div>
      <div class="flex space-x-3">
        <button
            @click="fetchUsers"
            class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4"
        >
          Refresh
        </button>
        <button
            @click="showCreateForm = !showCreateForm"
            class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-bold text-sm px-6 shadow-lg shadow-blue-100"
        >
          {{ showCreateForm ? 'Close' : '+ New User' }}
        </button>
      </div>
    </div>

    <div v-if="showCreateForm" class="mb-10 bg-white border-2 border-blue-500 rounded-2xl p-8 shadow-xl">
      <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
        <span class="bg-blue-500 w-2 h-6 rounded-full mr-3"></span>
        Register new user
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Name</label>
          <input v-model="newUser.name" type="text" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Full name">
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Email address</label>
          <input v-model="newUser.email" type="email" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="example@email.com">
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Password</label>
          <input v-model="newUser.password" type="password" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Min. 12 characters">
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Role</label>
          <select v-model="newUser.role" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all">
            <option value="user">USER (General user)</option>
            <option value="admin">ADMIN (Administrator)</option>
          </select>
        </div>

        <div v-if="newUser.password.length > 0" class="md:col-span-2 mt-2 p-4 bg-gray-50 rounded-xl border border-gray-200 animate-in fade-in duration-300">
          <p class="text-[10px] font-bold text-gray-400 uppercase mb-3 tracking-widest">Security Requirements:</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
            <div :class="passwordValidation.length ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium">
              <span class="mr-1.5">{{ passwordValidation.length ? '✓' : '○' }}</span> Min. 12 characters
            </div>
            <div :class="passwordValidation.hasUpper ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium">
              <span class="mr-1.5">{{ passwordValidation.hasUpper ? '✓' : '○' }}</span> Uppercase letter (A-Z)
            </div>
            <div :class="passwordValidation.hasLower ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium">
              <span class="mr-1.5">{{ passwordValidation.hasLower ? '✓' : '○' }}</span> Lowercase letter (a-z)
            </div>
            <div :class="passwordValidation.hasNumber ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium">
              <span class="mr-1.5">{{ passwordValidation.hasNumber ? '✓' : '○' }}</span> Number (0-9)
            </div>
            <div :class="passwordValidation.hasSymbol ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium">
              <span class="mr-1.5">{{ passwordValidation.hasSymbol ? '✓' : '○' }}</span> Special character (!@#$)
            </div>
          </div>
        </div>
      </div>

      <button
          @click="createUser"
          :disabled="!isNewUserPasswordValid || !newUser.name || !newUser.email"
          class="mt-8 w-full py-4 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all uppercase tracking-wider disabled:opacity-50 disabled:bg-gray-400 disabled:shadow-none"
      >
        Create user
      </button>
    </div>

    <div v-if="loading" class="p-20 text-center">
      <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mb-4"></div>
      <p class="text-gray-400 font-medium">Loading data...</p>
    </div>

    <div v-else class="grid gap-6">
      <div v-for="user in users" :key="user.id" class="group">

        <div v-if="editingUser && editingUser.id === user.id" class="bg-white border-2 border-blue-500 rounded-2xl p-8 shadow-xl mb-4">
          <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="bg-blue-500 w-2 h-6 rounded-full mr-3"></span>
            Edit user
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-bold text-gray-700 ml-1">Name</label>
              <input v-model="editingUser.name" type="text" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all">
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-bold text-gray-700 ml-1">Email address</label>
              <input v-model="editingUser.email" type="email" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all">
            </div>
          </div>

          <div class="mt-6 space-y-2">
            <label class="block text-sm font-bold text-gray-700 ml-1">Role</label>
            <select v-model="editingUser.role" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all">
              <option value="user">USER (General user)</option>
              <option value="admin">ADMIN (Administrator)</option>
            </select>
          </div>

          <div class="flex justify-end space-x-4 mt-8">
            <button @click="editingUser = null" class="p-3 bg-gray-50 text-red-400 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all border border-transparent hover:border-red-100 font-bold text-sm px-4">
              Cancel
            </button>
            <button @click="updateUser" class="px-8 py-4 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all uppercase tracking-wider">
              Save
            </button>
          </div>
        </div>

        <div v-else class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between hover:border-blue-300 transition-all shadow-sm">
          <div class="flex items-center space-x-4">
            <div :class="user.role === 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600'" class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-xl">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div class="space-y-1">
              <div class="flex items-center space-x-2">
                <h2 class="text-lg font-bold text-gray-900">{{ user.name }}</h2>
                <span :class="user.role === 'admin' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-gray-50 text-gray-600 border-gray-100'"
                      class="px-2 py-0.5 rounded text-[10px] font-black uppercase border tracking-widest">
                  {{ user.role }}
                </span>
              </div>
              <p class="text-gray-500 text-sm flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ user.email }}
              </p>
            </div>
          </div>

          <div class="flex items-center space-x-2 mt-6 md:mt-0">
            <button @click="startEdit(user)" class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4">
              Edit
            </button>
            <button @click="confirmDelete(user.id)" class="p-3 bg-gray-50 text-red-400 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all border border-transparent hover:border-red-100 font-bold text-sm px-4">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.show" class="fixed bottom-10 right-10 z-[100] flex items-center bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl border border-gray-700">
          <span class="mr-3 text-xl">{{ toast.type === 'success' ? '✅' : '⚠️' }}</span>
          <p class="font-bold tracking-wide">{{ toast.message }}</p>
        </div>
      </Transition>
    </Teleport>

    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden transform transition-all border border-gray-100">
            <div class="p-8 text-center">
              <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6 text-red-600">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </div>
              <h3 class="text-2xl font-black text-gray-900 mb-2 uppercase tracking-tighter">Delete User?</h3>
              <p class="text-gray-500 font-medium px-4">Are you sure you want to remove this user? This action cannot be undone.</p>
            </div>
            <div class="bg-gray-50 px-8 py-6 flex flex-col sm:flex-row-reverse gap-3">
              <button @click="handleDelete" class="flex-1 py-3 bg-red-600 text-white font-black rounded-xl hover:bg-red-700 transition-all uppercase text-sm tracking-widest shadow-lg shadow-red-200">Yes, Delete</button>
              <button @click="showDeleteModal = false" class="flex-1 py-3 bg-white text-gray-400 font-bold rounded-xl border border-gray-200 hover:bg-gray-100 transition-all uppercase text-sm tracking-widest">Cancel</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';

const users = ref([]);
const loading = ref(true);
const editingUser = ref(null);
const showCreateForm = ref(false);
const newUser = ref({ name: '', email: '', password: '', role: 'user' });

const showDeleteModal = ref(false);
const userIdToDelete = ref(null);
const toast = reactive({ show: false, message: '', type: 'success' });

// --- Password Validation Logic ---
const passwordValidation = computed(() => {
  if (!newUser.value.password) return null;
  return {
    length: newUser.value.password.length >= 12,
    hasUpper: /[A-Z]/.test(newUser.value.password),
    hasLower: /[a-z]/.test(newUser.value.password),
    hasNumber: /[0-9]/.test(newUser.value.password),
    hasSymbol: /[!@#$%^&*(),.?":{}|<>]/.test(newUser.value.password)
  };
});

const isNewUserPasswordValid = computed(() => {
  const v = passwordValidation.value;
  return v && v.length && v.hasUpper && v.hasLower && v.hasNumber && v.hasSymbol;
});

const showNotification = (msg, type = 'success') => {
  toast.message = msg;
  toast.type = type;
  toast.show = true;
  setTimeout(() => toast.show = false, 3500);
};

const fetchUsers = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/admin/users', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });

    if (res.ok) {
      users.value = await res.json();
    }
  } catch (err) { console.error(err); } finally { loading.value = false; }
};

const createUser = async () => {
  if (!newUser.value.name || !newUser.value.email || !isNewUserPasswordValid.value) {
    showNotification("Security requirements not met!", "error");
    return;
  }
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/admin/users', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(newUser.value)
    });

    if (res.ok) {
      const data = await res.json();
      users.value.unshift(data.user);
      newUser.value = { name: '', email: '', password: '', role: 'user' };
      showCreateForm.value = false;
      showNotification("User created successfully!");
    } else {
      const errorData = await res.json();
      showNotification(errorData.message || 'Creation failed', "error");
    }
  } catch (err) { console.error(err); }
};

const startEdit = (user) => {
  editingUser.value = { ...user };
};

const updateUser = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`/api/admin/users/${editingUser.value.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(editingUser.value)
    });

    if (res.ok) {
      const data = await res.json();
      const index = users.value.findIndex(u => u.id === editingUser.value.id);
      users.value[index] = data.user;
      editingUser.value = null;
      showNotification("User updated successfully!");
    }
  } catch (err) { console.error(err); }
};

const confirmDelete = (id) => {
  userIdToDelete.value = id;
  showDeleteModal.value = true;
};

const handleDelete = async () => {
  showDeleteModal.value = false;
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`/api/admin/users/${userIdToDelete.value}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) {
      users.value = users.value.filter(u => u.id !== userIdToDelete.value);
      showNotification("User deleted.");
    }
  } catch (err) { console.error(err); }
};

onMounted(fetchUsers);
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { opacity: 0; transform: translateY(20px) scale(0.9); }
.toast-leave-to { opacity: 0; transform: translateX(50px); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
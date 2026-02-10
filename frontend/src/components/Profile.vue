<template>
  <div class="max-w-4xl mx-auto p-6 text-gray-800">
    <div class="flex items-center justify-between mb-8 border-b-2 border-gray-100 pb-6">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Profile Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage personal information and security</p>
      </div>
    </div>

    <div class="bg-white border-2 border-blue-500 rounded-2xl p-8 shadow-xl">
      <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
        <span class="bg-blue-500 w-2 h-6 rounded-full mr-3"></span>
        Update Personal Information
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Full Name</label>
          <input v-model="user.name" type="text" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all text-gray-900">
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Email Address</label>
          <input v-model="user.email" type="email" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all text-gray-900">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">New Password (optional)</label>
          <input v-model="password" type="password" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all text-gray-900" placeholder="••••••••••••">
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Confirm New Password</label>
          <input v-model="password_confirmation" type="password" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all text-gray-900" placeholder="••••••••••••">
        </div>
      </div>

      <div v-if="password.length > 0" class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 animate-in fade-in duration-300">
        <p class="text-xs font-bold text-gray-500 uppercase mb-3 tracking-widest">Security Requirements:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-4">
          <div :class="passwordValidation.length ? 'text-green-600' : 'text-gray-400'" class="text-xs flex items-center transition-colors font-medium">
            <span class="mr-2 text-lg">{{ passwordValidation.length ? '✓' : '○' }}</span> Min. 12 characters
          </div>
          <div :class="passwordValidation.hasUpper ? 'text-green-600' : 'text-gray-400'" class="text-xs flex items-center transition-colors font-medium">
            <span class="mr-2 text-lg">{{ passwordValidation.hasUpper ? '✓' : '○' }}</span> Uppercase letter (A-Z)
          </div>
          <div :class="passwordValidation.hasLower ? 'text-green-600' : 'text-gray-400'" class="text-xs flex items-center transition-colors font-medium">
            <span class="mr-2 text-lg">{{ passwordValidation.hasLower ? '✓' : '○' }}</span> Lowercase letter (a-z)
          </div>
          <div :class="passwordValidation.hasNumber ? 'text-green-600' : 'text-gray-400'" class="text-xs flex items-center transition-colors font-medium">
            <span class="mr-2 text-lg">{{ passwordValidation.hasNumber ? '✓' : '○' }}</span> Number (0-9)
          </div>
          <div :class="passwordValidation.hasSymbol ? 'text-green-600' : 'text-gray-400'" class="text-xs flex items-center transition-colors font-medium">
            <span class="mr-2 text-lg">{{ passwordValidation.hasSymbol ? '✓' : '○' }}</span> Special character (!@#$)
          </div>
          <div :class="passwordValidation.match ? 'text-green-600' : 'text-red-500'" class="text-xs flex items-center transition-colors font-bold">
            <span class="mr-2 text-lg">{{ passwordValidation.match ? '✓' : '⚠️' }}</span> Passwords match
          </div>
        </div>
      </div>

      <div class="mt-10 pt-8 border-t-2 border-gray-100">
        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          Google Authenticator (MFA)
        </h3>

        <div v-if="!user.two_factor_enabled && !mfaSetupData" class="bg-gray-50 p-6 rounded-2xl border-2 border-gray-100">
          <p class="text-sm text-gray-600 mb-4 font-medium">Enhance your account security! Activate Google Authenticator to require a code when logging in.</p>
          <button @click="initiateMFASetup" class="px-6 py-2 bg-gray-900 text-white font-bold rounded-xl hover:bg-black transition-all text-sm">Activate MFA</button>
        </div>

        <div v-if="mfaSetupData" class="bg-blue-50 p-6 rounded-2xl border-2 border-blue-200 animate-in fade-in slide-in-from-top-4">
          <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="bg-white p-3 rounded-xl shadow-md">
              <qrcode-vue :value="mfaSetupData.qr_code" :size="160" level="H" render-as="svg" />
            </div>
            <div class="flex-1 space-y-4 text-center md:text-left">
              <p class="text-sm font-bold text-blue-900">1. Scan the QR code with the Google Authenticator app!</p>
              <p class="text-sm text-blue-700">2. Enter the 6-digit code shown in the app to confirm:</p>

              <div class="flex flex-col space-y-2">
                <input v-model="mfaVerificationCode" type="text" placeholder="000 000" maxlength="6" class="w-full md:w-48 text-center text-2xl font-black tracking-widest bg-white border-2 border-blue-300 p-3 rounded-xl outline-none focus:border-blue-500">
                <div class="flex space-x-2">
                  <button @click="verifyAndEnableMFA" class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md">Confirm</button>
                  <button @click="mfaSetupData = null" class="px-4 py-3 bg-white text-gray-500 font-bold rounded-xl border border-gray-200">Cancel</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="user.two_factor_enabled" class="bg-green-50 p-6 rounded-2xl border-2 border-green-200 flex items-center justify-between">
          <div class="flex items-center">
            <div class="bg-green-500 p-2 rounded-full mr-4 text-white">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <div>
              <p class="font-bold text-green-900 tracking-tight">Two-factor authentication is active</p>
              <p class="text-xs text-green-700 font-medium tracking-tight uppercase">Your account is currently protected.</p>
            </div>
          </div>
          <button @click="showMfaModal = true" class="bg-black text-white px-4 py-2 rounded-lg font-bold hover:bg-red-600 transition-all text-sm group">
            <span class="text-red-500 group-hover:text-white">Deactivate</span>
          </button>
        </div>
      </div>

      <button
          @click="saveProfile"
          :disabled="loading || !isPasswordValid"
          class="mt-8 w-full py-4 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all uppercase tracking-wider flex justify-center items-center disabled:opacity-50 disabled:bg-gray-400 disabled:shadow-none"
      >
        <span v-if="loading" class="animate-spin inline-block w-5 h-5 border-3 border-white border-t-transparent rounded-full mr-2"></span>
        {{ loading ? 'Processing...' : 'Save Profile Changes' }}
      </button>
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
        <div v-if="showMfaModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden transform transition-all border border-gray-100">
            <div class="p-8 text-center">
              <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6 text-red-600">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <h3 class="text-2xl font-black text-gray-900 mb-2 uppercase tracking-tighter">Disable Security?</h3>
              <p class="text-gray-500 font-medium">Are you sure you want to disable MFA? This will significantly decrease your account security.</p>
            </div>
            <div class="bg-gray-50 px-8 py-6 flex flex-col sm:flex-row-reverse gap-3">
              <button @click="handleConfirmDisable" class="flex-1 py-3 bg-red-600 text-white font-black rounded-xl hover:bg-red-700 transition-all uppercase text-sm tracking-widest shadow-lg shadow-red-200">Yes, Disable</button>
              <button @click="showMfaModal = false" class="flex-1 py-3 bg-white text-gray-400 font-bold rounded-xl border border-gray-200 hover:bg-gray-100 transition-all uppercase text-sm tracking-widest">Cancel</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import QrcodeVue from 'qrcode.vue';

// --- State ---
const user = ref({ name: '', email: '', two_factor_enabled: false });
const password = ref('');
const password_confirmation = ref('');
const loading = ref(false);
const showMfaModal = ref(false);
const toast = reactive({ show: false, message: '', type: 'success' });
const mfaSetupData = ref(null);
const mfaVerificationCode = ref('');

// --- Password Validation Logic ---
const passwordValidation = computed(() => {
  if (!password.value) return null;
  return {
    length: password.value.length >= 12,
    hasUpper: /[A-Z]/.test(password.value),
    hasLower: /[a-z]/.test(password.value),
    hasNumber: /[0-9]/.test(password.value),
    hasSymbol: /[!@#$%^&*(),.?":{}|<>]/.test(password.value),
    match: password.value === password_confirmation.value
  };
});

const isPasswordValid = computed(() => {
  // Profilnál ha üres a jelszó, az érvényes (nem akarja módosítani)
  if (!password.value && !password_confirmation.value) return true;

  const v = passwordValidation.value;
  if (!v) return true;

  return v.length && v.hasUpper && v.hasLower && v.hasNumber && v.hasSymbol && v.match;
});

// --- Actions ---
onMounted(() => {
  const storedUser = JSON.parse(localStorage.getItem('user'));
  if (storedUser) user.value = { ...storedUser };
});

const showNotification = (msg, type = 'success') => {
  toast.message = msg;
  toast.type = type;
  toast.show = true;
  setTimeout(() => toast.show = false, 3500);
};

const initiateMFASetup = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/user/mfa/setup', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) mfaSetupData.value = await res.json();
  } catch (err) {
    showNotification("Error initiating MFA setup.", "error");
  }
};

const verifyAndEnableMFA = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/user/mfa/verify', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({
        code: mfaVerificationCode.value,
        secret: mfaSetupData.value.secret
      })
    });

    if (res.ok) {
      const data = await res.json();
      user.value = data.user;
      localStorage.setItem('user', JSON.stringify(data.user));
      mfaSetupData.value = null;
      mfaVerificationCode.value = '';
      showNotification("Two-factor authentication is now active!");
    } else {
      showNotification("Invalid verification code!", "error");
    }
  } catch (err) {
    showNotification("Verification failed.", "error");
  }
};

const handleConfirmDisable = async () => {
  showMfaModal.value = false;
  await disableMFA();
};

const disableMFA = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/user/mfa/disable', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });

    const data = await res.json();
    if (res.ok) {
      user.value = data.user;
      localStorage.setItem('user', JSON.stringify(data.user));
      showNotification("Two-factor authentication disabled.", "success");
    }
  } catch (err) {
    showNotification("Could not reach server.", "error");
  }
};

const saveProfile = async () => {
  if (!user.value.name || !user.value.email) {
    showNotification("Name and email are required!", "error");
    return;
  }

  loading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/user/profile', {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: user.value.name,
        email: user.value.email,
        password: password.value || null,
        password_confirmation: password_confirmation.value || null
      })
    });

    if (res.ok) {
      const data = await res.json();
      localStorage.setItem('user', JSON.stringify(data.user));
      showNotification("Profile updated successfully!");
      password.value = '';
      password_confirmation.value = '';
    } else {
      const errData = await res.json();
      showNotification(errData.message || "Failed to update profile.", "error");
    }
  } catch (err) {
    showNotification("Server error while updating.", "error");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { opacity: 0; transform: translateY(20px) scale(0.9); }
.toast-leave-to { opacity: 0; transform: translateX(50px); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
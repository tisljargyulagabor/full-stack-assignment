<template>
  <div class="fixed inset-0 w-full min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-blue-100 p-6 font-sans z-50">
    <div class="max-w-md w-full bg-white/80 backdrop-blur-md border border-white rounded-3xl p-10 shadow-[0_20px_50px_rgba(8,_112,_184,_0.1)]">

      <div v-if="showForgotPassword" class="space-y-6">
        <div class="text-center">
          <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Password Reset</h2>
          <p class="text-sm text-gray-500 mt-2">We'll send you a link to reset your password.</p>
        </div>

        <div v-if="!resetSent" class="space-y-4">
          <input v-model="resetEmail" type="email"
                 class="w-full bg-gray-50 border border-gray-200 p-4 rounded-2xl text-gray-900 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                 placeholder="email@example.com">
          <button @click="handleForgotPassword" :disabled="loading"
                  class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">
            {{ loading ? 'Sending...' : 'Send reset link' }}
          </button>
        </div>
        <div v-else class="bg-emerald-50 p-6 rounded-2xl text-emerald-800 text-center font-semibold border border-emerald-100">
          ✅ Email sent! Please check your inbox.
        </div>

        <button @click="showForgotPassword = false; resetSent = false; error = null"
                class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4">
          Back to login
        </button>
      </div>

      <div v-else-if="showMfaInput" class="space-y-6">
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
          <h2 class="text-3xl font-extrabold text-gray-900">MFA Code</h2>
          <p class="text-sm text-gray-500 mt-2 font-medium">Enter the 6-digit code from your app:</p>
        </div>
        <input v-model="mfaCode" type="text" maxlength="6"
               class="w-full text-center text-4xl font-black tracking-[0.5em] bg-gray-50 border border-gray-200 p-5 rounded-2xl text-blue-900 outline-none focus:border-blue-500 transition-all"
               placeholder="000000">
        <button @click="handleMfaVerify" :disabled="loading || mfaCode.length < 6"
                class="w-full py-4 bg-gray-900 hover:bg-black text-white font-bold rounded-2xl shadow-xl transition-all active:scale-[0.98]">
          Confirm Login
        </button>
        <p v-if="error" class="text-xs text-red-500 font-bold text-center mt-2 animate-bounce">
          {{ error }}
        </p>
      </div>

      <div v-else class="space-y-6">
        <div class="text-center mb-8">
          <h2 class="text-4xl font-black text-gray-900 tracking-tight">Welcome Back</h2>
          <p class="text-gray-500 font-medium mt-1">Please sign in to your account</p>
        </div>

        <div class="space-y-4 text-left">
          <div>
            <label class="block text-xs font-bold text-gray-400 mb-2 ml-1 uppercase tracking-widest">Email Address</label>
            <input v-model="email" type="email"
                   class="w-full bg-gray-50 border border-gray-200 p-4 rounded-2xl text-gray-900 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                   placeholder="name@example.com">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-400 mb-2 ml-1 uppercase tracking-widest">Password</label>
            <input v-model="password" type="password"
                   class="w-full bg-gray-50 border border-gray-200 p-4 rounded-2xl text-gray-900 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                   placeholder="••••••••">
            <div class="flex justify-end mt-3">
              <button @click="showForgotPassword = true; error = null" class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4">Forgot password?</button>
            </div>
          </div>
        </div>

        <button @click="handleLogin" :disabled="loading"
                class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>

        <p v-if="error" class="text-xs text-red-500 font-bold text-center mt-2 animate-bounce italic">{{ error }}</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref(null);
const loading = ref(false);

const showMfaInput = ref(false);
const showForgotPassword = ref(false);
const resetEmail = ref('');
const resetSent = ref(false);
const mfaCode = ref('');
const tempUserId = ref(null);

const emit = defineEmits(['login-success']);

const handleLogin = async () => {
  error.value = null;
  loading.value = true;
  try {
    const res = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    });
    const data = await res.json();

    if (!res.ok) throw new Error(data.message || 'Wrong Email or Password');

    if (data.mfa_required) {
      tempUserId.value = data.user_id;
      showMfaInput.value = true;
    } else {
      finalizeLogin(data);
    }
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const handleMfaVerify = async () => {
  error.value = null;
  loading.value = true;
  try {
    const res = await fetch('/api/login/mfa', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ user_id: tempUserId.value, code: mfaCode.value })
    });

    const data = await res.json();

    if (res.status === 429) {
      alert(data.message || 'Too many attempts. Redirecting...');
      showMfaInput.value = false;
      mfaCode.value = '';
      error.value = null;
      return;
    }

    if (!res.ok) {
      throw new Error(data.message || 'Wrong MFA Code');
    }

    finalizeLogin(data);
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const handleForgotPassword = async () => {
  if (!resetEmail.value) return;
  error.value = null;
  loading.value = true;
  try {
    const res = await fetch('/api/forgot-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: resetEmail.value })
    });
    const data = await res.json();
    if (res.ok) {
      resetSent.value = true;
    } else {
      throw new Error(data.message || 'Error occurred during sending.');
    }
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const finalizeLogin = (data) => {
  localStorage.setItem('auth_token', data.token);
  localStorage.setItem('user', JSON.stringify(data.user));
  emit('login-success');
  router.push(data.user.role === 'admin' ? '/admin/dashboard' : '/dashboard');
};
</script>
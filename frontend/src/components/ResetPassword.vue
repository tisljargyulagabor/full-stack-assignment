<template>
  <div class="fixed inset-0 w-full min-h-screen flex items-center justify-center bg-gradient-to-tr from-blue-50 to-indigo-100 p-6 font-sans z-50">
    <div class="max-w-md w-full bg-white border border-white rounded-3xl p-10 shadow-[0_20px_50px_rgba(0,0,0,0.05)]">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Set New Password</h2>
        <p class="text-sm text-gray-500 mt-2">Create a secure password for your account.</p>
      </div>

      <div v-if="!success" class="space-y-5">
        <div class="bg-blue-50 p-3 rounded-xl flex items-center justify-center">
          <span class="text-xs font-bold text-blue-600 uppercase tracking-wider text-center">Account: {{ email }}</span>
        </div>

        <div class="space-y-4">
          <input v-model="password" type="password" placeholder="New password"
                 class="w-full bg-gray-50 border border-gray-200 p-4 rounded-2xl text-gray-900 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">

          <div v-if="password.length > 0" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 animate-in fade-in duration-300">
            <p class="text-[10px] font-bold text-gray-400 uppercase mb-3 tracking-widest">Security Requirements:</p>
            <div class="grid grid-cols-2 gap-y-2 gap-x-1">
              <div :class="passwordValidation.length ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium transition-colors">
                <span class="mr-1.5">{{ passwordValidation.length ? '✓' : '○' }}</span> Min. 12 characters
              </div>
              <div :class="passwordValidation.hasUpper ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium transition-colors">
                <span class="mr-1.5">{{ passwordValidation.hasUpper ? '✓' : '○' }}</span> Uppercase letter (A-Z)
              </div>
              <div :class="passwordValidation.hasLower ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium transition-colors">
                <span class="mr-1.5">{{ passwordValidation.hasLower ? '✓' : '○' }}</span> Lowercase letter (a-z)
              </div>
              <div :class="passwordValidation.hasNumber ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium transition-colors">
                <span class="mr-1.5">{{ passwordValidation.hasNumber ? '✓' : '○' }}</span> Number (0-9)
              </div>
              <div :class="passwordValidation.hasSymbol ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium transition-colors">
                <span class="mr-1.5">{{ passwordValidation.hasSymbol ? '✓' : '○' }}</span> Special character (!@#$)
              </div>
              <div :class="passwordsMatch ? 'text-green-600' : 'text-gray-400'" class="text-[11px] flex items-center font-medium transition-colors">
                <span class="mr-1.5">{{ passwordsMatch ? '✓' : '○' }}</span> Passwords match
              </div>
            </div>
          </div>

          <input v-model="password_confirmation" type="password" placeholder="Confirm new password"
                 class="w-full bg-gray-50 border border-gray-200 p-4 rounded-2xl text-gray-900 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
        </div>

        <button @click="handleReset"
                :disabled="loading || !token || !isFormValid"
                class="w-full p-4 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 transition-all font-bold text-sm shadow-lg shadow-blue-100 disabled:bg-gray-300 disabled:shadow-none disabled:cursor-not-allowed disabled:opacity-70">
          {{ loading ? 'Saving...' : 'Update Password' }}
        </button>
      </div>

      <div v-else class="space-y-4 text-center">
        <div class="mx-auto w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900">Success!</h3>
        <p class="text-emerald-700 font-medium">Your password has been changed. Redirecting to login...</p>
      </div>

      <p v-if="error" class="mt-6 p-4 bg-red-50 text-red-700 text-sm rounded-2xl text-center font-bold border border-red-100">
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const email = ref('');
const token = ref('');
const password = ref('');
const password_confirmation = ref('');
const loading = ref(false);
const error = ref(null);
const success = ref(false);

onMounted(() => {
  token.value = route.query.token || '';
  email.value = route.query.email || '';

  if (!token.value || !email.value) {
    error.value = "Missing reset token or email address. Please request a new link!";
  }
});

// --- Validation Logic ---
const passwordValidation = computed(() => {
  return {
    length: password.value.length >= 12,
    hasUpper: /[A-Z]/.test(password.value),
    hasLower: /[a-z]/.test(password.value),
    hasNumber: /[0-9]/.test(password.value),
    hasSymbol: /[!@#$%^&*(),.?":{}|<>]/.test(password.value)
  };
});

const passwordsMatch = computed(() => {
  return password.value === password_confirmation.value && password.value !== '';
});

// Strictly checks if all criteria + match are met
const isFormValid = computed(() => {
  const v = passwordValidation.value;
  return v.length &&
      v.hasUpper &&
      v.hasLower &&
      v.hasNumber &&
      v.hasSymbol &&
      passwordsMatch.value;
});

const handleReset = async () => {
  if (!isFormValid.value) return;

  error.value = null;
  loading.value = true;

  try {
    const res = await fetch('/api/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        token: token.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value
      })
    });

    const data = await res.json();

    if (!res.ok) {
      throw new Error(data.message || 'An error occurred during reset.');
    }

    success.value = true;
    setTimeout(() => router.push('/login'), 3000);
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};
</script>
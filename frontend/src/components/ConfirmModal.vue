<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">

        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all animate-in zoom-in duration-300">

          <div class="p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
              <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>

            <h3 class="text-2xl font-black text-gray-900 mb-2 uppercase tracking-tight">
              {{ title || 'Confirm Deletion' }}
            </h3>
            <p class="text-gray-500 font-medium">
              {{ message || 'Are you sure you want to proceed? This action cannot be undone.' }}
            </p>
          </div>

          <div class="bg-gray-50 px-8 py-6 flex flex-col sm:flex-row-reverse gap-3">
            <button
                @click="$emit('confirm')"
                class="w-full sm:w-auto px-6 py-3 bg-red-600 text-white font-black rounded-xl hover:bg-red-700 transition-all shadow-lg shadow-red-200 uppercase text-sm tracking-wide"
            >
              Confirm
            </button>
            <button
                @click="$emit('close')"
                class="w-full sm:w-auto px-6 py-3 bg-white text-gray-500 font-black rounded-xl border-2 border-gray-200 hover:bg-gray-100 transition-all uppercase text-sm tracking-wide"
            >
              Cancel
            </button>
          </div>
        </div>

      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps(['show', 'title', 'message']);
defineEmits(['close', 'confirm']);
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
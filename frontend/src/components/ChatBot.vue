<template>
  <div class="fixed bottom-6 right-6 z-50 font-sans">
    <button v-if="!isOpen" @click="toggleChat" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-2xl transition-all transform hover:scale-110">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
      </svg>
    </button>

    <div v-if="isOpen" class="flex flex-col h-[550px] w-[350px] md:w-[400px] border border-gray-300 rounded-2xl bg-white shadow-2xl overflow-hidden">
      <div class="p-4 bg-blue-600 text-white flex justify-between items-center shadow-md">
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
          <span class="font-bold">UCC AI Assistant</span>
        </div>
        <button @click="toggleChat" class="text-white hover:bg-blue-500 rounded p-1">✕</button>
      </div>

      <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50" ref="chatWindow">
        <div v-for="(msg, index) in messages" :key="index" :class="msg.role === 'user' ? 'text-right' : 'text-left'">
          <div v-if="msg.role === 'admin'" class="text-[10px] text-blue-600 font-bold mb-1 ml-1 uppercase">Ügyintéző</div>
          <div
              :class="[
                msg.role === 'user' ? 'bg-blue-600 text-white rounded-tr-none' : '',
                msg.role === 'model' ? 'bg-white border border-gray-200 text-gray-800 rounded-tl-none' : '',
                msg.role === 'admin' ? 'bg-blue-50 border-2 border-blue-200 text-blue-900 rounded-tl-none shadow-sm' : ''
              ]"
              class="inline-block px-4 py-2 rounded-2xl max-w-[85%] text-sm shadow-sm break-words"
          >
            {{ msg.content }}
          </div>
        </div>

        <div v-if="isRequestPending || isAdminWaiting" class="text-left">
          <div class="inline-block bg-white border border-gray-200 px-4 py-2 rounded-2xl text-xs shadow-sm">
            <span v-if="isAdminWaiting" class="flex items-center gap-2 text-gray-500">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
              </span>
              Waiting for our agent's reply...
            </span>

            <span v-else-if="isRequestPending" class="text-gray-400 italic animate-pulse">
              AI is thinking...
            </span>
          </div>
        </div>
      </div>

      <div class="p-4 bg-white border-t border-gray-200">
        <div class="flex items-center gap-2 bg-gray-100 border border-gray-300 rounded-full px-4 py-1">
          <input
              v-model="newMessage"
              @keyup.enter="sendMessage"
              type="text"
              placeholder="Send a message"
              class="flex-1 bg-transparent border-none py-2 focus:outline-none text-sm text-gray-800"
              :disabled="isRequestPending"
          />
          <button @click="sendMessage" :disabled="isRequestPending || !newMessage.trim()" class="text-blue-600 font-bold px-2 hover:text-blue-800 disabled:text-gray-400 text-sm">
            Send
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const newMessage = ref('');
const isRequestPending = ref(false);
const isAdminWaiting = ref(false);
const isChatClosed = ref(false);
const chatWindow = ref(null);
const messages = ref([{ role: 'model', content: 'Hello, I\'m an automatic AI Assistant. How may I help you?' }]);
const sessionId = ref(null);
let pollingInterval = null;

const toggleChat = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    scrollToBottom();
    fetchUpdates();
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (chatWindow.value) {
    chatWindow.value.scrollTo({ top: chatWindow.value.scrollHeight, behavior: 'smooth' });
  }
};

// ChatBot.vue - fetchUpdates részlet
const fetchUpdates = async () => {
  if (!sessionId.value || !isOpen.value) return;
  try {
    const baseUrl = import.meta.env.VITE_API_URL || 'https://api.uccproject.localhost/api';
    const response = await axios.get(`${baseUrl}/chat/history/${sessionId.value}`);

    if (response.data.length > 0) {
      const lastMsg = response.data[response.data.length - 1];

      isChatClosed.value = !!lastMsg.is_closed;
      // Akkor várunk adminra, ha a legutolsó üzenet állapota needs_admin,
      // DE a legutolsó küldő NEM az admin (mert ha ő írt, akkor épp nem várunk rá)
      isAdminWaiting.value = !isChatClosed.value && !!lastMsg.needs_admin && lastMsg.sender !== 'admin';

      const formattedMessages = response.data.map(m => ({
        role: m.sender === 'user' ? 'user' : (m.sender === 'bot' ? 'model' : 'admin'),
        content: m.message
      }));

      if (JSON.stringify(formattedMessages) !== JSON.stringify(messages.value)) {
        messages.value = formattedMessages;
        await scrollToBottom();
      }
    }
  } catch (error) {
    console.error("Polling hiba:", error);
  }
};

const startNewSession = () => {
  const newId = 'session_' + Math.random().toString(36).substr(2, 9);
  sessionId.value = newId;
  localStorage.setItem('chat_session_id', newId);
  messages.value = [{ role: 'model', content: 'Hello, I\'m an automatic AI Assistant. How may I help you?' }];
  isChatClosed.value = false;
  isAdminWaiting.value = false;
};

onMounted(() => {
  sessionId.value = localStorage.getItem('chat_session_id');
  if (!sessionId.value) {
    startNewSession();
  }
  pollingInterval = setInterval(fetchUpdates, 4000);
});

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval);
});

const sendMessage = async () => {
  const text = newMessage.value.trim();
  if (!text || isRequestPending.value) return;

  // 1. HA LE VOLT ZÁRVA A BESZÉLGETÉS, ÚJAT NYITUNK
  if (isChatClosed.value) {
    startNewSession();
  }

  // 2. AZONNALI RESET: Mivel a user írt, a várakozás megszűnik
  isAdminWaiting.value = false;
  messages.value.push({ role: 'user', content: text });

  const payload = { message: text, session_id: sessionId.value };
  newMessage.value = '';
  await scrollToBottom();

  try {
    isRequestPending.value = true;
    const baseUrl = import.meta.env.VITE_API_URL || 'https://api.uccproject.localhost/api';

    // Elküldjük az üzenetet
    await axios.post(`${baseUrl}/chat`, payload);

    // Kényszerített frissítés, hogy lássuk a bot válaszát és az új needs_admin állapotot
    await fetchUpdates();
  } catch (error) {
    console.error("Küldési hiba:", error);
    messages.value.push({ role: 'model', content: 'An error occured. Please check your connection.' });
  } finally {
    isRequestPending.value = false;
    await scrollToBottom();
  }
};
</script>

<style scoped>
.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes ping {
  75%, 100% { transform: scale(2); opacity: 0; }
}
</style>
<template>
  <div class="max-w-4xl mx-auto p-6 text-gray-800">
    <div class="flex items-center justify-end mb-8 border-b-2 border-gray-100 pb-6">
      <div class="flex space-x-3">
        <button @click="changeView('all')" class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4">All Events</button>
        <button @click="changeView('mine')" class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4">My Events</button>
        <button @click="fetchEvents" class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4">Refresh</button>
        <button @click="showCreateForm = !showCreateForm" class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-bold text-sm px-6 shadow-lg shadow-blue-100">
          {{ showCreateForm ? 'Close' : '+ New Event' }}
        </button>
      </div>
    </div>

    <div v-if="showCreateForm" class="mb-10 bg-white border-2 border-blue-500 rounded-2xl p-8 shadow-xl">
      <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
        <span class="bg-blue-500 w-2 h-6 rounded-full mr-3"></span> Add new event
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Event name</label>
          <input v-model="newEvent.event_name" type="text" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="e.g. Workshop">
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 ml-1">Date and time</label>
          <VueDatePicker
              v-model="newEvent.event_date"
              :min-date="new Date()"
              format="yyyy/MM/dd HH:mm"
              model-type="yyyy-MM-dd HH:mm:ss"
              placeholder="Select date and time"
              auto-apply
          />
        </div>
      </div>

      <div class="mt-6 space-y-2">
        <label class="block text-sm font-bold text-gray-700 ml-1">Description</label>
        <textarea v-model="newEvent.event_description" rows="3" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Event details..."></textarea>
      </div>

      <button @click="createEvent" class="mt-8 w-full py-4 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all uppercase tracking-wider">Save Event</button>
    </div>

    <div v-if="loading" class="p-20 text-center">
      <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mb-4"></div>
      <p class="text-gray-400 font-medium">Loading data...</p>
    </div>

    <div v-else class="grid gap-6">
      <div v-for="event in events" :key="event.event_id" class="group">

        <div v-if="editingEvent && editingEvent.event_id === event.event_id" class="bg-white border-2 border-blue-500 rounded-2xl p-8 shadow-xl mb-4">
          <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="bg-blue-500 w-2 h-6 rounded-full mr-3"></span> Edit event
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-bold text-gray-700 ml-1">Event name</label>
              <input v-model="editingEvent.event_name" type="text" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all">
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-bold text-gray-700 ml-1">Date and time</label>
              <VueDatePicker
                  v-model="editingEvent.event_date"
                  :min-date="new Date(originalEventDate) < new Date() ? new Date(originalEventDate) : new Date()"
                  format="yyyy/MM/dd HH:mm"
                  model-type="yyyy-MM-dd HH:mm:ss"
                  auto-apply
              />
            </div>
          </div>
          <div class="mt-6 space-y-2">
            <label class="block text-sm font-bold text-gray-700 ml-1">Description</label>
            <textarea v-model="editingEvent.event_description" rows="3" class="w-full bg-gray-50 border-2 border-gray-200 p-3 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all"></textarea>
          </div>
          <div class="flex justify-end space-x-4 mt-8">
            <button @click="editingEvent = null" class="p-3 bg-gray-50 text-red-400 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all border border-transparent hover:border-red-100 font-bold text-sm px-4">Cancel</button>
            <button @click="updateEvent" class="px-8 py-4 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all uppercase tracking-wider">Save changes</button>
          </div>
        </div>

        <div v-else class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between hover:border-blue-300 transition-all shadow-sm">
          <div class="space-y-2">
            <h2 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ event.event_name }}</h2>
            <div class="flex items-center text-blue-500 font-bold text-sm bg-blue-50 w-fit px-3 py-1 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ formatDateForList(event.event_date) }}
            </div>
            <p v-if="event.event_description" class="text-gray-500 text-sm leading-relaxed mt-2">{{ event.event_description }}</p>
          </div>
          <div class="flex items-center space-x-2 mt-6 md:mt-0">
            <template v-if="event.event_user_id === currentUserId || currentUserRole === 'admin'">
              <button @click="startEdit(event)" class="p-3 bg-gray-50 text-white rounded-xl hover:bg-blue-50 hover:text-gray-300 transition-all border border-transparent hover:border-blue-100 font-bold text-sm px-4">Edit</button>
              <button @click="openDeleteModal(event.event_id)" class="p-3 bg-gray-50 text-red-400 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all border border-transparent hover:border-red-100 font-bold text-sm px-4">Delete</button>
            </template>
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

    <ConfirmModal
        :show="showDeleteModal"
        title="Delete Event"
        message="Are you sure you want to remove this event? This cannot be undone."
        @close="showDeleteModal = false"
        @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, watch } from 'vue';
import { useRoute } from 'vue-router';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import ConfirmModal from './ConfirmModal.vue';
import '@vuepic/vue-datepicker/dist/main.css';

const route = useRoute();

// --- Állapotok ---
const events = ref([]);
const currentUserId = ref(null);
const currentUserRole = ref(null);
const loading = ref(true);
const viewMode = ref('all');
const editingEvent = ref(null);
const showCreateForm = ref(false);
const newEvent = ref({ event_name: '', event_date: '', event_description: '' });
const originalEventDate = ref(null);
const showDeleteModal = ref(false);
const eventIdToDelete = ref(null);
const toast = reactive({ show: false, message: '', type: 'success' });

// --- Toast segédfüggvény ---
const triggerToast = (msg, type = 'success') => {
  toast.message = msg;
  toast.type = type;
  toast.show = true;
  setTimeout(() => toast.show = false, 3500);
};

// --- Események betöltése és sorbarendezése ---
const fetchEvents = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user'));
    let url = '/api/events';
    if (viewMode.value === 'mine' && user) url = `/api/events/user/${user.id}`;

    const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } });
    if (res.ok) {
      const data = await res.json();
      // FRONTEND RENDEZÉS: Dátum szerint növekvő sorrend (legközelebbi felül)
      events.value = data.sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
    }
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const changeView = (mode) => { viewMode.value = mode; fetchEvents(); };

// --- Létrehozás ---
const createEvent = async () => {
  if (!newEvent.value.event_name || !newEvent.value.event_date) {
    triggerToast("Name and future date are required!", "error");
    return;
  }
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/events', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(newEvent.value)
    });
    if (res.ok) {
      await fetchEvents();
      newEvent.value = { event_name: '', event_date: '', event_description: '' };
      showCreateForm.value = false;
      triggerToast("Event created successfully!");
    } else {
      triggerToast("Error creating event", "error");
    }
  } catch (error) { console.error(error); }
};

const startEdit = (event) => {
  editingEvent.value = { ...event };
  originalEventDate.value = event.event_date;
};
// --- Módosítás ---
const updateEvent = async () => {
  if (!editingEvent.value.event_name || !editingEvent.value.event_date) {
    triggerToast("Name and date are required!", "error");
    return;
  }

  const isDateChanged = editingEvent.value.event_date !== originalEventDate.value;
  const isPastDate = new Date(editingEvent.value.event_date) < new Date();

  if (isDateChanged && isPastDate) {
    triggerToast("Please select a future date!", "error");
    return;
  }

  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`/api/events/${editingEvent.value.event_id}`, {
      method: 'PUT',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(editingEvent.value)
    });
    if (res.ok) {
      await fetchEvents();
      editingEvent.value = null;
      originalEventDate.value = null;
      triggerToast("Event updated successfully!");
    } else {
      const errorData = await res.json();
      triggerToast(errorData.message || "Update failed", "error");
    }
  } catch (error) { console.error(error); }
};
// --- Törlés ---
const openDeleteModal = (id) => {
  eventIdToDelete.value = id;
  showDeleteModal.value = true;
};

const handleDelete = async () => {
  showDeleteModal.value = false;
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`/api/events/${eventIdToDelete.value}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (res.ok) {
      events.value = events.value.filter(e => e.event_id !== eventIdToDelete.value);
      triggerToast("Event deleted.");
    }
  } catch (error) { console.error(error); }
};

const formatDateForList = (ds) => {
  if (!ds) return '';
  const d = new Date(ds);
  return `${d.getFullYear()}/${String(d.getMonth()+1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
};

const initView = () => {
  viewMode.value = route.query.mode || 'all';
  fetchEvents();
};

watch(() => route.query.mode, initView);

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (user) {
    currentUserId.value = user.id;
    currentUserRole.value = user.role;
  }
  initView();
});
</script>

<style scoped>
/* TOAST ANIMÁCIÓK */
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { opacity: 0; transform: translateY(20px) scale(0.9); }
.toast-leave-to { opacity: 0; transform: translateX(50px); }

/* DATEPICKER INPUT TESTRESZABÁSA */
:deep(.dp__input) {
  background-color: #f9fafb !important;
  border: 2px solid #e5e7eb !important;
  border-radius: 0.75rem !important;
  padding: 0.75rem 0.75rem 0.75rem 2.5rem !important;
  color: #111827 !important;
  transition: all 0.2s;
}

:deep(.dp__input:focus) {
  border-color: #3b82f6 !important;
  background-color: #ffffff !important;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
}

:deep(.dp__input_icon) {
  color: #9ca3af;
  padding-left: 0.5rem;
}
</style>
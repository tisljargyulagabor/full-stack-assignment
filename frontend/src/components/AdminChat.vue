<template>
  <div class="admin-chat-container">
    <div class="sessions-sidebar">
      <div class="sidebar-header">
        <h3 class="font-bold text-gray-800">Chat Manager</h3>
      </div>

      <div class="sidebar-content">
        <div class="section-title active-header">
          🔴 Waiting for help ({{ activeSessions.length }})
        </div>
        <div class="card-list">
          <div v-if="activeSessions.length > 0">
            <div v-for="session in activeSessions"
                 :key="session.session_id"
                 @click="loadHistory(session.session_id)"
                 :class="['chat-card', { 'active': currentSession === session.session_id }]"
                 class="active-card-border">
              <div class="card-icon">👤</div>
              <div class="card-details">
                <span class="user-name">{{ session.user_name || 'Guest' }}</span>
                <small class="time-stamp">{{ formatDate(session.last_message) }}</small>
              </div>
            </div>
          </div>
          <div v-else class="empty-msg">No active requests.</div>
        </div>

        <div class="section-title history-header">
          📁 History
        </div>
        <div class="card-list">
          <div v-if="closedSessions.length > 0">
            <div v-for="session in closedSessions"
                 :key="session.session_id"
                 @click="loadHistory(session.session_id)"
                 :class="['chat-card', { 'active': currentSession === session.session_id }]"
                 class="history-card-border">
              <div class="card-icon gray-icon">👤</div>
              <div class="card-details">
                <span class="user-name">{{ session.user_name || 'Guest' }}</span>
                <small class="time-stamp">{{ formatDate(session.last_message) }}</small>
              </div>
            </div>
          </div>
          <div v-else class="empty-msg">No saved conversations.</div>
        </div>
      </div>
    </div>

    <div class="chat-window">
      <div v-if="currentSession" class="chat-wrapper">
        <div class="chat-header">
          <div>
            <div class="header-user-name">{{ currentUserName }}</div>
          </div>
          <span :class="['status-badge', isCurrentActive ? 'status-active' : 'status-closed']">
            {{ isCurrentActive ? 'ACTIVE REQUEST' : 'CLOSED' }}
          </span>
        </div>

        <div class="messages-list" ref="messageBox">
          <div v-for="msg in history" :key="msg.id" :class="['msg-row', msg.sender]">
            <div class="msg-bubble">
              <div class="msg-label">{{ msg.sender === 'user' ? 'CUSTOMER' : (msg.sender === 'admin' ? 'ADMIN' : 'BOT') }}</div>
              <div class="msg-text">{{ msg.message }}</div>
            </div>
          </div>
        </div>

        <div v-if="isCurrentActive" class="reply-area">
          <input
              v-model="adminReply"
              @keyup.enter="sendReply"
              placeholder="Write a reply..."
              class="reply-input"
          />
          <button @click="sendReply" class="send-btn" :disabled="!adminReply.trim()">Send</button>
          <button @click="closeSession" class="close-btn">Close</button>
        </div>
        <div v-else class="closed-banner">
          This conversation is closed and is read-only.
        </div>
      </div>

      <div v-else class="no-selection">
        <p>Select a conversation from the list!</p>
      </div>
    </div>

    <ConfirmModal
        :show="isConfirmModalOpen"
        title="Close Case"
        message="Are you sure you want to close this case?"
        @close="isConfirmModalOpen = false"
        @confirm="handleCloseConfirm"
    />
  </div>
</template>

<script>
import axios from 'axios';
import ConfirmModal from './ConfirmModal.vue';

export default {
  components: { ConfirmModal },
  data() {
    return {
      flaggedSessions: [],
      history: [],
      currentSession: null,
      adminReply: '',
      polling: null,
      isConfirmModalOpen: false // Modal state added
    }
  },
  computed: {
    activeSessions() {
      return this.flaggedSessions.filter(s =>
          (s.needs_admin == 1 || s.needs_admin === true) && (s.is_closed == 0 || s.is_closed === false)
      );
    },
    closedSessions() {
      return this.flaggedSessions.filter(s => (s.is_closed == 1 || s.is_closed === true));
    },
    isCurrentActive() {
      if (!this.currentSession) return false;
      const session = this.flaggedSessions.find(s => s.session_id === this.currentSession);
      return session ? (session.is_closed == 0 || session.is_closed === false) : false;
    },
    currentUserName() {
      if (!this.currentSession) return '';
      const s = this.flaggedSessions.find(s => s.session_id === this.currentSession);
      return s ? (s.user_name || 'Guest') : 'Guest';
    }
  },
  mounted() {
    this.fetchData();
    this.polling = setInterval(this.fetchData, 4000);
  },
  beforeUnmount() {
    if (this.polling) clearInterval(this.polling);
  },
  methods: {
    async fetchData() {
      try {
        const res = await axios.get('/api/admin/flagged-sessions');
        this.flaggedSessions = res.data;
        if (this.currentSession) await this.refreshHistory();
      } catch (e) { console.error("Error downloading data", e); }
    },
    async loadHistory(id) {
      this.currentSession = id;
      this.history = [];
      await this.refreshHistory();
      this.scrollToBottom();
    },
    async refreshHistory() {
      if (!this.currentSession) return;
      try {
        const res = await axios.get(`/api/admin/chat-history/${this.currentSession}`);
        if (res.data.length > this.history.length) {
          this.history = res.data;
          this.scrollToBottom();
        }
      } catch (e) { console.error("History error", e); }
    },
    async sendReply() {
      if (!this.adminReply.trim()) return;
      try {
        await axios.post('/api/admin/reply', { session_id: this.currentSession, message: this.adminReply });
        this.adminReply = '';
        await this.refreshHistory();
      } catch (e) { alert("Error while sending"); }
    },
    closeSession() {
      // Logic replaced to open modal instead of native confirm
      this.isConfirmModalOpen = true;
    },
    async handleCloseConfirm() {
      // New method to handle the actual API call after modal confirmation
      this.isConfirmModalOpen = false;
      try {
        await axios.post('/api/admin/close-session', { session_id: this.currentSession });
        await this.fetchData();
      } catch (e) { alert("Error while closing"); }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const el = this.$refs.messageBox;
        if (el) el.scrollTop = el.scrollHeight;
      });
    },
    formatDate(d) {
      return d ? new Date(d).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
    }
  }
}
</script>

<style scoped>
/* Styles remain identical to your original code */
.admin-chat-container {
  display: flex;
  height: calc(100vh - 120px);
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  color: #2d3748;
}

.sessions-sidebar {
  width: 300px;
  border-right: 1px solid #edf2f7;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
}
.sidebar-header {
  padding: 1rem;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
}

.sidebar-content { flex: 1; overflow-y: auto; }
.section-title {
  padding: 0.75rem 1rem;
  font-size: 0.7rem;
  font-weight: bold;
  background: #edf2f7;
  color: #4a5568;
}
.active-header { color: #c53030; }

.card-list {
  padding: 10px;
}

.chat-card {
  display: flex;
  align-items: center;
  padding: 12px;
  margin-bottom: 10px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.chat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.chat-card.active {
  background: #3182ce !important;
  border-color: #2b6cb0 !important;
  color: white !important;
}

.card-icon {
  width: 40px;
  height: 40px;
  background: #ebf8ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  font-size: 1.2rem;
}
.active .card-icon {
  background: rgba(255,255,255,0.2);
}
.gray-icon {
  background: #f1f5f9;
}

.card-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: bold;
  font-size: 0.95rem;
}

.time-stamp {
  font-size: 0.75rem;
  color: #718096;
}
.active .time-stamp {
  color: #ebf8ff;
}

.active-card-border { border-left: 4px solid #f56565; }
.history-card-border { border-left: 4px solid #cbd5e0; }

.chat-window { flex: 1; display: flex; flex-direction: column; background: #fff; }
.chat-wrapper { display: flex; flex-direction: column; height: 100%; }

.chat-header {
  padding: 0.75rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}
.header-user-name { font-weight: bold; font-size: 1.1rem; color: #1a202c; }

.status-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
.status-active { background: #fed7d7; color: #c53030; }
.status-closed { background: #edf2f7; color: #4a5568; }

.messages-list {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
  background: #f1f5f9;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.msg-row { display: flex; width: 100%; }
.msg-bubble { padding: 0.75rem 1rem; border-radius: 8px; max-width: 75%; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
.msg-label { font-size: 0.6rem; font-weight: bold; margin-bottom: 3px; opacity: 0.7; }
.msg-text { font-size: 0.95rem; line-height: 1.4; color: #1a202c; }

.user { justify-content: flex-start; }
.user .msg-bubble { background: white; border: 1px solid #e2e8f0; }

.admin { justify-content: flex-end; }
.admin .msg-bubble { background: #3182ce; color: white; }
.admin .msg-text { color: white; }

.bot { justify-content: center; }
.bot .msg-bubble { background: #fffaf0; border: 1px solid #feebc8; text-align: center; }

.reply-area {
  padding: 1.25rem;
  background: white;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 0.75rem;
}
.reply-input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
  outline: none;
  font-size: 0.95rem;
}
.reply-input:focus { border-color: #3182ce; box-shadow: 0 0 0 2px rgba(49, 130, 206, 0.1); }

.send-btn { background: #3182ce; color: white; padding: 0 1.25rem; border-radius: 6px; font-weight: bold; border: none; cursor: pointer; }
.close-btn { background: #e53e3e; color: white; padding: 0 1rem; border-radius: 6px; font-weight: bold; border: none; cursor: pointer; }
.send-btn:disabled { background: #a0aec0; cursor: not-allowed; }

.closed-banner { padding: 1rem; text-align: center; background: #edf2f7; color: #4a5568; font-weight: bold; font-size: 0.9rem; }
.no-selection { flex: 1; display: flex; align-items: center; justify-content: center; color: #a0aec0; }
.empty-msg { padding: 1rem; text-align: center; font-size: 0.8rem; color: #a0aec0; }
</style>
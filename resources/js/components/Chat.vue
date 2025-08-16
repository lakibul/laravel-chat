<template>
  <div class="chat-container">
    <!-- Authentication Form -->
    <div v-if="!isAuthenticated" class="auth-container">
      <div class="auth-form">
        <h2>{{ isLoginMode ? 'Login' : 'Register' }}</h2>
        <form @submit.prevent="isLoginMode ? login() : register()">
          <div v-if="!isLoginMode" class="form-group">
            <input
              v-model="authForm.name"
              type="text"
              placeholder="Name"
              required
            />
          </div>
          <div class="form-group">
            <input
              v-model="authForm.email"
              type="email"
              placeholder="Email"
              required
            />
          </div>
          <div class="form-group">
            <input
              v-model="authForm.password"
              type="password"
              placeholder="Password"
              required
            />
          </div>
          <div v-if="!isLoginMode" class="form-group">
            <input
              v-model="authForm.password_confirmation"
              type="password"
              placeholder="Confirm Password"
              required
            />
          </div>
          <button type="submit" :disabled="authLoading">
            {{ authLoading ? 'Loading...' : (isLoginMode ? 'Login' : 'Register') }}
          </button>
        </form>
        <p class="auth-switch">
          {{ isLoginMode ? "Don't have an account?" : "Already have an account?" }}
          <a href="#" @click.prevent="isLoginMode = !isLoginMode">
            {{ isLoginMode ? 'Register' : 'Login' }}
          </a>
        </p>
        <div v-if="authError" class="error">{{ authError }}</div>
      </div>
    </div>

    <!-- Chat Interface -->
    <div v-else class="chat-interface">
      <div class="chat-sidebar">
        <div class="user-header">
          <h3>{{ currentUser.name }}</h3>
          <button @click="logout" class="logout-btn">Logout</button>
        </div>

        <div class="users-list">
          <h4>Users</h4>
          <div
            v-for="user in users"
            :key="user.id"
            @click="startConversation(user)"
            class="user-item"
            :class="{ active: selectedUser && selectedUser.id === user.id }"
          >
            <div class="user-name">{{ user.name }}</div>
            <div v-if="getUserUnreadCount(user.id)" class="unread-badge">
              {{ getUserUnreadCount(user.id) }}
            </div>
          </div>
        </div>

        <div class="conversations-list">
          <h4>Recent Conversations</h4>
          <div
            v-for="conversation in conversations"
            :key="conversation.id"
            @click="selectConversation(conversation)"
            class="conversation-item"
            :class="{ active: selectedConversation && selectedConversation.id === conversation.id }"
          >
            <div class="conversation-info">
              <div class="user-name">{{ conversation.other_user.name }}</div>
              <div v-if="conversation.latest_message" class="last-message">
                {{ conversation.latest_message.message.substring(0, 30) }}{{ conversation.latest_message.message.length > 30 ? '...' : '' }}
              </div>
            </div>
            <div v-if="conversation.unread_count" class="unread-badge">
              {{ conversation.unread_count }}
            </div>
          </div>
        </div>
      </div>

      <div class="chat-main">
        <div v-if="!selectedConversation" class="no-conversation">
          <p>Select a user or conversation to start chatting</p>
        </div>

        <div v-else class="conversation-view">
          <div class="conversation-header">
            <h3>{{ selectedUser.name }}</h3>
            <div v-if="isTyping" class="typing-indicator">
              {{ typingUser }} is typing...
            </div>
          </div>

          <div class="messages-container" ref="messagesContainer">
            <div
              v-for="message in messages"
              :key="message.id"
              class="message"
              :class="{ 'own-message': message.sender_id === currentUser.id }"
            >
              <div class="message-content">
                <div class="message-text">{{ message.message }}</div>
                <div class="message-time">{{ formatTime(message.created_at) }}</div>
              </div>
            </div>
          </div>

          <div class="message-input-container">
            <div class="message-input">
              <input
                v-model="newMessage"
                @keyup.enter="sendMessage"
                @input="handleTyping"
                placeholder="Type a message..."
                :disabled="sendingMessage"
              />
              <button @click="sendMessage" :disabled="!newMessage.trim() || sendingMessage">
                {{ sendingMessage ? 'Sending...' : 'Send' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, nextTick, watch } from 'vue'
import Echo from 'laravel-echo'

export default {
  name: 'Chat',
  setup() {
    // Authentication state
    const isAuthenticated = ref(false)
    const isLoginMode = ref(true)
    const authLoading = ref(false)
    const authError = ref('')
    const currentUser = ref(null)

    // Auth form
    const authForm = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    })

    // Chat state
    const users = ref([])
    const conversations = ref([])
    const messages = ref([])
    const selectedConversation = ref(null)
    const selectedUser = ref(null)
    const newMessage = ref('')
    const sendingMessage = ref(false)
    const isTyping = ref(false)
    const typingUser = ref('')
    const messagesContainer = ref(null)

    // Typing timer
    let typingTimer = null

    // Initialize
    onMounted(() => {
      checkAuthStatus()
    })

    // Watch for new messages to scroll to bottom
    watch(messages, () => {
      nextTick(() => {
        scrollToBottom()
      })
    }, { deep: true })

    // Authentication methods
    const checkAuthStatus = () => {
      const token = localStorage.getItem('auth_token')
      if (token) {
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        getCurrentUser()
      }
    }

    const getCurrentUser = async () => {
      try {
        const response = await window.axios.get('/api/me')
        currentUser.value = response.data
        isAuthenticated.value = true
        loadInitialData()
        setupEcho()
      } catch (error) {
        localStorage.removeItem('auth_token')
        delete window.axios.defaults.headers.common['Authorization']
      }
    }

    const login = async () => {
      authLoading.value = true
      authError.value = ''

      try {
        const response = await window.axios.post('/api/login', {
          email: authForm.email,
          password: authForm.password
        })

        localStorage.setItem('auth_token', response.data.access_token)
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`
        currentUser.value = response.data.user
        isAuthenticated.value = true
        loadInitialData()
        setupEcho()

        // Reset form
        Object.keys(authForm).forEach(key => authForm[key] = '')
      } catch (error) {
        authError.value = error.response?.data?.message || 'Login failed'
      } finally {
        authLoading.value = false
      }
    }

    const register = async () => {
      authLoading.value = true
      authError.value = ''

      try {
        const response = await window.axios.post('/api/register', authForm)

        localStorage.setItem('auth_token', response.data.access_token)
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`
        currentUser.value = response.data.user
        isAuthenticated.value = true
        loadInitialData()
        setupEcho()

        // Reset form
        Object.keys(authForm).forEach(key => authForm[key] = '')
      } catch (error) {
        authError.value = error.response?.data?.message || 'Registration failed'
      } finally {
        authLoading.value = false
      }
    }

    const logout = async () => {
      try {
        await window.axios.post('/api/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        localStorage.removeItem('auth_token')
        delete window.axios.defaults.headers.common['Authorization']
        isAuthenticated.value = false
        currentUser.value = null
        // Reset chat state
        users.value = []
        conversations.value = []
        messages.value = []
        selectedConversation.value = null
        selectedUser.value = null
      }
    }

    // Chat methods
    const loadInitialData = async () => {
      await Promise.all([
        loadUsers(),
        loadConversations()
      ])
    }

    const loadUsers = async () => {
      try {
        const response = await window.axios.get('/api/users')
        users.value = response.data
      } catch (error) {
        console.error('Error loading users:', error)
      }
    }

    const loadConversations = async () => {
      try {
        const response = await window.axios.get('/api/conversations')
        conversations.value = response.data
      } catch (error) {
        console.error('Error loading conversations:', error)
      }
    }

    const startConversation = async (user) => {
      try {
        const response = await window.axios.post('/api/conversations', {
          user_id: user.id
        })

        selectedUser.value = user
        selectedConversation.value = response.data
        loadMessages(response.data.id)

        // Update conversations list
        loadConversations()
      } catch (error) {
        console.error('Error starting conversation:', error)
      }
    }

    const selectConversation = (conversation) => {
      selectedConversation.value = conversation
      selectedUser.value = conversation.other_user
      loadMessages(conversation.id)
    }

    const loadMessages = async (conversationId) => {
      try {
        const response = await window.axios.get(`/api/conversations/${conversationId}/messages`)
        messages.value = response.data
        // Refresh conversations to update unread counts
        loadConversations()
      } catch (error) {
        console.error('Error loading messages:', error)
      }
    }

    const sendMessage = async () => {
      if (!newMessage.value.trim() || sendingMessage.value || !selectedUser.value) return

      sendingMessage.value = true

      try {
        await window.axios.post('/api/messages', {
          receiver_id: selectedUser.value.id,
          message: newMessage.value
        })

        newMessage.value = ''
        // Stop typing indicator
        sendTypingStatus(false)
        // Refresh conversations
        loadConversations()
      } catch (error) {
        console.error('Error sending message:', error)
      } finally {
        sendingMessage.value = false
      }
    }

    const handleTyping = () => {
      if (!selectedConversation.value) return

      sendTypingStatus(true)

      // Clear existing timer
      if (typingTimer) {
        clearTimeout(typingTimer)
      }

      // Stop typing after 2 seconds of inactivity
      typingTimer = setTimeout(() => {
        sendTypingStatus(false)
      }, 2000)
    }

    const sendTypingStatus = async (isTypingStatus) => {
      if (!selectedConversation.value) return

      try {
        await window.axios.post('/api/typing', {
          conversation_id: selectedConversation.value.id,
          is_typing: isTypingStatus
        })
      } catch (error) {
        console.error('Error sending typing status:', error)
      }
    }

    // Helper methods
    const getUserUnreadCount = (userId) => {
      const conversation = conversations.value.find(conv =>
        conv.other_user.id === userId
      )
      return conversation ? conversation.unread_count : 0
    }

    const formatTime = (timestamp) => {
      return new Date(timestamp).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const scrollToBottom = () => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
      }
    }

    // Setup Echo for real-time events
    const setupEcho = () => {
      // Get the dynamic base URL for broadcasting auth
      const getCurrentBaseUrl = () => {
        const currentUrl = window.location.origin + window.location.pathname.replace(/\/+$/, '');
        return currentUrl.endsWith('.php') ? currentUrl.substring(0, currentUrl.lastIndexOf('/')) : currentUrl;
      };

      const token = localStorage.getItem('auth_token');

      if (token) {
        // Initialize Echo with proper authentication
        window.Echo = new Echo({
          broadcaster: 'pusher',
          key: import.meta.env.VITE_PUSHER_APP_KEY || 'a1d360ab497a40571a2a',
          cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'us2',
          wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER || 'us2'}.pusher.com`,
          wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
          wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
          forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
          enabledTransports: ['ws', 'wss'],
          disableStats: true,
          authEndpoint: `${getCurrentBaseUrl()}/broadcasting/auth`,
          auth: {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          },
        });

        console.log('Echo initialized with token:', token.substring(0, 10) + '...');
      }
    }

    // Watch for conversation changes to setup listeners
    watch(selectedConversation, (newConversation, oldConversation) => {
      // Leave old channel
      if (oldConversation && window.Echo) {
        try {
          window.Echo.leaveChannel(`conversation.${oldConversation.id}`)
        } catch (error) {
          console.warn('Could not leave channel:', error)
        }
      }

      // Join new channel
      if (newConversation && window.Echo) {
        try {
          window.Echo.private(`conversation.${newConversation.id}`)
            .listen('.message.sent', (e) => {
              // Add message if it's not from current user
              if (e.sender_id !== currentUser.value.id) {
                messages.value.push(e)
                // Refresh conversations to update counts
                loadConversations()
              }
            })
            .listen('.user.typing', (e) => {
              if (e.user_id !== currentUser.value.id) {
                isTyping.value = e.is_typing
                typingUser.value = e.user_name

                if (e.is_typing) {
                  // Auto-hide typing indicator after 3 seconds
                  setTimeout(() => {
                    isTyping.value = false
                  }, 3000)
                }
              }
            })
        } catch (error) {
          console.warn('Real-time features not available:', error)
        }
      }
    })

    return {
      // Authentication
      isAuthenticated,
      isLoginMode,
      authLoading,
      authError,
      currentUser,
      authForm,
      login,
      register,
      logout,

      // Chat
      users,
      conversations,
      messages,
      selectedConversation,
      selectedUser,
      newMessage,
      sendingMessage,
      isTyping,
      typingUser,
      messagesContainer,
      startConversation,
      selectConversation,
      sendMessage,
      handleTyping,
      getUserUnreadCount,
      formatTime
    }
  }
}
</script>

<style scoped>
.chat-container {
  height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Authentication Styles */
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.auth-form {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 400px;
}

.auth-form h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #333;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

button[type="submit"] {
  width: 100%;
  padding: 0.75rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
}

button[type="submit"]:hover:not(:disabled) {
  background: #5a6fd8;
}

button[type="submit"]:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-switch {
  text-align: center;
  margin-top: 1rem;
}

.auth-switch a {
  color: #667eea;
  text-decoration: none;
}

.error {
  color: #e74c3c;
  text-align: center;
  margin-top: 1rem;
}

/* Chat Interface Styles */
.chat-interface {
  display: flex;
  height: 100vh;
}

.chat-sidebar {
  width: 300px;
  background: #f8f9fa;
  border-right: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
}

.user-header {
  padding: 1rem;
  background: #667eea;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-header h3 {
  margin: 0;
}

.logout-btn {
  background: rgba(255,255,255,0.2);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
}

.logout-btn:hover {
  background: rgba(255,255,255,0.3);
}

.users-list, .conversations-list {
  flex: 1;
  overflow-y: auto;
}

.users-list h4, .conversations-list h4 {
  padding: 1rem;
  margin: 0;
  background: #e9ecef;
  color: #495057;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.user-item, .conversation-item {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #dee2e6;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background 0.2s;
}

.user-item:hover, .conversation-item:hover {
  background: #e9ecef;
}

.user-item.active, .conversation-item.active {
  background: #667eea;
  color: white;
}

.user-name {
  font-weight: 500;
}

.conversation-info {
  flex: 1;
}

.last-message {
  font-size: 0.8rem;
  color: #6c757d;
  margin-top: 0.25rem;
}

.conversation-item.active .last-message {
  color: rgba(255,255,255,0.8);
}

.unread-badge {
  background: #e74c3c;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: bold;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.no-conversation {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  font-size: 1.2rem;
}

.conversation-view {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.conversation-header {
  padding: 1rem;
  background: white;
  border-bottom: 1px solid #dee2e6;
  position: relative;
}

.conversation-header h3 {
  margin: 0;
  color: #333;
}

.typing-indicator {
  position: absolute;
  bottom: 0.5rem;
  left: 1rem;
  font-size: 0.8rem;
  color: #667eea;
  font-style: italic;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #f8f9fa;
}

.message {
  margin-bottom: 1rem;
  display: flex;
}

.message.own-message {
  justify-content: flex-end;
}

.message-content {
  max-width: 70%;
  background: white;
  padding: 0.75rem 1rem;
  border-radius: 15px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message.own-message .message-content {
  background: #667eea;
  color: white;
}

.message-text {
  margin-bottom: 0.25rem;
}

.message-time {
  font-size: 0.7rem;
  opacity: 0.7;
}

.message-input-container {
  padding: 1rem;
  background: white;
  border-top: 1px solid #dee2e6;
}

.message-input {
  display: flex;
  gap: 0.5rem;
}

.message-input input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 25px;
  outline: none;
}

.message-input input:focus {
  border-color: #667eea;
}

.message-input button {
  padding: 0.75rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  transition: background 0.3s;
}

.message-input button:hover:not(:disabled) {
  background: #5a6fd8;
}

.message-input button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .chat-sidebar {
    width: 250px;
  }

  .auth-form {
    margin: 1rem;
  }
}
</style>

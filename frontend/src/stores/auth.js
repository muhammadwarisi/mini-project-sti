import { computed, reactive } from 'vue'
import { api, TOKEN_KEY, USER_KEY } from '../lib/api'

function readStoredUser() {
  try {
    return JSON.parse(localStorage.getItem(USER_KEY) || 'null')
  } catch {
    return null
  }
}

export const authState = reactive({
  token: localStorage.getItem(TOKEN_KEY) || '',
  user: readStoredUser(),
})

function storeSession(payload) {
  authState.token = payload.token
  authState.user = payload.user

  localStorage.setItem(TOKEN_KEY, payload.token)
  localStorage.setItem(USER_KEY, JSON.stringify(payload.user))
}

function storeUser(user) {
  authState.user = user
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function clearSession() {
  authState.token = ''
  authState.user = null

  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

export function useAuth() {
  const isAuthenticated = computed(() => Boolean(authState.token))
  const isGuru = computed(() => authState.user?.role === 'guru')
  const isSiswa = computed(() => authState.user?.role === 'siswa')

  async function login(credentials) {
    const response = await api.post('/login', credentials)
    storeSession(response.data)
    return response
  }

  async function register(payload) {
    const response = await api.post('/register', payload)
    storeSession(response.data)
    return response
  }

  async function refreshUser() {
    const user = await api.get('/user')
    storeUser(user)
    return user
  }

  function logout() {
    clearSession()
  }

  return {
    state: authState,
    isAuthenticated,
    isGuru,
    isSiswa,
    login,
    register,
    refreshUser,
    logout,
  }
}

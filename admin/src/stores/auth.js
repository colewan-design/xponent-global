import { defineStore } from 'pinia'
import { api, ensureCsrfCookie } from '../lib/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    initialized: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    async login(email, password) {
      await ensureCsrfCookie()
      const { data } = await api.post('/auth/login', { email, password })
      this.user = data.data
    },
    async logout() {
      await api.post('/auth/logout')
      this.user = null
    },
    async fetchUser() {
      try {
        const { data } = await api.get('/auth/me')
        this.user = data.data
      } catch {
        this.user = null
      } finally {
        this.initialized = true
      }
    },
  },
})

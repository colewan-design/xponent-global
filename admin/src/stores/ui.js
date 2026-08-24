import { defineStore } from 'pinia'

/**
 * Chrome-level UI state: the light/dark theme and the mobile nav drawer.
 *
 * The theme is written to `body[data-theme]` because every token in
 * assets/theme.css is redeclared under that selector — no component needs to
 * know which theme is active, it just reads the tokens.
 */
export const useUiStore = defineStore('ui', {
  state: () => ({
    theme: 'light',
    mobileDrawerOpen: false,
  }),

  actions: {
    setTheme(theme) {
      this.theme = theme
      document.body.setAttribute('data-theme', theme)
      try {
        localStorage.setItem('xponent_admin_theme', theme)
      } catch {
        // Private-mode browsers throw on write; the theme still applies for
        // this session, it just will not be remembered.
      }
    },

    toggleTheme() {
      this.setTheme(this.theme === 'dark' ? 'light' : 'dark')
    },

    loadTheme() {
      let stored = null
      try {
        stored = localStorage.getItem('xponent_admin_theme')
      } catch {
        stored = null
      }
      this.setTheme(stored === 'dark' ? 'dark' : 'light')
    },

    openMobileDrawer() {
      this.mobileDrawerOpen = true
    },
    closeMobileDrawer() {
      this.mobileDrawerOpen = false
    },
    toggleMobileDrawer() {
      this.mobileDrawerOpen = !this.mobileDrawerOpen
    },
  },
})

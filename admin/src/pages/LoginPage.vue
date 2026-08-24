<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import BaseInput from '../components/BaseInput.vue'
import BaseButton from '../components/BaseButton.vue'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push(route.query.redirect?.toString() ?? { name: 'dashboard' })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Unable to log in.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login">
    <!-- The brand panel is always dark and uses literal values rather than the
         chrome tokens: it is the one surface that should not follow the admin's
         light/dark toggle, since it renders before a session exists. -->
    <div class="login-brand">
      <div class="login-brand-inner">
        <span class="login-brand-mark">XG</span>
        <h1 class="login-brand-title">Xponent Global</h1>
        <p class="login-brand-copy">
          Admin console for the solutions catalogue, careers, media and every
          page of the public site.
        </p>
      </div>
    </div>

    <div class="login-form-side">
      <form class="login-form" @submit.prevent="handleSubmit">
        <h2 class="login-form-title">Sign in</h2>
        <p class="login-form-subtitle">Use your Xponent Global admin account.</p>

        <label class="ui-field">
          <span class="ui-field-label">Email</span>
          <BaseInput v-model="email" type="email" required />
        </label>
        <label class="ui-field">
          <span class="ui-field-label">Password</span>
          <BaseInput v-model="password" type="password" required />
        </label>

        <p v-if="error" class="login-error">{{ error }}</p>

        <BaseButton type="submit" :disabled="loading" style="width: 100%; height: 42px">
          {{ loading ? 'Signing in…' : 'Sign in' }}
        </BaseButton>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  background: var(--color-bg);
}

.login-brand {
  background: #171615;
  color: #fff;
  display: flex;
  align-items: center;
  padding: 48px;
  position: relative;
  overflow: hidden;
}

/* Soft gold wash so the panel is not a flat black rectangle. */
.login-brand::after {
  content: '';
  position: absolute;
  width: 520px;
  height: 520px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(238, 181, 0, 0.22) 0%, transparent 70%);
  right: -180px;
  bottom: -180px;
}

.login-brand-inner { position: relative; z-index: 1; max-width: 420px; }

.login-brand-mark {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: var(--brand-gradient);
  color: #1d1d1d;
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 28px;
}

.login-brand-title {
  font-size: 34px;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin: 0 0 12px;
}

.login-brand-copy {
  font-size: 15px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.62);
  margin: 0;
}

.login-form-side {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 32px;
}

.login-form { width: 100%; max-width: 360px; }

.login-form-title {
  font-size: 26px;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--color-text);
  margin: 0 0 4px;
}

.login-form-subtitle {
  font-size: 14px;
  color: var(--color-text-2);
  margin: 0 0 28px;
}

.login-error {
  font-size: 13px;
  color: var(--color-danger);
  background: var(--tint-danger);
  border-radius: 10px;
  padding: 10px 12px;
  margin: 0 0 16px;
}

@media (max-width: 860px) {
  .login { grid-template-columns: 1fr; }
  .login-brand { display: none; }
}
</style>

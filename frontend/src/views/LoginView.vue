<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AuthLayout from '../components/AuthLayout.vue'
import AppIcon from '../components/AppIcon.vue'
import { useAuth } from '../stores/auth'
import { notify } from '../stores/toast'

const router = useRouter()
const route = useRoute()
const { login } = useAuth()

const form = reactive({
  email: '',
  password: '',
})

const errors = ref({})
const formError = ref('')
const submitting = ref(false)
const showPassword = ref(false)

const demoAccounts = [
  { label: 'Guru', email: 'guru@example.com', password: 'password' },
  { label: 'Siswa', email: 'siswa@example.com', password: 'password' },
]

function errorFor(field) {
  const error = errors.value[field]

  if (Array.isArray(error)) {
    return error[0] || ''
  }

  return error || ''
}

function fillDemo(account) {
  form.email = account.email
  form.password = account.password
  errors.value = {}
  formError.value = ''
}

async function submit() {
  const validation = {}

  if (!form.email.trim()) {
    validation.email = 'Email wajib diisi.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    validation.email = 'Format email tidak valid.'
  }

  if (!form.password) {
    validation.password = 'Password wajib diisi.'
  }

  errors.value = validation
  formError.value = ''

  if (Object.keys(validation).length > 0) {
    return
  }

  submitting.value = true

  try {
    await login({
      email: form.email.trim(),
      password: form.password,
    })

    notify('success', 'Berhasil masuk. Selamat datang kembali!')

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : ''
    await router.replace(redirect.startsWith('/') ? redirect : { name: 'tasks' })
  } catch (error) {
    errors.value = error.errors || {}
    formError.value = error.message || 'Gagal masuk. Coba lagi.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="auth-card__heading">
      <h1>Selamat datang kembali</h1>
      <p>Masuk untuk melanjutkan ke daftar tugas kelasmu.</p>
    </div>

    <div v-if="formError" class="alert alert--error" role="alert">
      <AppIcon name="alert" :size="18" />
      <span>{{ formError }}</span>
    </div>

    <form class="form" novalidate @submit.prevent="submit">
      <div class="field">
        <label for="login-email">Email</label>
        <div class="input-wrap">
          <span class="input-icon"><AppIcon name="mail" :size="18" /></span>
          <input
            id="login-email"
            v-model="form.email"
            type="email"
            autocomplete="email"
            placeholder="nama@sekolah.sch.id"
            :class="{ 'input--invalid': errors.email }"
          />
        </div>
        <p v-if="errorFor('email')" class="field-error">{{ errorFor('email') }}</p>
      </div>

      <div class="field">
        <label for="login-password">Password</label>
        <div class="input-wrap">
          <span class="input-icon"><AppIcon name="lock" :size="18" /></span>
          <input
            id="login-password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            placeholder="Masukkan password"
            :class="{ 'input--invalid': errors.password }"
          />
          <button
            type="button"
            class="input-action"
            :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
            @click="showPassword = !showPassword"
          >
            <AppIcon :name="showPassword ? 'eye-off' : 'eye'" :size="18" />
          </button>
        </div>
        <p v-if="errorFor('password')" class="field-error">{{ errorFor('password') }}</p>
      </div>

      <button type="submit" class="btn btn--primary btn--block" :disabled="submitting">
        <span v-if="submitting" class="spinner spinner--sm"></span>
        <template v-else>
          Masuk
          <AppIcon name="arrow-right" :size="18" />
        </template>
        <span v-if="submitting">Memproses...</span>
      </button>
    </form>

    <p class="auth-card__switch">
      Belum punya akun?
      <RouterLink :to="{ name: 'register' }">Daftar sekarang</RouterLink>
    </p>

    <div class="demo-box">
      <div class="demo-box__heading">
        <AppIcon name="sparkles" :size="16" />
        <span>Akun demo</span>
      </div>
      <div class="demo-box__accounts">
        <button
          v-for="account in demoAccounts"
          :key="account.email"
          type="button"
          class="demo-account"
          @click="fillDemo(account)"
        >
          <strong>{{ account.label }}</strong>
          <span>{{ account.email }}</span>
        </button>
      </div>
    </div>
  </AuthLayout>
</template>

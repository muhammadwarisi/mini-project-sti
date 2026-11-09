<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import AuthLayout from '../components/AuthLayout.vue'
import AppIcon from '../components/AppIcon.vue'
import { useAuth } from '../stores/auth'
import { notify } from '../stores/toast'

const router = useRouter()
const { register } = useAuth()

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'siswa',
})

const errors = ref({})
const formError = ref('')
const submitting = ref(false)
const showPassword = ref(false)

const roles = [
  {
    value: 'guru',
    title: 'Guru',
    description: 'Buat, ubah, dan hapus tugas kelas.',
    icon: 'book-open',
  },
  {
    value: 'siswa',
    title: 'Siswa',
    description: 'Lihat tugas dan tandai yang sudah selesai.',
    icon: 'graduation',
  },
]

function errorFor(field) {
  const error = errors.value[field]

  if (Array.isArray(error)) {
    return error[0] || ''
  }

  return error || ''
}

async function submit() {
  const validation = {}

  if (!form.name.trim()) {
    validation.name = 'Nama wajib diisi.'
  }

  if (!form.email.trim()) {
    validation.email = 'Email wajib diisi.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    validation.email = 'Format email tidak valid.'
  }

  if (!form.password) {
    validation.password = 'Password wajib diisi.'
  } else if (form.password.length < 6) {
    validation.password = 'Password minimal 6 karakter.'
  }

  if (!['guru', 'siswa'].includes(form.role)) {
    validation.role = 'Pilih peran terlebih dahulu.'
  }

  errors.value = validation
  formError.value = ''

  if (Object.keys(validation).length > 0) {
    return
  }

  submitting.value = true

  try {
    await register({
      name: form.name.trim(),
      email: form.email.trim(),
      password: form.password,
      role: form.role,
    })

    notify('success', 'Akun berhasil dibuat. Selamat datang!')
    await router.replace({ name: 'tasks' })
  } catch (error) {
    errors.value = error.errors || {}
    formError.value = error.message || 'Gagal mendaftar. Coba lagi.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="auth-card__heading">
      <h1>Buat akun baru</h1>
      <p>Pilih peranmu, lalu mulai kelola tugas dengan rapi.</p>
    </div>

    <div v-if="formError" class="alert alert--error" role="alert">
      <AppIcon name="alert" :size="18" />
      <span>{{ formError }}</span>
    </div>

    <form class="form" novalidate @submit.prevent="submit">
      <div class="field">
        <label for="register-name">Nama lengkap</label>
        <div class="input-wrap">
          <span class="input-icon"><AppIcon name="user" :size="18" /></span>
          <input
            id="register-name"
            v-model="form.name"
            type="text"
            autocomplete="name"
            placeholder="Nama lengkap kamu"
            :class="{ 'input--invalid': errors.name }"
          />
        </div>
        <p v-if="errorFor('name')" class="field-error">{{ errorFor('name') }}</p>
      </div>

      <div class="field">
        <label for="register-email">Email</label>
        <div class="input-wrap">
          <span class="input-icon"><AppIcon name="mail" :size="18" /></span>
          <input
            id="register-email"
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
        <label for="register-password">Password</label>
        <div class="input-wrap">
          <span class="input-icon"><AppIcon name="lock" :size="18" /></span>
          <input
            id="register-password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            placeholder="Minimal 6 karakter"
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

      <div class="field">
        <span class="field-label">Daftar sebagai</span>
        <div class="role-options" role="radiogroup" aria-label="Pilih peran">
          <label
            v-for="role in roles"
            :key="role.value"
            class="role-card"
            :class="{ 'role-card--active': form.role === role.value }"
          >
            <input v-model="form.role" type="radio" name="role" :value="role.value" />
            <span class="role-card__icon"><AppIcon :name="role.icon" :size="20" /></span>
            <span class="role-card__text">
              <strong>{{ role.title }}</strong>
              <span>{{ role.description }}</span>
            </span>
            <span class="role-card__check"><AppIcon name="check" :size="14" /></span>
          </label>
        </div>
        <p v-if="errorFor('role')" class="field-error">{{ errorFor('role') }}</p>
      </div>

      <button type="submit" class="btn btn--primary btn--block" :disabled="submitting">
        <span v-if="submitting" class="spinner spinner--sm"></span>
        <template v-else>
          Daftar
          <AppIcon name="arrow-right" :size="18" />
        </template>
        <span v-if="submitting">Memproses...</span>
      </button>
    </form>

    <p class="auth-card__switch">
      Sudah punya akun?
      <RouterLink :to="{ name: 'login' }">Masuk di sini</RouterLink>
    </p>
  </AuthLayout>
</template>

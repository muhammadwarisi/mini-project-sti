<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from './stores/auth'
import ToastHost from './components/ToastHost.vue'

const route = useRoute()
const router = useRouter()
const { state, refreshUser, logout } = useAuth()

onMounted(async () => {
  if (!state.token) {
    return
  }

  try {
    await refreshUser()
  } catch {
    logout()

    if (route.meta.requiresAuth) {
      router.replace({ name: 'login' })
    }
  }
})
</script>

<template>
  <RouterView />
  <ToastHost />
</template>

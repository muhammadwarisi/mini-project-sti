<script setup>
import { useToasts } from '../stores/toast'
import AppIcon from './AppIcon.vue'

const { toasts, dismissToast } = useToasts()

const iconFor = (type) => {
  if (type === 'success') {
    return 'check-circle'
  }

  if (type === 'error') {
    return 'alert'
  }

  return 'info'
}
</script>

<template>
  <div class="toast-host" aria-live="polite" aria-atomic="false">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast"
        :class="`toast--${toast.type}`"
        role="status"
      >
        <span class="toast__icon">
          <AppIcon :name="iconFor(toast.type)" :size="19" />
        </span>
        <p class="toast__message">{{ toast.message }}</p>
        <button
          type="button"
          class="toast__close"
          aria-label="Tutup notifikasi"
          @click="dismissToast(toast.id)"
        >
          <AppIcon name="x" :size="16" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

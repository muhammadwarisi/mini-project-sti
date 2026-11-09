<script setup>
import { onBeforeUnmount, onMounted, watch } from 'vue'
import AppIcon from './AppIcon.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Konfirmasi' },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Hapus' },
  cancelLabel: { type: String, default: 'Batal' },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['confirm', 'cancel'])

watch(
  () => props.open,
  (open) => {
    document.body.classList.toggle('no-scroll', open)
  },
)

function cancel() {
  if (!props.loading) {
    emit('cancel')
  }
}

function onKeydown(event) {
  if (event.key === 'Escape' && props.open) {
    cancel()
  }
}

onMounted(() => window.addEventListener('keydown', onKeydown))

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
  document.body.classList.remove('no-scroll')
})
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="open" class="modal-overlay" @mousedown.self="cancel">
        <div class="modal modal--compact" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title">
          <div class="confirm-icon">
            <AppIcon name="trash" :size="22" />
          </div>
          <h2 id="confirm-title">{{ title }}</h2>
          <p class="confirm-message">{{ message }}</p>
          <footer class="modal__footer modal__footer--center">
            <button type="button" class="btn btn--soft" :disabled="loading" @click="cancel">
              {{ cancelLabel }}
            </button>
            <button type="button" class="btn btn--danger" :disabled="loading" @click="$emit('confirm')">
              <span v-if="loading" class="spinner spinner--sm"></span>
              <AppIcon v-else name="trash" :size="16" />
              {{ loading ? 'Menghapus...' : confirmLabel }}
            </button>
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

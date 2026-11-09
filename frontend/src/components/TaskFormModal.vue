<script setup>
import { nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import AppIcon from './AppIcon.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  task: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
  serverErrors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['close', 'submit'])

const form = reactive({
  judul: '',
  deskripsi: '',
  tenggat: '',
  kelas: '',
})

const localErrors = ref({})
const judulInput = ref(null)

const isEditing = () => Boolean(props.task)

watch(
  () => props.open,
  (open) => {
    document.body.classList.toggle('no-scroll', open)

    if (!open) {
      return
    }

    form.judul = props.task?.judul || ''
    form.deskripsi = props.task?.deskripsi || ''
    form.tenggat = props.task?.tenggat || ''
    form.kelas = props.task?.kelas || ''
    localErrors.value = {}

    nextTick(() => judulInput.value?.focus())
  },
)

function errorFor(field) {
  const local = localErrors.value[field]

  if (local) {
    return local
  }

  const server = props.serverErrors?.[field]

  if (Array.isArray(server)) {
    return server[0]
  }

  return server || ''
}

function submit() {
  const validation = {}

  if (!form.judul.trim()) {
    validation.judul = 'Judul tugas wajib diisi.'
  }

  if (!form.tenggat) {
    validation.tenggat = 'Tenggat wajib ditentukan.'
  }

  if (!form.kelas.trim()) {
    validation.kelas = 'Kelas wajib diisi.'
  }

  localErrors.value = validation

  if (Object.keys(validation).length > 0) {
    return
  }

  emit('submit', {
    judul: form.judul.trim(),
    deskripsi: form.deskripsi.trim() || null,
    tenggat: form.tenggat,
    kelas: form.kelas.trim(),
  })
}

function close() {
  if (!props.submitting) {
    emit('close')
  }
}

function onKeydown(event) {
  if (event.key === 'Escape' && props.open) {
    close()
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
      <div v-if="open" class="modal-overlay" @mousedown.self="close">
        <div
          class="modal modal--wide"
          role="dialog"
          aria-modal="true"
          aria-labelledby="task-modal-title"
        >
          <header class="modal__header">
            <div>
              <p class="eyebrow">{{ isEditing() ? 'Perbarui tugas' : 'Tugas baru' }}</p>
              <h2 id="task-modal-title">{{ isEditing() ? 'Ubah detail tugas' : 'Buat tugas untuk kelas' }}</h2>
            </div>
            <button
              type="button"
              class="icon-btn"
              aria-label="Tutup dialog"
              :disabled="submitting"
              @click="close"
            >
              <AppIcon name="x" :size="18" />
            </button>
          </header>

          <form class="modal__body form" novalidate @submit.prevent="submit">
            <div class="field">
              <label for="task-judul">Judul tugas</label>
              <input
                id="task-judul"
                ref="judulInput"
                v-model="form.judul"
                type="text"
                placeholder="Contoh: Latihan Soal Aljabar"
                :class="{ 'input--invalid': errorFor('judul') }"
              />
              <p v-if="errorFor('judul')" class="field-error">{{ errorFor('judul') }}</p>
            </div>

            <div class="field">
              <label for="task-deskripsi">Deskripsi <span class="field-hint">(opsional)</span></label>
              <textarea
                id="task-deskripsi"
                v-model="form.deskripsi"
                rows="4"
                placeholder="Jelaskan instruksi atau detail tugas..."
                :class="{ 'input--invalid': errorFor('deskripsi') }"
              ></textarea>
              <p v-if="errorFor('deskripsi')" class="field-error">{{ errorFor('deskripsi') }}</p>
            </div>

            <div class="form-grid">
              <div class="field">
                <label for="task-tenggat">Tenggat</label>
                <input
                  id="task-tenggat"
                  v-model="form.tenggat"
                  type="date"
                  :class="{ 'input--invalid': errorFor('tenggat') }"
                />
                <p v-if="errorFor('tenggat')" class="field-error">{{ errorFor('tenggat') }}</p>
              </div>

              <div class="field">
                <label for="task-kelas">Kelas</label>
                <input
                  id="task-kelas"
                  v-model="form.kelas"
                  type="text"
                  placeholder="Contoh: X IPA 1"
                  :class="{ 'input--invalid': errorFor('kelas') }"
                />
                <p v-if="errorFor('kelas')" class="field-error">{{ errorFor('kelas') }}</p>
              </div>
            </div>

            <footer class="modal__footer">
              <button
                type="button"
                class="btn btn--soft"
                :disabled="submitting"
                @click="close"
              >
                Batal
              </button>
              <button type="submit" class="btn btn--primary" :disabled="submitting">
                <span v-if="submitting" class="spinner spinner--sm"></span>
                <AppIcon v-else :name="isEditing() ? 'check' : 'plus'" :size="17" />
                {{ submitting ? 'Menyimpan...' : isEditing() ? 'Simpan perubahan' : 'Buat tugas' }}
              </button>
            </footer>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

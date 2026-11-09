<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppIcon from '../components/AppIcon.vue'
import ConfirmDialog from '../components/ConfirmDialog.vue'
import PaginationBar from '../components/PaginationBar.vue'
import TaskCard from '../components/TaskCard.vue'
import TaskFormModal from '../components/TaskFormModal.vue'
import { api } from '../lib/api'
import { useAuth } from '../stores/auth'
import { notify } from '../stores/toast'

const router = useRouter()
const { state: authState, isGuru, isSiswa, logout } = useAuth()

const tasks = ref([])
const meta = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
  per_page: 9,
})

const search = ref('')
const status = ref('')
const page = ref(1)
const loading = ref(true)
const errorMessage = ref('')
const busyTaskId = ref(null)

const formOpen = ref(false)
const editingTask = ref(null)
const formSubmitting = ref(false)
const formErrors = ref({})

const deleteOpen = ref(false)
const taskToDelete = ref(null)
const deleteSubmitting = ref(false)

const statusOptions = [
  { value: '', label: 'Semua' },
  { value: 'belum', label: 'Belum selesai' },
  { value: 'selesai', label: 'Selesai' },
]

const perPage = 9
let searchTimer = null
let activeRequest = 0
let controller = null

const firstName = computed(() => authState.user?.name?.trim().split(/\s+/)[0] || 'Pengguna')

const initials = computed(() => {
  const words = (authState.user?.name || '').trim().split(/\s+/).filter(Boolean)

  if (words.length === 0) {
    return '?'
  }

  return words
    .slice(0, 2)
    .map((word) => word.charAt(0).toUpperCase())
    .join('')
})

const hasFilters = computed(() => Boolean(search.value.trim()) || Boolean(status.value))

const emptyDescription = computed(() => {
  if (hasFilters.value) {
    return 'Coba kata kunci lain atau ubah filter status tugas.'
  }

  if (isGuru.value) {
    return 'Buat tugas pertama agar siswa bisa mulai mengerjakannya.'
  }

  return 'Tugas dari guru akan muncul di sini setelah dibuat.'
})

async function fetchTasks({ silent = false } = {}) {
  const requestId = ++activeRequest

  controller?.abort()
  controller = new AbortController()

  if (!silent) {
    loading.value = true
  }

  errorMessage.value = ''

  try {
    const response = await api.get(
      '/tasks',
      {
        page: page.value,
        per_page: perPage,
        search: search.value.trim(),
        status: status.value,
      },
      { signal: controller.signal },
    )

    if (requestId !== activeRequest) {
      return
    }

    const paginator = response.data

    tasks.value = paginator.data
    meta.current_page = paginator.current_page
    meta.last_page = paginator.last_page
    meta.total = paginator.total
    meta.from = paginator.from || 0
    meta.to = paginator.to || 0
    meta.per_page = paginator.per_page

    if (paginator.last_page >= 1 && page.value > paginator.last_page) {
      page.value = paginator.last_page
      await fetchTasks({ silent: true })
    }
  } catch (error) {
    if (error.name === 'AbortError' || requestId !== activeRequest) {
      return
    }

    if (error.status === 401) {
      logout()
      notify('error', 'Sesi kamu berakhir. Silakan masuk kembali.')
      router.replace({ name: 'login' })
      return
    }

    errorMessage.value = error.message || 'Gagal memuat daftar tugas.'
  } finally {
    if (requestId === activeRequest) {
      loading.value = false
    }
  }
}

function onSearchInput() {
  window.clearTimeout(searchTimer)

  searchTimer = window.setTimeout(() => {
    page.value = 1
    fetchTasks()
  }, 350)
}

function clearSearch() {
  window.clearTimeout(searchTimer)
  search.value = ''
  page.value = 1
  fetchTasks()
}

function setStatus(value) {
  if (status.value === value) {
    return
  }

  status.value = value
  page.value = 1
  fetchTasks()
}

function resetFilters() {
  window.clearTimeout(searchTimer)
  search.value = ''
  status.value = ''
  page.value = 1
  fetchTasks()
}

function changePage(nextPage) {
  if (nextPage === page.value || nextPage < 1 || nextPage > meta.last_page) {
    return
  }

  page.value = nextPage
  fetchTasks()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openCreate() {
  editingTask.value = null
  formErrors.value = {}
  formOpen.value = true
}

function openEdit(task) {
  editingTask.value = task
  formErrors.value = {}
  formOpen.value = true
}

function closeForm() {
  if (formSubmitting.value) {
    return
  }

  formOpen.value = false
  editingTask.value = null
  formErrors.value = {}
}

async function submitTask(payload) {
  formSubmitting.value = true
  formErrors.value = {}

  try {
    if (editingTask.value) {
      await api.put(`/tasks/${editingTask.value.id}`, payload)
      notify('success', 'Tugas berhasil diperbarui.')
    } else {
      await api.post('/tasks', payload)
      notify('success', 'Tugas baru berhasil dibuat.')
      page.value = 1
    }

    formOpen.value = false
    editingTask.value = null
    await fetchTasks({ silent: true })
  } catch (error) {
    if (error.status === 401) {
      logout()
      notify('error', 'Sesi kamu berakhir. Silakan masuk kembali.')
      router.replace({ name: 'login' })
      return
    }

    if (error.status === 422) {
      formErrors.value = error.errors || {}
      notify('error', 'Periksa kembali isian form tugas.')
    } else {
      notify('error', error.message || 'Gagal menyimpan tugas.')
    }
  } finally {
    formSubmitting.value = false
  }
}

function requestDelete(task) {
  taskToDelete.value = task
  deleteOpen.value = true
}

function cancelDelete() {
  if (deleteSubmitting.value) {
    return
  }

  deleteOpen.value = false
  taskToDelete.value = null
}

async function confirmDelete() {
  if (!taskToDelete.value) {
    return
  }

  deleteSubmitting.value = true

  try {
    await api.delete(`/tasks/${taskToDelete.value.id}`)
    notify('success', 'Tugas berhasil dihapus.')
    deleteOpen.value = false
    taskToDelete.value = null

    if (tasks.value.length === 1 && page.value > 1) {
      page.value -= 1
    }

    await fetchTasks({ silent: true })
  } catch (error) {
    if (error.status === 401) {
      logout()
      notify('error', 'Sesi kamu berakhir. Silakan masuk kembali.')
      router.replace({ name: 'login' })
      return
    }

    notify('error', error.message || 'Gagal menghapus tugas.')
  } finally {
    deleteSubmitting.value = false
  }
}

async function toggleComplete(task) {
  busyTaskId.value = task.id

  try {
    const response = await api.patch(`/tasks/${task.id}/complete`)
    const updated = response.data

    if (status.value && updated.status !== status.value) {
      if (tasks.value.length === 1 && page.value > 1) {
        page.value -= 1
      }

      await fetchTasks({ silent: true })
    } else {
      tasks.value = tasks.value.map((item) => (item.id === updated.id ? updated : item))
    }

    notify(
      'success',
      updated.status === 'selesai'
        ? 'Tugas ditandai selesai.'
        : 'Tugas ditandai belum selesai.',
    )
  } catch (error) {
    if (error.status === 401) {
      logout()
      notify('error', 'Sesi kamu berakhir. Silakan masuk kembali.')
      router.replace({ name: 'login' })
      return
    }

    notify('error', error.message || 'Gagal memperbarui status tugas.')
  } finally {
    busyTaskId.value = null
  }
}

async function handleLogout() {
  logout()
  notify('info', 'Kamu telah keluar dari akun.')
  await router.replace({ name: 'login' })
}

onMounted(() => fetchTasks())

onBeforeUnmount(() => {
  window.clearTimeout(searchTimer)
  controller?.abort()
})
</script>

<template>
  <div class="app-shell">
    <header class="topbar">
      <div class="topbar__inner">
        <RouterLink :to="{ name: 'tasks' }" class="brand">
          <span class="brand-mark">
            <AppIcon name="check" :size="20" />
          </span>
          <span class="brand-name">TugasKita</span>
        </RouterLink>

        <div class="topbar__right">
          <div class="user-chip">
            <span class="avatar">{{ initials }}</span>
            <span class="user-chip__text">
              <strong>{{ authState.user?.name }}</strong>
              <span
                class="role-badge"
                :class="isGuru ? 'role-badge--guru' : 'role-badge--siswa'"
              >
                <AppIcon :name="isGuru ? 'book-open' : 'graduation'" :size="12" />
                {{ isGuru ? 'Guru' : 'Siswa' }}
              </span>
            </span>
          </div>

          <button type="button" class="btn btn--ghost btn--sm topbar__logout" @click="handleLogout">
            <AppIcon name="logout" :size="17" />
            <span>Keluar</span>
          </button>
        </div>
      </div>
    </header>

    <main class="page">
      <section class="page__heading">
        <div class="page__intro">
          <p class="eyebrow">{{ isGuru ? 'Ruang kerja guru' : 'Ruang belajar siswa' }}</p>
          <h1>Halo, {{ firstName }}</h1>
          <p class="page__subtitle">
            {{
              isGuru
                ? 'Kelola tugas kelas, pantau statusnya, dan pastikan tidak ada yang terlewat.'
                : 'Lihat daftar tugasmu dan tandai tugas yang sudah selesai dikerjakan.'
            }}
          </p>
        </div>

        <button v-if="isGuru" type="button" class="btn btn--primary page__cta" @click="openCreate">
          <AppIcon name="plus" :size="18" />
          Buat tugas
        </button>
      </section>

      <section class="toolbar" aria-label="Pencarian dan filter tugas">
        <div class="search-field">
          <span class="search-field__icon">
            <AppIcon name="search" :size="18" />
          </span>
          <input
            v-model="search"
            type="search"
            placeholder="Cari judul tugas..."
            aria-label="Cari tugas berdasarkan judul"
            @input="onSearchInput"
          />
          <button
            v-if="search"
            type="button"
            class="search-field__clear"
            aria-label="Hapus pencarian"
            @click="clearSearch"
          >
            <AppIcon name="x" :size="16" />
          </button>
        </div>

        <div class="filter-tabs" role="group" aria-label="Filter status tugas">
          <button
            v-for="option in statusOptions"
            :key="option.value"
            type="button"
            class="filter-tab"
            :class="{ 'filter-tab--active': status === option.value }"
            :aria-pressed="status === option.value"
            @click="setStatus(option.value)"
          >
            <AppIcon
              :name="option.value === 'belum' ? 'clock' : option.value === 'selesai' ? 'check-circle' : 'filter'"
              :size="15"
            />
            {{ option.label }}
          </button>
        </div>
      </section>

      <div v-if="errorMessage" class="alert alert--error alert--page" role="alert">
        <AppIcon name="alert" :size="18" />
        <span>{{ errorMessage }}</span>
        <button type="button" class="alert__action" @click="fetchTasks()">Coba lagi</button>
      </div>

      <div v-if="loading" class="task-grid" aria-busy="true" aria-label="Memuat daftar tugas">
        <div v-for="item in 6" :key="item" class="skeleton-card">
          <div class="skeleton skeleton--chip"></div>
          <div class="skeleton skeleton--title"></div>
          <div class="skeleton skeleton--line"></div>
          <div class="skeleton skeleton--line skeleton--short"></div>
          <div class="skeleton skeleton--footer"></div>
        </div>
      </div>

      <template v-else>
        <div v-if="tasks.length > 0" class="task-grid">
          <TaskCard
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            :can-manage="isGuru"
            :can-complete="isSiswa"
            :busy="busyTaskId === task.id"
            @edit="openEdit"
            @delete="requestDelete"
            @toggle="toggleComplete"
          />
        </div>

        <div v-else class="empty-state">
          <span class="empty-state__icon">
            <AppIcon :name="hasFilters ? 'search' : 'inbox'" :size="28" />
          </span>
          <h2>{{ hasFilters ? 'Tidak ada tugas yang cocok' : 'Belum ada tugas' }}</h2>
          <p>{{ emptyDescription }}</p>
          <button v-if="hasFilters" type="button" class="btn btn--soft" @click="resetFilters">
            Reset pencarian dan filter
          </button>
          <button v-else-if="isGuru" type="button" class="btn btn--primary" @click="openCreate">
            <AppIcon name="plus" :size="17" />
            Buat tugas pertama
          </button>
        </div>

        <PaginationBar
          v-if="tasks.length > 0"
          :current-page="meta.current_page"
          :last-page="meta.last_page"
          :total="meta.total"
          :from="meta.from"
          :to="meta.to"
          @change="changePage"
        />
      </template>
    </main>

    <TaskFormModal
      :open="formOpen"
      :task="editingTask"
      :submitting="formSubmitting"
      :server-errors="formErrors"
      @close="closeForm"
      @submit="submitTask"
    />

    <ConfirmDialog
      :open="deleteOpen"
      title="Hapus tugas ini?"
      :message="
        taskToDelete
          ? `Tugas “${taskToDelete.judul}” akan dihapus permanen dan tidak bisa dikembalikan.`
          : ''
      "
      confirm-label="Hapus tugas"
      :loading="deleteSubmitting"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </div>
</template>

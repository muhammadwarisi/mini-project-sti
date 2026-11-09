<script setup>
import { computed } from 'vue'
import AppIcon from './AppIcon.vue'

const props = defineProps({
  task: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
  canComplete: { type: Boolean, default: false },
  busy: { type: Boolean, default: false },
})

defineEmits(['edit', 'delete', 'toggle'])

const isDone = computed(() => props.task.status === 'selesai')

const deadlineLabel = computed(() => {
  if (!props.task.tenggat) {
    return 'Tanpa tenggat'
  }

  const date = new Date(`${props.task.tenggat}T00:00:00`)

  if (Number.isNaN(date.getTime())) {
    return props.task.tenggat
  }

  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
})

const isOverdue = computed(() => {
  if (isDone.value || !props.task.tenggat) {
    return false
  }

  const deadline = new Date(`${props.task.tenggat}T23:59:59`)

  return !Number.isNaN(deadline.getTime()) && deadline < new Date()
})
</script>

<template>
  <article class="task-card" :class="{ 'task-card--done': isDone }">
    <div class="task-card__top">
      <span
        class="status-badge"
        :class="isDone ? 'status-badge--done' : 'status-badge--pending'"
      >
        <AppIcon :name="isDone ? 'check-circle' : 'clock'" :size="14" />
        {{ isDone ? 'Selesai' : 'Belum selesai' }}
      </span>
      <span class="deadline" :class="{ 'deadline--overdue': isOverdue }">
        <AppIcon name="calendar" :size="14" />
        {{ deadlineLabel }}
      </span>
    </div>

    <h2 class="task-card__title">{{ task.judul }}</h2>

    <p v-if="task.deskripsi" class="task-card__desc">{{ task.deskripsi }}</p>
    <p v-else class="task-card__desc task-card__desc--muted">Tidak ada deskripsi.</p>

    <div class="task-card__meta">
      <span class="class-chip">
        <AppIcon name="graduation" :size="14" />
        {{ task.kelas }}
      </span>
      <span v-if="isOverdue" class="overdue-note">Lewat tenggat</span>
    </div>

    <div class="task-card__actions">
      <template v-if="canManage">
        <button type="button" class="btn btn--soft btn--sm" @click="$emit('edit', task)">
          <AppIcon name="edit" :size="16" />
          Ubah
        </button>
        <button
          type="button"
          class="btn btn--danger-ghost btn--sm"
          @click="$emit('delete', task)"
        >
          <AppIcon name="trash" :size="16" />
          Hapus
        </button>
      </template>

      <button
        v-else-if="canComplete"
        type="button"
        class="btn btn--sm"
        :class="isDone ? 'btn--soft' : 'btn--primary'"
        :disabled="busy"
        @click="$emit('toggle', task)"
      >
        <span v-if="busy" class="spinner spinner--sm"></span>
        <AppIcon v-else :name="isDone ? 'rotate' : 'check'" :size="16" />
        {{ busy ? 'Menyimpan...' : isDone ? 'Tandai belum' : 'Tandai selesai' }}
      </button>
    </div>
  </article>
</template>

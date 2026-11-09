<script setup>
import { computed } from 'vue'
import AppIcon from './AppIcon.vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
  total: { type: Number, default: 0 },
  from: { type: Number, default: 0 },
  to: { type: Number, default: 0 },
})

const emit = defineEmits(['change'])

const pages = computed(() => {
  const total = props.lastPage
  const current = props.currentPage

  if (total <= 7) {
    return Array.from({ length: total }, (_, index) => index + 1)
  }

  const candidates = [1, total, current - 2, current - 1, current, current + 1, current + 2]
  const unique = [...new Set(candidates)]
    .filter((page) => page >= 1 && page <= total)
    .sort((a, b) => a - b)

  const result = []
  let previous = 0

  unique.forEach((page) => {
    if (page - previous > 1) {
      result.push('...')
    }

    result.push(page)
    previous = page
  })

  return result
})

const rangeLabel = computed(() => {
  if (props.total === 0) {
    return 'Tidak ada tugas untuk ditampilkan'
  }

  return `Menampilkan ${props.from}–${props.to} dari ${props.total} tugas`
})

function goTo(page) {
  if (page === '...' || page === props.currentPage) {
    return
  }

  emit('change', page)
}
</script>

<template>
  <div class="pagination">
    <p class="pagination__info">{{ rangeLabel }}</p>

    <nav class="pagination__nav" aria-label="Navigasi halaman">
      <button
        type="button"
        class="page-btn page-btn--arrow"
        :disabled="currentPage <= 1"
        aria-label="Halaman sebelumnya"
        @click="goTo(currentPage - 1)"
      >
        <AppIcon name="chevron-left" :size="16" />
      </button>

      <template v-for="(page, index) in pages" :key="`${page}-${index}`">
        <span v-if="page === '...'" class="page-ellipsis">…</span>
        <button
          v-else
          type="button"
          class="page-btn"
          :class="{ 'page-btn--active': page === currentPage }"
          :aria-current="page === currentPage ? 'page' : undefined"
          @click="goTo(page)"
        >
          {{ page }}
        </button>
      </template>

      <button
        type="button"
        class="page-btn page-btn--arrow"
        :disabled="currentPage >= lastPage"
        aria-label="Halaman berikutnya"
        @click="goTo(currentPage + 1)"
      >
        <AppIcon name="chevron-right" :size="16" />
      </button>
    </nav>
  </div>
</template>

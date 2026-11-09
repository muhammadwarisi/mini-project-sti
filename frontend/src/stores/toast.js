import { reactive } from 'vue'

const toasts = reactive([])
let nextId = 0

export function notify(type, message, duration = 4500) {
  const id = ++nextId
  toasts.push({ id, type, message })

  if (duration > 0) {
    window.setTimeout(() => dismissToast(id), duration)
  }

  return id
}

export function dismissToast(id) {
  const index = toasts.findIndex((toast) => toast.id === id)

  if (index !== -1) {
    toasts.splice(index, 1)
  }
}

export function useToasts() {
  return { toasts, dismissToast }
}

<template>
  <div class="toast-container">
    <Toast
      v-for="toast in toasts"
      :key="toast.id"
      :message="toast.message"
      :type="toast.type"
      :duration="toast.duration"
      :meta="toast.meta"
      @closed="removeToast(toast.id, $event)"
    />
  </div>
</template>

<script>
import Toast from './Toast.vue'

export default {
  name: 'ToastContainer',
  components: { Toast },
  data() {
    return { toasts: [] }
  },
  methods: {
    addToast(message, type = 'error', duration = 4000, meta = {}) {
      const id = Date.now() + Math.random()
      this.toasts.push({ id, message, type, duration, meta })
      if (this.toasts.length > 5) this.toasts.shift()
      return id
    },
    removeToast(id, payload) {
      const index = this.toasts.findIndex(t => t.id === id)
      if (index !== -1) {
        const toast = this.toasts[index]
        if (typeof toast?.meta?.resolve === 'function') {
          toast.meta.resolve(Boolean(payload?.confirmed))
        }
        this.toasts.splice(index, 1)
      }
    },
    error(message, duration) { return this.addToast(message, 'error', duration) },
    success(message, duration) { return this.addToast(message, 'success', duration) },
    warning(message, duration) { return this.addToast(message, 'warning', duration) },
    info(message, duration) { return this.addToast(message, 'info', duration) }
  }
}
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 9999;
  pointer-events: none;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 100%;
}

.toast-container > * {
  pointer-events: auto;
}
</style>
<template>
  <div class="toast-container">
    <Toast
      v-for="toast in toasts"
      :key="toast.id"
      :message="toast.message"
      :type="toast.type"
      :duration="toast.duration"
      @closed="removeToast(toast.id)"
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
    addToast(message, type = 'error', duration = 4000) {
      const id = Date.now() + Math.random()
      this.toasts.push({ id, message, type, duration })
      if (this.toasts.length > 5) this.toasts.shift()
      return id
    },
    removeToast(id) {
      const index = this.toasts.findIndex(t => t.id === id)
      if (index !== -1) this.toasts.splice(index, 1)
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
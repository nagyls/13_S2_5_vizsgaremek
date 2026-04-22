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

// Konstansok a könnyebb módosíthatóság érdekében
const MAX_CONCURRENT_TOASTS = 5;
const DEFAULT_NOTIFICATION_DURATION = 4000;

export default {
  name: 'ToastContainer',
  components: { Toast },
  data() {
    return { 
      toasts: [] 
    }
  },
  methods: {
    addToast(message, type = 'error', duration = DEFAULT_NOTIFICATION_DURATION, meta = {}) {
      const id = Date.now() + Math.random();
      const newToast = { id, message, type, duration, meta };

      this.toasts.push(newToast);

      // Korlátozzuk az egyszerre látható üzenetek számát
      if (this.toasts.length > MAX_CONCURRENT_TOASTS) {
        this.toasts.shift();
      }

      return id;
    },

    removeToast(id, payload) {
      const toastIndex = this.toasts.findIndex(t => t.id === id);
      if (toastIndex === -1) return;

      const toast = this.toasts[toastIndex];
      this.resolveToastPromise(toast, payload);
      this.toasts.splice(toastIndex, 1);
    },

    // Kezeli a megerősítő (confirm) ablakok aszinkron válaszát
    resolveToastPromise(toast, payload) {
      if (toast.meta && typeof toast.meta.resolve === 'function') {
        toast.meta.resolve(Boolean(payload?.confirmed));
      }
    },

    error(message, duration) { 
      return this.addToast(message, 'error', duration); 
    },

    success(message, duration) { 
      return this.addToast(message, 'success', duration); 
    },

    warning(message, duration) { 
      return this.addToast(message, 'warning', duration); 
    },

    info(message, duration) { 
      return this.addToast(message, 'info', duration); 
    }
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
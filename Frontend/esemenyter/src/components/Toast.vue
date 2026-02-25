<template>
  <Transition name="toast">
    <div v-if="visible" class="toast" :class="typeClass">
      <div class="toast-icon">
        <i :class="iconClass"></i>
      </div>
      <div class="toast-content">
        <div class="toast-message">{{ message }}</div>
      </div>
      <button class="toast-close" @click="close">
        <i class='bx bx-x'></i>
      </button>
    </div>
  </Transition>
</template>

<script>
export default {
  name: 'Toast',
  props: {
    message: { type: String, required: true },
    type: { type: String, default: 'error' },
    duration: { type: Number, default: 4000 }
  },
  data() {
    return { visible: true }
  },
  computed: {
    typeClass() {
      return {
        'toast-error': this.type === 'error',
        'toast-success': this.type === 'success',
        'toast-warning': this.type === 'warning',
        'toast-info': this.type === 'info'
      }
    },
    iconClass() {
      const icons = {
        error: 'bx bx-error-circle',
        success: 'bx bx-check-circle',
        warning: 'bx bx-error',
        info: 'bx bx-info-circle'
      }
      return icons[this.type] || icons.error
    }
  },
  mounted() {
    if (this.duration > 0) {
      setTimeout(() => this.close(), this.duration)
    }
  },
  methods: {
    close() {
      this.visible = false
      setTimeout(() => this.$emit('closed'), 300)
    }
  }
}
</script>

<style scoped>
.toast {
  position: relative;
  min-width: 320px;
  max-width: 450px;
  background: white;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  border-left: 6px solid;
  margin-bottom: 10px;
}

.toast-error { background: #fff5f5; border-left-color: #ef4444; }
.toast-success { background: #f0fdf4; border-left-color: #10b981; }
.toast-warning { background: #fffbeb; border-left-color: #f59e0b; }
.toast-info { background: #eff6ff; border-left-color: #3b82f6; }

.toast-error .toast-icon { color: #ef4444; }
.toast-success .toast-icon { color: #10b981; }
.toast-warning .toast-icon { color: #f59e0b; }
.toast-info .toast-icon { color: #3b82f6; }

.toast-icon { font-size: 24px; flex-shrink: 0; }
.toast-content { flex: 1; }
.toast-message { color: #1f2937; font-size: 15px; line-height: 1.5; font-weight: 500; }

.toast-close {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
}

.toast-close:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #4b5563;
}

.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(100%); }

@media (max-width: 768px) {
  .toast {
    left: 20px;
    right: 20px;
    min-width: auto;
    max-width: none;
  }
}
</style>
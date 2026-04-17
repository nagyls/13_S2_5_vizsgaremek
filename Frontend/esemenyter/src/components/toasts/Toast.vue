<template>
  <Transition name="toast">
    <div v-if="visible" class="toast" :class="typeClass">
      <div class="toast-icon">
        <i :class="iconClass"></i>
      </div>
      <div class="toast-content">
        <div class="toast-message">{{ message }}</div>
        <div v-if="type === 'confirm'" class="toast-actions">
          <button class="toast-action cancel" @click="cancel">{{ meta.cancelText || 'Mégse' }}</button>
          <button class="toast-action confirm" @click="confirm">{{ meta.confirmText || 'Igen' }}</button>
        </div>
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
    message: { 
      type: String, 
      required: true 
    },
    type: { 
      type: String, 
      default: 'error',
      validator: (value) => ['error', 'success', 'warning', 'info', 'confirm'].includes(value)
    },
    duration: { 
      type: Number, 
      default: 4000 
    },
    meta: { 
      type: Object, 
      default: () => ({}) 
    }
  },
  data() {
    return { 
      visible: true,
      closeTimer: null
    }
  },
  computed: {
    typeClass() {
      return {
        'toast-error': this.type === 'error',
        'toast-success': this.type === 'success',
        'toast-warning': this.type === 'warning',
        'toast-info': this.type === 'info',
        'toast-confirm': this.type === 'confirm'
      }
    },
    iconClass() {
      const icons = {
        error: 'bx bx-error-circle',
        success: 'bx bx-check-circle',
        warning: 'bx bx-error',
        info: 'bx bx-info-circle',
        confirm: 'bx bx-help-circle'
      }
      return icons[this.type] || icons.error
    }
  },
  mounted() {
    this.startAutoCloseTimer();
  },
  beforeUnmount() {
    // Memóriaszivárgás megelőzése az aktív időzítő törlésével
    if (this.closeTimer) {
      clearTimeout(this.closeTimer);
    }
  },
  methods: {
    startAutoCloseTimer() {
      if (this.duration > 0) {
        this.closeTimer = setTimeout(() => this.close(), this.duration);
      }
    },
    confirm() {
      this.closeWith(true)
    },
    cancel() {
      this.closeWith(false)
    },
    close() {
      this.closeWith(false)
    },
    closeWith(confirmed) {
      this.visible = false
      // Megvárjuk az áttűnési animációt (300ms), mielőtt kiváltjuk az eseményt a szülőnek
      setTimeout(() => this.$emit('closed', { confirmed }), 300)
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

.toast-actions {
  margin-top: 10px;
  display: flex;
  gap: 8px;
}

.toast-action {
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.toast-action.cancel {
  background: #e5e7eb;
  color: #374151;
}

.toast-action.confirm {
  background: #4f46e5;
  color: white;
}

.toast-confirm { 
  background: #f8f9ff; 
  border-left-color: #4f46e5; 
}
.toast-confirm .toast-icon {
  color: #4f46e5; 
}

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

.toast-enter-active, .toast-leave-active {
 transition: all 0.3s ease; 
 }
.toast-enter-from, .toast-leave-to {
  opacity: 0; 
  transform: translateX(100%);
}

@media (max-width: 768px) {
  .toast {
    left: 20px;
    right: 20px;
    min-width: auto;
    max-width: none;
  }
}
</style>
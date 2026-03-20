let toastContainer = null

export function setToastContainer(container) {
  toastContainer = container
}

export function showToast(message, type = 'error', duration = 4000, meta = {}) {
  if (toastContainer) {
    return toastContainer.addToast(message, type, duration, meta)
  }
  console.warn('Toast container not initialized')
  return null
}

export const toast = {
  error: (message, duration) => showToast(message, 'error', duration),
  success: (message, duration) => showToast(message, 'success', duration),
  warning: (message, duration) => showToast(message, 'warning', duration),
  info: (message, duration) => showToast(message, 'info', duration),
  confirm: (message, options = {}) => new Promise(resolve => {
    showToast(message, 'confirm', 0, {
      resolve,
      confirmText: options.confirmText || 'Igen',
      cancelText: options.cancelText || 'Mégse'
    })
  })
}

export default toast
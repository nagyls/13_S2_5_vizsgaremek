let toastContainer = null

export function setToastContainer(container) {
  toastContainer = container
}

export function showToast(message, type = 'error', duration = 4000) {
  if (toastContainer) {
    return toastContainer.addToast(message, type, duration)
  }
  console.warn('Toast container not initialized')
  return null
}

export const toast = {
  error: (message, duration) => showToast(message, 'error', duration),
  success: (message, duration) => showToast(message, 'success', duration),
  warning: (message, duration) => showToast(message, 'warning', duration),
  info: (message, duration) => showToast(message, 'info', duration)
}

export default toast
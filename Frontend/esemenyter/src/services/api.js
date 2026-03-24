export const API_BASE = 'http://127.0.0.1:8000/api'

export function getToken() {
  return localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token')
}

export function hasLocalToken() {
  return Boolean(localStorage.getItem('esemenyter_token'))
}

export function getAuthHeaders(token = getToken(), withJsonBody = false) {
  return {
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...(withJsonBody ? { 'Content-Type': 'application/json' } : {})
  }
}

export function clearAuthStorage() {
  localStorage.removeItem('esemenyter_user')
  localStorage.removeItem('esemenyter_token')
  localStorage.removeItem('CurrentInstitution')
  localStorage.removeItem('remember_me')

  sessionStorage.removeItem('esemenyter_user')
  sessionStorage.removeItem('esemenyter_token')
  sessionStorage.removeItem('CurrentInstitution')
}

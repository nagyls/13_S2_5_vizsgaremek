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

/**
 * Lekéri az aktuálisan kiválasztott intézmény azonosítóját.
 *
 * @param {Object|null} user Az aktuális felhasználó objektum (opcionális fallback-nek).
 * @returns {number|null} Az intézmény azonosítója vagy null.
 */
export function getCurrentInstitutionId(user = null) {
  const storedInstitutionId =
    localStorage.getItem('CurrentInstitution') ||
    sessionStorage.getItem('CurrentInstitution') ||
    user?.institution_id ||
    user?.establishment_id

  const institutionId = Number(storedInstitutionId)
  return Number.isFinite(institutionId) && institutionId > 0 ? institutionId : null
}

/**
 * Esemény státuszának normalizálása
 */
export function normalizeEventStatus(status) {
  const normalized = String(status || '').toLowerCase()

  if (normalized === 'ongoing' || normalized === 'open' || normalized === 'upcoming') {
    return 'open'
  }

  if (normalized === 'ended' || normalized === 'closed') {
    return 'closed'
  }

  return 'open'
}

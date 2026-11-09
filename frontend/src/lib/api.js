export const TOKEN_KEY = 'tugaskita.token'
export const USER_KEY = 'tugaskita.user'

const BASE_URL = (import.meta.env.VITE_API_URL || '/api').replace(/\/+$/, '')

export class ApiError extends Error {
  constructor(message, status = 0, errors = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors || {}
  }
}

function buildUrl(path, params) {
  if (!params) {
    return `${BASE_URL}${path}`
  }

  const query = new URLSearchParams()

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      query.set(key, value)
    }
  })

  const queryString = query.toString()

  return queryString ? `${BASE_URL}${path}?${queryString}` : `${BASE_URL}${path}`
}

async function request(path, { method = 'GET', body, params, signal } = {}) {
  const headers = { Accept: 'application/json' }
  const token = localStorage.getItem(TOKEN_KEY)

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  let payload = body

  if (body !== undefined && !(body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
    payload = JSON.stringify(body)
  }

  let response

  try {
    response = await fetch(buildUrl(path, params), {
      method,
      headers,
      body: payload,
      signal,
    })
  } catch (error) {
    if (error.name === 'AbortError') {
      throw error
    }

    throw new ApiError(
      'Tidak dapat terhubung ke server. Pastikan backend Laravel sudah berjalan.',
      0,
    )
  }

  const text = await response.text()
  let data = null

  if (text) {
    try {
      data = JSON.parse(text)
    } catch {
      data = null
    }
  }

  if (!response.ok) {
    let message = data?.message

    if (response.status === 401) {
      message = 'Sesi kamu sudah berakhir. Silakan masuk kembali.'
    } else if (response.status === 403) {
      message = 'Kamu tidak memiliki akses untuk tindakan ini.'
    } else if (response.status >= 500) {
      message = 'Terjadi kesalahan pada server. Coba lagi sebentar lagi.'
    } else if (!message) {
      message = 'Permintaan tidak dapat diproses. Coba lagi.'
    }

    throw new ApiError(message, response.status, data?.errors)
  }

  return data
}

export const api = {
  get: (path, params, options = {}) => request(path, { ...options, params }),
  post: (path, body, options = {}) => request(path, { ...options, method: 'POST', body }),
  put: (path, body, options = {}) => request(path, { ...options, method: 'PUT', body }),
  patch: (path, body, options = {}) => request(path, { ...options, method: 'PATCH', body }),
  delete: (path, options = {}) => request(path, { ...options, method: 'DELETE' }),
}

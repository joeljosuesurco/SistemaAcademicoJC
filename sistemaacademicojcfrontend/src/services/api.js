import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
})

// Inyectar token antes de cada solicitud
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Interceptor de respuesta: manejar errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      localStorage.removeItem('token')
      delete api.defaults.headers.common['Authorization']
      window.location.href = '/login' // importante: usa hash si usas HashHistory: '/#/login'
    }

    if (status >= 500) {
      console.error('Error del servidor:', error.response?.data?.message || error.message)
    }

    return Promise.reject(error)
  },
)

export default api

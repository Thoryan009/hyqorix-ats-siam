import axios from 'axios'
import app from '@/shared/config/appConfig'
import router from '@/router' // 👈 import your router
import  queryClient  from '@/config/queryClient'

const axiosInstance = axios.create({
  baseURL: app.apiUrl,
  headers: {
    Accept: 'application/json',
  },
})

// Request interceptor → attach token
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem(app.tokenKey)
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error),
)

// ✅ Response interceptor → handle unauthorized
axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem(app.tokenKey)
      queryClient.clear()

      router.replace({
        name: 'Login',
        query: { unauthorized: 'true' },
      })
    }

    return Promise.reject(error)
  },
)


export default axiosInstance

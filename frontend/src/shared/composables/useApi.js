import { ref } from 'vue'
import axiosInstance from '@/config/axiosConfig'

export function useApi() {
  const data = ref(null)
  const error = ref(null)

  const sendRequest = async (url, method = 'GET', payload = null, config = {}) => {
    error.value = null

    try {
      let response
      switch (method.toUpperCase()) {
        case 'GET':
          response = await axiosInstance.get(url, config)
          break
        case 'POST':
          response = await axiosInstance.post(url, payload, config)
          break
        case 'PUT':
          response = await axiosInstance.put(url, payload, config)
          break
        case 'DELETE':
          response = await axiosInstance.delete(url, config)
          break
      }

      data.value = response.data
    } catch (err) {
      error.value = err.response ? err.response.data : err.message
    }
  }

  return { data, error, sendRequest }
}

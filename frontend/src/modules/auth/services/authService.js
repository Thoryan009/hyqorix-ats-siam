import { useApi } from '@/shared/composables/useApi'

const BASE_URL = '/auth'

export async function loginUser(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL + '/login', 'POST', payload)
  if (api.error.value) {
    throw api.error.value // 🚨 throw error so useMutation.onError triggers
  }
  return api.data.value
}

export async function logoutUser() {
  const api = useApi()
  await api.sendRequest(BASE_URL + '/logout', 'POST')
  if (api.error.value) {
    throw api.error.value // 🚨 throw error so useMutation.onError triggers
  }
  return api.data.value
}

export async function getUser() {
  const api = useApi()
  await api.sendRequest(BASE_URL + '/user')
  if (api.error.value) {
    throw api.error.value // 🚨 throw error so useQuery.onError triggers
  }
  return api.data.value
}

export async function refreshData() {
  const api = useApi()
  await api.sendRequest(BASE_URL + '/refresh', 'POST')
  if (api.error.value) {
    throw api.error.value // 🚨 throw error so useMutation.onError triggers
  }
  return api.data.value
}

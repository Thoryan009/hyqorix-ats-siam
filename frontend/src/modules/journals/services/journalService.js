import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/journals'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchAll(page = 1, perPage = 10, filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchJournal(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`)
  if (api.error.value) {
    throw api.error.value
  }

  return response(api)
}

export async function fetchJobOptions() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/job-options`)
  if (api.error.value) {
    throw api.error.value
  }

  return response(api)
}

export async function fetchDemandLetterOptions() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/demand-letter-options`)
  if (api.error.value) {
    throw api.error.value
  }

  return response(api)
}

export async function submitJournal(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function approveJournal(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/approve`, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function payJournal(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/pay`, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

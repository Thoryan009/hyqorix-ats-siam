import { useApi } from '@/shared/composables/useApi'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchTopEmployee() {
  const api = useApi()
  await api.sendRequest('/employees-top-performer')
  return response(api)
}

export async function fetchApprovalManagers() {
  const api = useApi()
  await api.sendRequest('/employee-approval-managers')
  return response(api)
}

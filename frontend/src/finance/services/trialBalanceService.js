import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/finance-reports/trial-balance'

export async function fetchTrialBalance(filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, filters)
  await api.sendRequest(url)
  if (api.error.value) throw api.error.value
  return api.data.value
}

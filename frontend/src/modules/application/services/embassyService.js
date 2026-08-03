import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

export async function fetchEmbassyHints(search = '', options = {}) {
  const api = useApi()
  const url = buildUrl('embassy-hints', {
    search,
    submit_date: options.submitDate || undefined,
    exclude_embassy_list_id: options.excludeEmbassyListId || undefined,
  })

  await api.sendRequest(url)

  return {
    data: api.data.value,
    error: api.error.value,
  }
}

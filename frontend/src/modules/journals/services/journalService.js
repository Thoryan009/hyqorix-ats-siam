import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'
import { hasUploadFile, toDynamicFormData } from '@/finance/utils/billEntryMapper'

const BASE_URL = '/journals'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

function resolveRequestBody(payload) {
  if (hasUploadFile(payload)) {
    return toDynamicFormData(payload)
  }
  return payload
}

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

export async function fetchPartyLedger(page = 1, perPage = 50, filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/party-ledger`, {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchGeneralLedger(page = 1, perPage = 50, filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/general-ledger`, {
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

export async function fetchSubLedgerApplicants(jobListId) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/sub-ledger-applicants`, {
    job_list_id: jobListId,
  })
  await api.sendRequest(url)
  if (api.error.value) {
    throw api.error.value
  }

  return response(api)
}

export async function fetchNarrationHints(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/narration-hints`, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value?.data?.hints ?? []
}

export async function fetchEntryChat(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/entry-chat`, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value?.data?.reply ?? ''
}

export async function submitJournal(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', resolveRequestBody(payload))
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

export async function returnJournal(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/return`, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function resubmitJournal(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/resubmit`, 'POST', resolveRequestBody(payload))
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function payJournal(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/pay`, 'POST', resolveRequestBody(payload))
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function reverseJournal(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/reverse`, 'POST')
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/applications'

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

export async function fetchEmbassySubmissions(page = 1, perPage = 10, filters = {}) {
  const api = useApi()
  const url = buildUrl('embasy-submissions', {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchEmbassySubmissiondData() {
  const api = useApi()
  await api.sendRequest('embasy-submissions-ksa-data')
  return response(api)
}

export async function fetchSingle(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`)
  return response(api)
}

export async function fetchApplicationData() {
  const api = useApi()
  await api.sendRequest('applications-data')
  return response(api)
}

export async function fetchHiringListData() {
  const api = useApi()
  await api.sendRequest('hiring-list-data')
  return response(api)
}

export async function fetchApplicationListData() {
  const api = useApi()
  await api.sendRequest('application-list-data')
  return response(api)
}

export async function fetchShortListData() {
  const api = useApi()
  await api.sendRequest('short-list-data')
  return response(api)
}

export async function fetchWaitingListData() {
  const api = useApi()
  await api.sendRequest('waiting-list-data')
  return response(api)
}

export async function fetchRejectedListData() {
  const api = useApi()
  await api.sendRequest('rejected-list-data')
  return response(api)
}

export async function fetchApplicationProcesses() {
  const api = useApi()
  await api.sendRequest('applications-processes')
  return response(api)
}

export async function submitData(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function updateData(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${payload.id}`, 'POST', payload.data, {
    headers: { 'X-HTTP-Method-Override': 'PUT' },
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}
export async function updateEmbassySubmissionData(payload) {
  const api = useApi()
  await api.sendRequest(`embasy-submissions/${payload.id}`, 'POST', payload.data, {
    headers: { 'X-HTTP-Method-Override': 'PUT' },
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function generateVisaProfessionEnglishData(payload) {
  const api = useApi()
  await api.sendRequest('embasy-submissions/generate-visa-profession-en', 'POST', payload)

  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function fetchMofaInformations(applicationId) {
  const api = useApi()
  await api.sendRequest(`embasy-submissions/${applicationId}`)
  return response(api)
}

export async function deleteEmbassySubmission(applicationId) {
  const api = useApi()
  await api.sendRequest(`embasy-submissions/${applicationId}`, 'DELETE')
  if (api.error.value) {
    throw api.error.value
  }
  return api.data.value
}

export async function bulkOfferExtendData({ jobId, processId, ids }) {
  const api = useApi()
  const payloadData = {
    job_id: jobId,
    process_id: processId,
    ids,
  }

  if (!payloadData.ids.length) {
    throw new Error('No applications selected')
  }

  await api.sendRequest(`${BASE_URL}/bulk-offer-extend`, 'POST', payloadData)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function deleteItem(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`, 'DELETE')
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function bulkDelete(ids) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/bulk-delete`, 'POST', { ids })
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function bulkStatusUpdate({ ids, status }) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/bulk-status-update`, 'POST', { ids, status })
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function bulkStatusUpdateEmbassySubmission({ ids, status }) {
  const api = useApi()
  await api.sendRequest(`embasy-submissions-bulk-status-update`, 'POST', { ids, status })
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function bulkUpload(payload) {
  const api = useApi()
  await api.sendRequest(
    `${BASE_URL}/job-lists/${payload.job_id}/agents/${payload.agent_id}/bulk-upload`,
    'POST',
    payload.applications,
  )
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function deleteImagePdfFiles({ id, file_key }) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/delete-file/${id}`, 'POST', { file_key })
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function passportOCRFn(file) {
  const api = useApi()
  const formData = new FormData()
  formData.append('passport', file)

  await api.sendRequest(`${BASE_URL}/passport/ocr`, 'POST', formData)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function checkPassportExistsFn({ passport_no, ignore_id = null }) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/passport/check-exists`, {
    passport_no,
    ignore_id,
  })

  await api.sendRequest(url)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function mergeDocuments(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/merge-documents`, 'POST')
  if (api.error.value) throw api.error.value
  return api.data.value
}

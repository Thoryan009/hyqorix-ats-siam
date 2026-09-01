import { useApi } from '@/shared/composables/useApi'

const BASE_URL = '/settings'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export const fetchOne = async (id) => {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`)
  return response(api)
}

export async function fetchSettingData() {
  const api = useApi()
  await api.sendRequest('setting-data')
  // console.log(`Fetching setting with ID: ${id}`) // Debug log

  return response(api)
}

export async function fetchPartyTypeMappings() {
  const api = useApi()
  await api.sendRequest('settings/party-type-mappings')
  return response(api)
}

export async function updatePartyTypeMappings(payload) {
  const api = useApi()
  await api.sendRequest('settings/party-type-mappings', 'PUT', payload, {
    headers: {
      'Content-Type': 'application/json',
    },
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}

// Update Settings
export async function updateData({ id, payload }) {
  const api = useApi()

  await api.sendRequest(`${BASE_URL}/${id}`, 'POST', payload, {
    headers: {
      'X-HTTP-Method-Override': 'PUT',
    },
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}

// Update Embassy Settings
export async function updateEmbassyData({ id, payload }) {
  const api = useApi()

  await api.sendRequest(`${BASE_URL}/embassy/${id}`, 'POST', payload, {
    headers: {
      'X-HTTP-Method-Override': 'PUT',
    },
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}


// Change Password

export async function changePasswordFn(payload) {
  const api = useApi()

  await api.sendRequest(`auth/password-change`, 'POST', payload, {
    headers: {
      'Content-Type': 'application/json',
    },
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function backupDBFn() {
  const api = useApi()

  await api.sendRequest(`settings/backup`, 'POST', null, {
    headers: {
      'Content-Type': 'application/json',
    },
    responseType: 'blob', // Important: handle file download as blob
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}

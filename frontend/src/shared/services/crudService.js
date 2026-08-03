import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'
import { plural } from 'vue-ready-modular/lib/utils/string'

 const toKebabCase = (str) => {
  return str
    .replace(/([a-z])([A-Z])/g, "$1-$2") // CountryDetail -> Country-Detail
    .toLowerCase(); // country-detail
};

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

// ✅ Fetch all
export async function fetchAll(module, page = 1, perPage = 10, filters = {}) {
  const api = useApi()

  const url = buildUrl(`/${(plural(toKebabCase(module)))}`, {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

// ✅ Fetch one
export async function fetchOne(module, id) {
  const api = useApi()
  await api.sendRequest(`/${plural(toKebabCase(module))}/${id}`)
  return response(api)
}

// ✅ Create
export async function submitData(module, payload) {
  const api = useApi()
  await api.sendRequest(`/${plural(toKebabCase(module))}`, 'POST', payload)

  if (api.error.value) throw api.error.value
  return api.data.value
}

// ✅ Update
export async function updateData(module, { id, data }) {
  const api = useApi()
  console.log('Updating item with id:', id, 'and data:', data)
  await api.sendRequest(`/${plural(toKebabCase(module))}/${id}`, 'POST', data, {
    headers: { 'X-HTTP-Method-Override': 'PUT' }
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}

// ✅ Update single field / custom status endpoint (e.g., tasks/{task}/update-status)
export async function updateStatusRequest(module, id, status) {
  const api = useApi()
  await api.sendRequest(`/${plural(toKebabCase(module))}/${id}/update-status`, 'PUT', { status })

  if (api.error.value) throw api.error.value
  return api.data.value
}

// ✅ Delete
export async function deleteItem(module, id) {
  const api = useApi()
  await api.sendRequest(`/${plural(toKebabCase(module))}/${id}`, 'DELETE')

  if (api.error.value) throw api.error.value
  return api.data.value
}

// ✅ Bulk delete
export async function bulkDelete(module, ids) {
  const api = useApi()
  await api.sendRequest(`/${plural(toKebabCase(module))}/bulk-delete`, 'POST', { ids })

  if (api.error.value) throw api.error.value
  return api.data.value
}

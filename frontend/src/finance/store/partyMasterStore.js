import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchAll as fetchVendors } from '@/modules/vendor/services/vendorService'
import { fetchAll as fetchPrincipals } from '@/modules/principal/services/principalService'
import { fetchAll as fetchClients } from '@/modules/client/services/clientService'
import { fetchAll as fetchEmployees } from '@/modules/work_order/services/employeeService'
import {
  mapVendorFromApi,
  mapPrincipalFromApi,
  mapClientFromApi,
  mapStaffFromApi,
} from '../utils/partyEntityMapper'

const apiPartyTypes = ['vendor', 'principal', 'client', 'staff']

export const usePartyMasterStore = defineStore('partyMaster', () => {
  const vendors = ref([])
  const principals = ref([])
  const clients = ref([])
  const staff = ref([])

  const loadingState = ref({
    vendor: false,
    principal: false,
    client: false,
    staff: false,
  })

  const loadedState = ref({
    vendor: false,
    principal: false,
    client: false,
    staff: false,
  })

  async function fetchParties(partyType, force = false) {
    if (!apiPartyTypes.includes(partyType)) return
    if (loadingState.value[partyType]) return
    if (loadedState.value[partyType] && !force) return

    loadingState.value[partyType] = true

    try {
      if (partyType === 'vendor') {
        const { data, error } = await fetchVendors(1, 500, { status: 1 })
        if (error) throw error
        vendors.value = (data?.data ?? []).map(mapVendorFromApi)
        loadedState.value.vendor = true
      } else if (partyType === 'principal') {
        const { data, error } = await fetchPrincipals(1, 500, {})
        if (error) throw error
        principals.value = (data?.data ?? []).map(mapPrincipalFromApi)
        loadedState.value.principal = true
      } else if (partyType === 'client') {
        const { data, error } = await fetchClients(1, 500, { status: 1 })
        if (error) throw error
        clients.value = (data?.data ?? []).map(mapClientFromApi)
        loadedState.value.client = true
      } else if (partyType === 'staff') {
        const { data, error } = await fetchEmployees(1, 500, { status: 1 })
        if (error) throw error
        staff.value = (data?.data ?? []).map(mapStaffFromApi)
        loadedState.value.staff = true
      }
    } catch {
      if (partyType === 'vendor') vendors.value = []
      if (partyType === 'principal') principals.value = []
      if (partyType === 'client') clients.value = []
      if (partyType === 'staff') staff.value = []
    } finally {
      loadingState.value[partyType] = false
    }
  }

  function isLoading(partyType) {
    return loadingState.value[partyType] ?? false
  }

  function getParties(partyType) {
    if (partyType === 'vendor') return vendors.value
    if (partyType === 'principal') return principals.value
    if (partyType === 'client') return clients.value
    if (partyType === 'staff') return staff.value
    return []
  }

  function getParty(partyType, partyId) {
    return getParties(partyType).find((party) => party.id === Number(partyId)) ?? null
  }

  return {
    vendors,
    principals,
    clients,
    staff,
    fetchParties,
    isLoading,
    getParties,
    getParty,
  }
})

import { fetchPartySourceOptions } from '@/modules/parties/services/partyService'
import { usePartyTypeSourceStore } from '@/modules/parties/store/partyTypeSourceStore'

const partySourceModules = {
  client: {
    selectLabelKey: 'parties.select_client',
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  principal: {
    selectLabelKey: 'parties.select_principal',
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  agent: {
    selectLabelKey: 'parties.select_agent',
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  application: {
    selectLabelKey: 'parties.select_candidate',
    requiresJobFilter: true,
    mapItem: (row) => ({
      id: row.id,
      code: row.passport_no,
      name: row.full_name,
      applicationId: row.application_id,
    }),
  },
  vendor: {
    selectLabelKey: 'parties.select_vendor',
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  employee: {
    selectLabelKey: 'parties.select_staff',
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
}

function resolveSourceModule(partyType) {
  const store = usePartyTypeSourceStore()
  return store.getSourceModule(partyType)
}

export function hasPartySource(partyType) {
  const sourceModule = resolveSourceModule(partyType)
  return Boolean(sourceModule && partySourceModules[sourceModule])
}

export function requiresPartyJobFilter(partyType) {
  const sourceModule = resolveSourceModule(partyType)
  return Boolean(partySourceModules[sourceModule]?.requiresJobFilter)
}

export function getPartySourceConfig(partyType) {
  const sourceModule = resolveSourceModule(partyType)
  const base = sourceModule ? partySourceModules[sourceModule] : null

  if (!base) return null

  return {
    ...base,
    fetch: (filters = {}) => fetchPartySourceOptions(partyType, filters),
  }
}

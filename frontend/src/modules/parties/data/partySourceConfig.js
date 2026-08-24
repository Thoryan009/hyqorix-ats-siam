import { fetchPartySourceOptions } from '@/modules/parties/services/partyService'

export const partySourceConfigs = {
  Client: {
    selectLabelKey: 'parties.select_client',
    fetch: (filters = {}) => fetchPartySourceOptions('Client', filters),
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  Principal: {
    selectLabelKey: 'parties.select_principal',
    fetch: (filters = {}) => fetchPartySourceOptions('Principal', filters),
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  Agent: {
    selectLabelKey: 'parties.select_agent',
    fetch: (filters = {}) => fetchPartySourceOptions('Agent', filters),
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  Candidate: {
    selectLabelKey: 'parties.select_candidate',
    fetch: (filters = {}) => fetchPartySourceOptions('Candidate', filters),
    requiresJobFilter: true,
    mapItem: (row) => ({
      id: row.id,
      code: row.passport_no,
      name: row.full_name,
      applicationId: row.application_id,
    }),
  },
  Vendor: {
    selectLabelKey: 'parties.select_vendor',
    fetch: (filters = {}) => fetchPartySourceOptions('Vendor', filters),
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
  Staff: {
    selectLabelKey: 'parties.select_staff',
    fetch: (filters = {}) => fetchPartySourceOptions('Staff', filters),
    mapItem: (row) => ({
      id: row.id,
      code: row.code,
      name: row.name,
    }),
  },
}

export function hasPartySource(partyType) {
  return Boolean(partySourceConfigs[partyType])
}

export function requiresPartyJobFilter(partyType) {
  return Boolean(partySourceConfigs[partyType]?.requiresJobFilter)
}

export function getPartySourceConfig(partyType) {
  return partySourceConfigs[partyType] ?? null
}

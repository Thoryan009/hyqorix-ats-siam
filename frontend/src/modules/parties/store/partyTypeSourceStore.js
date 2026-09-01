import { defineStore } from 'pinia'
import { ref } from 'vue'

export const usePartyTypeSourceStore = defineStore('partyTypeSource', () => {
  const mappingsByCode = ref({})
  const jobFilterByCode = ref({})

  function setFromPartyTypes(partyTypes = []) {
    const map = {}
    const jobFilterMap = {}

    for (const partyType of partyTypes) {
      const code = partyType.code ?? partyType.id
      if (code && partyType.source_module) {
        map[code] = partyType.source_module
      }
      if (code) {
        jobFilterMap[code] = Boolean(partyType.apply_job_filter)
      }
    }

    mappingsByCode.value = map
    jobFilterByCode.value = jobFilterMap
  }

  function getSourceModule(partyTypeCode) {
    return mappingsByCode.value[partyTypeCode] ?? null
  }

  function requiresJobFilter(partyTypeCode) {
    return Boolean(jobFilterByCode.value[partyTypeCode])
  }

  return {
    mappingsByCode,
    jobFilterByCode,
    setFromPartyTypes,
    getSourceModule,
    requiresJobFilter,
  }
})

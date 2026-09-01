import { defineStore } from 'pinia'
import { ref } from 'vue'

export const usePartyTypeSourceStore = defineStore('partyTypeSource', () => {
  const mappingsByCode = ref({})

  function setFromPartyTypes(partyTypes = []) {
    const map = {}

    for (const partyType of partyTypes) {
      const code = partyType.code ?? partyType.id
      if (code && partyType.source_module) {
        map[code] = partyType.source_module
      }
    }

    mappingsByCode.value = map
  }

  function getSourceModule(partyTypeCode) {
    return mappingsByCode.value[partyTypeCode] ?? null
  }

  return {
    mappingsByCode,
    setFromPartyTypes,
    getSourceModule,
  }
})

import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchAll } from '@/modules/agent/services/agentService'
import { mapAgentFromApi } from '../utils/partyEntityMapper'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'

export const useAgentMasterStore = defineStore('agentMaster', () => {
  const agents = ref([])
  const isLoading = ref(false)
  const isLoaded = ref(false)

  async function fetchAgents(force = false) {
    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true

    try {
      const { data, error } = await fetchAll(1, 500, { status: 1 })
      if (error) throw error
      agents.value = extractPaginatedRows(data).map(mapAgentFromApi)
      isLoaded.value = true
    } catch {
      agents.value = []
    } finally {
      isLoading.value = false
    }
  }

  function getAgent(agentId) {
    return agents.value.find((agent) => agent.id === Number(agentId)) ?? null
  }

  return {
    agents,
    isLoading,
    fetchAgents,
    getAgent,
  }
})

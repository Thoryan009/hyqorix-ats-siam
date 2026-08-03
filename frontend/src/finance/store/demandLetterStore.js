import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchAll as fetchWorkOrders } from '@/modules/work_order/services/workOrderService'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'
import {
  mapDemandLetterFromApi,
  sortDemandLettersAlphabetically,
  formatDemandLetterSelectOption,
} from '../utils/demandLetterMapper'

export const useDemandLetterStore = defineStore('financeDemandLetter', () => {
  const demandLetters = ref([])

  const isLoadingDemandLetters = ref(false)
  const isLoadedDemandLetters = ref(false)

  async function fetchDemandLetterList(force = false) {
    if (isLoadingDemandLetters.value) return
    if (isLoadedDemandLetters.value && !force) return

    isLoadingDemandLetters.value = true

    try {
      const { data, error } = await fetchWorkOrders(1, 500, {
        has_jobs: 1,
        include_jobs_count: 1,
      })
      if (error) throw error

      demandLetters.value = sortDemandLettersAlphabetically(
        extractPaginatedRows(data)
          .map(mapDemandLetterFromApi)
          .filter((item) => item.jobs_count > 0)
      )
      isLoadedDemandLetters.value = true
    } catch {
      demandLetters.value = []
    } finally {
      isLoadingDemandLetters.value = false
    }
  }

  function getDemandLetter(id) {
    return demandLetters.value.find((item) => item.id === Number(id)) ?? null
  }

  function getDemandLetterSelectOptions() {
    return demandLetters.value.map(formatDemandLetterSelectOption)
  }

  function resolveDemandLetterMeta(demandLetterId) {
    const demandLetter = getDemandLetter(demandLetterId)
    if (!demandLetter) return null

    return {
      demand_letter_id: demandLetter.id,
      demand_letter: demandLetter.dl_no,
      client_name: demandLetter.client_name,
      demand_letter_country: demandLetter.country,
    }
  }

  return {
    demandLetters,
    isLoadingDemandLetters,
    fetchDemandLetterList,
    getDemandLetter,
    getDemandLetterSelectOptions,
    resolveDemandLetterMeta,
  }
})

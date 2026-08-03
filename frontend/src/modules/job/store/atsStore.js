import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useAtsStore = defineStore('ats', () => {
  const moduleName = 'Ats'
  
  const allProcesses = ref([])

  const loading = ref(false)

  const setAllProcesses = (processes) => {
    allProcesses.value = processes
  }

  const nextProcesses = computed(() => {
    // add select_process at the beginning
    return [ ...allProcesses.value]
  })

  const allProcessIds = () => {
    return allProcesses.value.map((process) => process.id)
  }

  return {
    moduleName,
    allProcesses,
    setAllProcesses,
    allProcessIds,
    nextProcesses,
    loading
  }
})

import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useJobAtsStore = defineStore('jobAts', () => {
  const moduleName = 'JobAts'

  const allProcesses = ref([])

  const setAllProcesses = (processes) => {
    allProcesses.value = processes
  }

  const nextProcesses = computed(() => {
    // add select_process at the beginning
    return [
      { id: '', name: 'Select Process' },
      ...allProcesses.value,
    ]
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
  }
})

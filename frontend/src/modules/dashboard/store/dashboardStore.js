import { defineStore } from 'pinia'
export const useDashboardStore = defineStore('dashboard', () => {
  const moduleName = 'Dashboard'

  return {
    moduleName,
  }
})

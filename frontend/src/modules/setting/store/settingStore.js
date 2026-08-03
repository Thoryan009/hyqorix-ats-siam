import { defineStore } from 'pinia'

export const useSettingStore = defineStore('setting', () => {
  const moduleName = 'Settings'

  return {
    moduleName,

  }
})

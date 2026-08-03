import app from '@/shared/config/appConfig'
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const moduleName = 'Auth'
  const permissions = ref([])

  // 🔥 INIT FUNCTION (IMPORTANT)
  const initAuth = () => {
    const storedPermissions = localStorage.getItem(app.userPermissionsKey)

    if (storedPermissions) {
      permissions.value = JSON.parse(storedPermissions)
    }
  }
  const userType = localStorage.getItem(app.userType) || null

  const can = (perm) => {
    return permissions.value.includes(perm)
  }

  const canAny = (perms) => {
    return perms.some((p) => permissions.value.includes(p))
  }

  return {
    moduleName,
    permissions,
    initAuth,
    can,
    canAny,
    userType,
  }
})

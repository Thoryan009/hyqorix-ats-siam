import app from '@/shared/config/appConfig'
import { defineStore } from 'pinia'
import { ref } from 'vue'

const parseJson = (value, fallback) => {
  try {
    return value ? JSON.parse(value) : fallback
  } catch {
    return fallback
  }
}

export const useAuthStore = defineStore('auth', () => {
  const moduleName = 'Auth'
  const permissions = ref([])
  const roles = ref([])
  const userType = ref(null)

  const initAuth = () => {
    permissions.value = parseJson(localStorage.getItem(app.userPermissionsKey), [])
    roles.value = parseJson(localStorage.getItem(app.userRolesKey), [])
    userType.value = localStorage.getItem(app.userType) || null
  }

  const setSession = ({ token, user, roles: nextRoles = [], permissions: nextPermissions = [] }) => {
    localStorage.setItem(app.tokenKey, token)
    localStorage.setItem(app.userType, user?.type || '')
    localStorage.setItem(app.userRolesKey, JSON.stringify(nextRoles))
    localStorage.setItem(app.userPermissionsKey, JSON.stringify(nextPermissions))

    permissions.value = nextPermissions
    roles.value = nextRoles
    userType.value = user?.type || null
  }

  const clearSession = () => {
    localStorage.removeItem(app.tokenKey)
    localStorage.removeItem(app.userRolesKey)
    localStorage.removeItem(app.userPermissionsKey)
    localStorage.removeItem(app.userType)

    permissions.value = []
    roles.value = []
    userType.value = null
  }

  const can = (perm) => {
    return permissions.value.includes(perm)
  }

  const canAny = (perms) => {
    return perms.some((p) => permissions.value.includes(p))
  }

  return {
    moduleName,
    permissions,
    roles,
    userType,
    initAuth,
    setSession,
    clearSession,
    can,
    canAny,
  }
})

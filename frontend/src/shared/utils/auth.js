import app from '@/shared/config/appConfig'


export const getToken = () => localStorage.getItem(app.tokenKey)

export function getUserPermissions() {
  return JSON.parse(localStorage.getItem(app.userPermissionsKey) || '[]')
}

export function getUserRoles() {
  return JSON.parse(localStorage.getItem(app.userRolesKey) || '[]')
}

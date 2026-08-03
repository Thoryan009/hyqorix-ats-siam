import { computed, ref } from 'vue'

export function useRolePermissions(roles, permissions) {
  const selectedRoleId = ref(null)
  const rolePermissionMap = ref({})

  const selectedRole = computed(() =>
    roles.value.find(r => Number(r.id) === Number(selectedRoleId.value)) || null
  )

  const selectedPermissionIds = computed(() =>
    rolePermissionMap.value[selectedRoleId.value] || []
  )

  const extractPermissionIds = (role) =>
    (role?.permissions || [])
      .map(p => typeof p === 'object' ? p.id : p)

  const initializeRoleMap = () => {
    const map = {}

    roles.value.forEach(role => {
      map[role.id] = extractPermissionIds(role)
    })

    rolePermissionMap.value = map
  }

  const togglePermission = (permissionId) => {
    const current = selectedPermissionIds.value
    const exists = current.includes(permissionId)

    rolePermissionMap.value[selectedRoleId.value] = exists
      ? current.filter(id => id !== permissionId)
      : [...current, permissionId]
  }

  return {
    selectedRoleId,
    selectedRole,
    selectedPermissionIds,
    rolePermissionMap,
    initializeRoleMap,
    togglePermission,
  }
}

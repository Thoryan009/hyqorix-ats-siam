
<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('role.role_permissions')"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
  >
    <ViewModalLayout height="70vh" modalHeight="h-auto">
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <aside class="lg:col-span-4 xl:col-span-3">
          <div class="h-full rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-4 py-3">
              <h3 class="text-sm font-semibold text-gray-700">{{ t('role.roles') }}</h3>
            </div>

            <nav class="max-h-[56vh] space-y-1 overflow-y-auto p-2">
              <button
                v-for="role in roles"
                :key="role.id"
                type="button"
                class="w-full rounded-md px-3 py-2 text-left text-sm transition"
                :class="
                  Number(selectedRoleId) === Number(role.id)
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-700 hover:bg-gray-100'
                "
                @click="selectedRoleId = role.id"
              >{{ role.name }}</button>
            </nav>
          </div>
        </aside>

        <section class="lg:col-span-8 xl:col-span-9">
          <div class="rounded-lg border border-gray-200 bg-white">
            <div
              class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex items-center gap-4">
                <div>
                  <h3 class="text-sm font-semibold text-gray-700">{{ t('role.permissions') }}</h3>
                  <p
                    class="text-xs text-gray-500"
                    v-if="selectedRole"
                  >{{ t('role.editing') }}: {{ selectedRole.name }}</p>
                </div>

                <!-- Master Select All -->
                <label
                  v-if="selectedRole && permissions.length"
                  class="flex cursor-pointer items-center gap-2 rounded-md border border-blue-300 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                >
                  <input
                    type="checkbox"
                    class="h-4 w-4"
                    :checked="isAllSelected"
                    v-indeterminate="isSomeSelected"
                    @change="toggleSelectAll"
                  />
                  <span>{{ t('role.select_all') }}</span>
                </label>
              </div>

              <BaseButton
                :disabled="!selectedRole || updateRolePermissionsLoading"
                @click="savePermissions"
              >
                <span v-if="updateRolePermissionsLoading">{{ t('shared.messages.saving') }}</span>
                <span v-else>{{ t('shared.actions.save') }}</span>
              </BaseButton>
            </div>

            <div class="max-h-[56vh] overflow-y-auto p-4">
              <div
                v-if="permissionsLoading"
                class="py-8 text-center text-sm text-gray-500"
              >{{t('role.loading_permissions')}}</div>

              <div
                v-else-if="!selectedRole"
                class="py-8 text-center text-sm text-gray-500"
              >{{ t('role.no_role_selected') }}</div>

              <div
                v-else-if="!permissions.length"
                class="py-8 text-center text-sm text-gray-500"
              >{{ t('role.no_permissions_found') }}</div>

              <div v-else class="space-y-3">

                <div
                  v-for="group in groupedPermissions"
                  :key="group.label"
                  class="rounded-md border border-gray-200"
                >
                  <div class="grid grid-cols-1 gap-2 p-3 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-3 space-y-2">
                      <div class="text-sm font-semibold text-gray-700">{{ group.label }}</div>

                      <!-- Group Select All -->
                      <label
                        class="flex cursor-pointer items-center gap-2 rounded-md border border-green-300 bg-green-50 px-2 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 w-fit"
                      >
                        <input
                          type="checkbox"
                          class="h-3.5 w-3.5"
                          :checked="isGroupFullySelected(group)"
                          v-indeterminate="isGroupPartiallySelected(group)"
                          @change="toggleGroupSelection(group)"
                        />
                        <span>{{ t('role.select_all') }}</span>
                      </label>
                    </div>

                    <div class="flex flex-wrap gap-2 lg:col-span-9">
                      <label
                        v-for="permission in group.items"
                        :key="permission.id"
                        class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                      >
                        <input
                          type="checkbox"
                          class="h-4 w-4"
                          :checked="isChecked(permission.id)"
                          @change="togglePermission(permission.id)"
                        />
                        <span>{{ permission.name }}</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { computed, watch } from 'vue'
import { usePermissionQuery } from '@/modules/access-control/queries/usePermissionQuery'

import { useRoleStore } from '@/modules/access-control/stores/RoleStore'
import { useRoleMutations } from '@/modules/access-control/queries/useRoleMutations'

import { useRolePermissions } from '@/modules/access-control/composables/useRolePermissions'
import { groupPermissions } from '@/modules/access-control/utils/permissionUtils'

import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import {useTranslate} from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const props = defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
})

const store = useRoleStore()
const { updateRolePermissions, updateRolePermissionsLoading } = useRoleMutations(store.moduleName)

const roles = computed(() => props.rows || [])

/**
 * 🔥 FETCH PERMISSIONS
 */

/* ---------------- Query ---------------- */
const { rows: permissions, isLoading: permissionsLoading } = usePermissionQuery(1, 250, {})

/**
 * 🔥 USE COMPOSABLE
 */
const { selectedRoleId, selectedRole, selectedPermissionIds, initializeRoleMap, togglePermission, rolePermissionMap } =
  useRolePermissions(roles)

/**
 * 🔥 GROUP PERMISSIONS
 */
const groupedPermissions = computed(() => groupPermissions(permissions.value))

/**
 * 🔥 CHECK
 */
const isChecked = (id) => selectedPermissionIds.value.includes(id)

/**
 * 🔥 SAVE
 */
const savePermissions = async () => {
  if (!selectedRole.value) return

  await updateRolePermissions.mutateAsync({
    roleId: selectedRole.value.id,
    permissions: selectedPermissionIds.value,
  })
}

/**
 * 🔥 MASTER SELECT ALL
 */
const isAllSelected = computed(() => {
  if (!permissions.value.length) return false
  return permissions.value.every(p => selectedPermissionIds.value.includes(p.id))
})

const isSomeSelected = computed(() => {
  if (!permissions.value.length) return false
  const someSelected = permissions.value.some(p => selectedPermissionIds.value.includes(p.id))
  return someSelected && !isAllSelected.value
})

const toggleSelectAll = () => {
  if (!selectedRoleId.value) return

  if (isAllSelected.value) {
    // Deselect all
    rolePermissionMap.value[selectedRoleId.value] = []
  } else {
    // Select all
    rolePermissionMap.value[selectedRoleId.value] = permissions.value.map(p => p.id)
  }
}

/**
 * 🔥 GROUP SELECT ALL
 */
const isGroupFullySelected = (group) => {
  if (!group.items.length) return false
  return group.items.every(p => selectedPermissionIds.value.includes(p.id))
}

const isGroupPartiallySelected = (group) => {
  if (!group.items.length) return false
  const someSelected = group.items.some(p => selectedPermissionIds.value.includes(p.id))
  return someSelected && !isGroupFullySelected(group)
}

const toggleGroupSelection = (group) => {
  if (!selectedRoleId.value) return

  const groupIds = group.items.map(p => p.id)
  const allSelected = isGroupFullySelected(group)
  const current = selectedPermissionIds.value

  if (allSelected) {
    // Deselect all in this group
    rolePermissionMap.value[selectedRoleId.value] = current.filter(id => !groupIds.includes(id))
  } else {
    // Select all in this group
    const newIds = groupIds.filter(id => !current.includes(id))
    rolePermissionMap.value[selectedRoleId.value] = [...current, ...newIds]
  }
}

/**
 * 🔥 CUSTOM DIRECTIVE FOR INDETERMINATE STATE
 */
const vIndeterminate = {
  mounted(el, binding) {
    el.indeterminate = binding.value
  },
  updated(el, binding) {
    el.indeterminate = binding.value
  }
}

/**
 * 🔥 WATCHERS
 */
watch(
  roles,
  () => {
    initializeRoleMap()

    if (!selectedRoleId.value && roles.value.length) {
      selectedRoleId.value = roles.value[0]?.id
    }
  },
  { immediate: true }
)

watch(
  () => store.isViewModal,
  (visible) => {
    if (!visible) return

    initializeRoleMap()
    selectedRoleId.value = store.item?.id || roles.value[0]?.id || null
  }
)
</script>

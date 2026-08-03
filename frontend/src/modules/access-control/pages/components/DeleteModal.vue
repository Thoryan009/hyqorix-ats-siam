
<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Are you sure you want to delete this ${store.moduleName}?`"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[50vw]'"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="space-y-2">
        <p class="text-lg">
          <strong>ID:</strong>
          {{ store.item.id }}
        </p>
        <p class="text-lg">
          <strong>Name:</strong>
          {{ store.item.name }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-2 pt-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700"
          type="button"
          @click="store.handleToggleModal"
        >Cancel</BaseButton>

        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700" :disabled="removeLoading">
          <span v-if="removeLoading">Deleting...</span>
          <span v-else>Delete</span>
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useRoleMutations } from '@/modules/access-control/queries/useRoleMutations'
import { useRoleStore } from '@/modules/access-control/stores/RoleStore'

const store = useRoleStore()

const { remove, removeLoading } = useRoleMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // close modal
  },
  onError(error) {
    console.log('Custom error handling', error)
  },
})

const handleSubmit = async () => {
  await remove.mutateAsync(store.item.id)
}
</script>

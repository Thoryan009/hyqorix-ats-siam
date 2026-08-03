<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Are you sure you want to delete this ${store.moduleName}?`"
    @close="store.handleToggleModal"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="space-y-2 text-gray-700">
        <p class="text-lg">
          <strong>ID:</strong>
          {{ store.item?.id }}
        </p>

        <p class="text-lg">
          <strong>Name:</strong>
          {{ store.item?.name }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-2 pt-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700"
          type="button"
          @click="store.handleToggleModal"
        >
          Cancel
        </BaseButton>

        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700"> Delete </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
// Designation Imports
import { useDesignationMutations } from '../../queries/useDesignationMutations'
import { useDesignationStore } from '../../stores/designationStore'

// Store
const store = useDesignationStore()

// Mutations
const { remove } = useDesignationMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
  onError(error) {
    console.log('Delete error:', error)
  },
})

// Submit
const handleSubmit = async () => {
  if (!store.item?.id) return

  await remove.mutateAsync(store.item.id)
}
</script>

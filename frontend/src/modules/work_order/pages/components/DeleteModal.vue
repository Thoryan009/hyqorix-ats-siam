<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Are you sure you want to delete this ${store.moduleName}?`"
    @close="store.handleToggleModal"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="space-y-2">
        <p class="text-sm sm:text-lg break-words">
          <strong>ID:</strong>
          {{ store.item.id }}
        </p>
        <p class="text-sm sm:text-lg break-words">
          <strong>Demand Letter:</strong>
          {{ store.item.work_order_id }}
        </p>
        <p class="text-sm sm:text-lg break-words">
          <strong>Candidates:</strong>
          {{ store.item.candidates }}
        </p>
        <p class="text-sm sm:text-lg break-words">
          <strong>End Date:</strong>
          {{ store.item.end_date }}
        </p>

        <p class="text-sm sm:text-lg break-words">
          <strong>Client:</strong>
          {{ store.item.client }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto"
          type="button"
          @click="store.handleToggleModal"
          >Cancel</BaseButton
        >
        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700 w-full sm:w-auto" :disabled="removeLoading">
          <span v-if="removeLoading">Deleting...</span>
          <span v-else>Delete</span>
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useWorkOrderStore } from '@/modules/work_order/store/workOrderStore'
import { useWorkOrderMutations } from '@/modules/work_order/queries/useWorkOrderMutations'

const store = useWorkOrderStore()

const { remove, removeLoading } = useWorkOrderMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // ✅ close modal
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})
const handleSubmit = async () => {
  await remove.mutateAsync(store.item.id)
}
</script>

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
          <strong>Name:</strong>
          {{ store.item.name }}
        </p>

        <p class="text-sm sm:text-lg break-words">
          <strong>Amount:</strong>
          {{ store.item.amount }}
        </p>

        <p class="text-sm sm:text-lg break-words">
          <strong>Amount USD:</strong>
          {{ store.item.amount_usd }}
        </p>

        <p class="text-sm sm:text-lg break-words">
          <strong>Category:</strong>
          {{ store.item.category }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto"
          type="button"
          @click="store.handleToggleModal"
          >Cancel
        </BaseButton>
        <BaseButton
          type="submit"
          class="bg-red-600 hover:bg-red-700 w-full sm:w-auto"
          :disabled="removeLoading"
        >
          <span v-if="removeLoading">Deleting...</span>
          <span v-else>Delete</span>
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useJobDetailsHeadMutations } from '../../queries/useJobDetailsHeadMutations'
import { useJobDetailsHeadStore } from '../../store/jobDetailsHeadStore'

const store = useJobDetailsHeadStore()

// const {remove} = useClientMutations()

const { remove, removeLoading } = useJobDetailsHeadMutations(store.moduleName, {
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

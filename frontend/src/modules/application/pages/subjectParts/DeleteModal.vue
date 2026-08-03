<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Are you sure you want to delete this ${store.moduleName}?`"
    :maxWidth="`${store.modalWidth}`"
    @close="store.handleToggleModal"
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
          >Cancel</BaseButton
        >
        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700" :disabled="removeLoading">
          <span v-if="removeLoading">Deleting...</span>
          <span v-else>Delete</span>
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useSubjectMutations } from '@/modules/application/queries/useSubjectMutations'
import { useSubjectStore } from '@/modules/application/store/subjectStore'

const store = useSubjectStore()

const { remove, removeLoading } = useSubjectMutations(store.moduleName, {
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

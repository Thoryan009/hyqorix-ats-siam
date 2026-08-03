<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Are you sure you want to delete this ${store.moduleName}?`"
    @close="store.handleToggleModal"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="space-y-2">
        <p class="text-lg">
          <strong>Job Code:</strong>
          {{ store.item.job_code }}
        </p>

        <p class="text-lg">
          <strong>Job Name:</strong>
          {{ store.item.name }}
        </p>

        <p class="text-lg">
          <strong>Vacancy:</strong>
          {{ store.item.vacancy }}
        </p>

        <p class="text-lg">
          <strong>Demand Letter:</strong>
          {{ store.item.work_order }}
        </p>

        <p class="text-lg">
          <strong>Salary:</strong>
          {{ store.item.salary }}
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

        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700" :disabled="removeLoading">
          <span v-if="removeLoading">Deleting...</span>
          <span v-else>Delete</span></BaseButton
        >
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useJobStore } from '@/modules/job/store/jobStore'
import { useJobMutations } from '@/modules/job/queries/useJobMutations'

const store = useJobStore()

const { remove, removeLoading } = useJobMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

const handleSubmit = async () => {
  await remove.mutateAsync(store.item.id)
}
</script>

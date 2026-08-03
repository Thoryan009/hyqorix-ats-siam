<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Are you sure you want to delete this ${store.moduleName}?`"
    @close="store.handleToggleModal"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- IMPORTANT INFO BLOCK -->
      <div class="space-y-3 text-lg">
        <p>
          <strong>ID:</strong>
          {{ store.item.id }}
        </p>

        <p>
          <strong>Full Name:</strong>
          {{ store.item.sur_name }} {{ store.item.given_name }}
        </p>

        <p>
          <strong>Application ID:</strong>
          {{ store.item.application_id }}
        </p>

        <p>
          <strong>Job Name:</strong>
          {{ store.item.job }}
        </p>

        <p>
          <strong>Passport No:</strong>
          {{ store.item.passport_no || 'N/A' }}
        </p>

        <p>
          <strong>NID No:</strong>
          {{ store.item.nid_no || 'N/A' }}
        </p>

        <p>
          <strong>Mobile:</strong>
          {{ store.item.mobile || 'N/A' }}
        </p>

        <p>
          <strong>Status:</strong>
          {{ store.item.status }}
        </p>
      </div>

      <!-- ACTION BUTTONS -->
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
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import { useApplicationMutations } from '@/modules/application/queries/useApplicationMutations'

const store = useApplicationStore()

const { remove, removeLoading } = useApplicationMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
  onError(error) {
    console.log('Custom error handling', error)
  },
})

const handleSubmit = async () => {
  await remove.mutateAsync(store.item.id)
}
</script>

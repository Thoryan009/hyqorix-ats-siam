<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="t('designations.delete_confirm')"
    @close="store.handleToggleModal"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="space-y-2 text-gray-700">
        <p class="text-lg">
          <strong>{{ t('shared.labels.id') }}:</strong>
          {{ store.item?.id }}
        </p>

        <p class="text-lg">
          <strong>{{ t('shared.labels.name') }}:</strong>
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
          {{ t('shared.actions.cancel') }}
        </BaseButton>

        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700"> {{ t('shared.actions.delete') }} </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
// Designation Imports
import { useDesignationMutations } from '../../queries/useDesignationMutations'
import { useDesignationStore } from '../../stores/designationStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

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

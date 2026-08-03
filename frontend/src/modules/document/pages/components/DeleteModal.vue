<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Delete ${store.moduleName}`"
    @close="store.handleToggleModal"
  >
    <p class="text-sm text-gray-600">
      Are you sure you want to delete
      <span class="font-semibold text-gray-900">{{ store.item?.name }}</span>?
    </p>
    <div class="mt-6 flex justify-end gap-2">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" @click="store.handleToggleModal">
        Cancel
      </BaseButton>
      <BaseButton class="bg-red-600 hover:bg-red-700" :disabled="removeLoading" @click="handleDelete">
        <span v-if="removeLoading">Deleting...</span>
        <span v-else>Delete</span>
      </BaseButton>
    </div>
  </BaseModal>
</template>

<script setup>
import BaseModal from '@/shared/components/base/BaseModal.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import { useDocumentMutations } from '../../queries/useDocumentMutations'
import { useDocumentStore } from '../../store/documentStore'

const store = useDocumentStore()

const { remove, removeLoading } = useDocumentMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
})

const handleDelete = async () => {
  if (!store.item?.id) return
  await remove.mutateAsync(store.item.id)
}
</script>

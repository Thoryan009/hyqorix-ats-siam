<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="t('documents.delete')"
    @close="store.handleToggleModal"
  >
    <p class="text-sm text-gray-600">
      {{ t('documents.delete_confirm', { name: store.item?.name }) }}
    </p>
    <div class="mt-6 flex justify-end gap-2">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" @click="store.handleToggleModal">
        {{ t('shared.actions.cancel') }}
      </BaseButton>
      <BaseButton class="bg-red-600 hover:bg-red-700" :disabled="removeLoading" @click="handleDelete">
        <span v-if="removeLoading">{{ t('shared.messages.deleting') }}</span>
        <span v-else>{{ t('shared.actions.delete') }}</span>
      </BaseButton>
    </div>
  </BaseModal>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import { useDocumentMutations } from '../../queries/useDocumentMutations'
import { useDocumentStore } from '../../store/documentStore'

const { t } = useTranslate()
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

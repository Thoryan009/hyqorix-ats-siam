<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="t('vendor.delete_type')"
    @close="store.handleToggleModal"
  >
    <form class="space-y-4" @submit.prevent="handleSubmit">
      <p class="text-gray-700">{{ t('vendor.delete_type_confirmation') }}</p>
      <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
        <p class="text-sm text-gray-500">{{ t('vendor.vendor_type') }}</p>
        <p class="mt-1 font-semibold text-gray-900">{{ store.item?.name }}</p>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700"
          type="button"
          @click="store.handleToggleModal"
        >
          {{ t('shared.actions.cancel') }}
        </BaseButton>
        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700" :disabled="removeLoading">
          <span v-if="removeLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-else>{{ t('shared.actions.delete') }}</span>
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate'
import { useVendorTypeMutations } from '@/modules/vendor/queries/useVendorTypeMutations'
import { useVendorTypeStore } from '@/modules/vendor/store/vendorTypeStore'

const { t } = useTranslate('vendor')
const store = useVendorTypeStore()

const { remove, removeLoading } = useVendorTypeMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
})

const handleSubmit = async () => {
  await remove.mutateAsync(store.item.id)
}
</script>

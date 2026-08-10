<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('vendor.view_type')"
    @close="store.handleToggleModal"
  >
    <div class="space-y-4">
      <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
        <p class="text-sm text-gray-500">{{ t('vendor.vendor_type') }}</p>
        <p class="mt-1 text-lg font-semibold text-gray-900">{{ store.item?.name || 'N/A' }}</p>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-gray-100 p-4">
          <p class="text-sm text-gray-500">{{ t('shared.labels.status') }}</p>
          <p class="mt-1 font-medium text-gray-900">{{ store.item?.status_formatted || 'N/A' }}</p>
        </div>
        <div class="rounded-lg border border-gray-100 p-4">
          <p class="text-sm text-gray-500">Code</p>
          <p class="mt-1 font-medium text-gray-900">{{ store.item?.code || 'N/A' }}</p>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate'
import { useVendorTypeStore } from '@/modules/vendor/store/vendorTypeStore'

const { t } = useTranslate('vendor')
const store = useVendorTypeStore()
</script>

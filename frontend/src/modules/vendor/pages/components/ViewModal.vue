<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('vendor.view')"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
  >
    <ViewModalLayout>
      <div
        v-can="'vendor.view_summary'"
        class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-lg border border-indigo-200"
      >
        <div class="flex items-center gap-3 mb-6">
          <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3 shadow-md">
            <i class="fa fa-building text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">{{ t('vendor.information') }}</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.id') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.id }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('vendor.organization_name') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.organization_name }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('vendor.vendor_type') }}</span>
            <p class="text-gray-800 font-semibold">
              {{ store.item.vendor_type_formatted || 'N/A' }}
            </p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('vendor.contact_person') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.contact_person || 'N/A' }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.address') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.address || 'N/A' }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.email') }}</span>
            <p class="text-gray-800 font-semibold break-all">{{ store.item.email }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.phone') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.phone }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.whatsapp') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.whatsapp_no }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">Vendor ID</span>
            <p class="text-gray-800 font-semibold">{{ store.item.vendor_id }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.status') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.status_formatted }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.send_notification') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.send_notification ? 'Yes' : 'No' }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.created_at') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.created_at }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.updated_at') }}</span>
            <p class="text-gray-800 font-semibold">{{ store.item.updated_at }}</p>
          </div>
        </div>
      </div>
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { useVendorStore } from '@/modules/vendor/store/vendorStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('vendor')
const store = useVendorStore()
</script>

<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('parties.view')"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
    @close="store.handleToggleModal"
  >
    <ViewModalLayout>
      <div class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-lg">
        <div class="mb-6 flex items-center gap-3">
          <div class="rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 p-3 text-white shadow-md">
            <i class="fa fa-users text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">{{ t('parties.information') }}</h3>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.id') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.id }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('parties.party_id') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.code }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('parties.party_type') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.type }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('parties.name') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.name }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('parties.opening_debit') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.opening_debit }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('parties.opening_credit') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.opening_credit }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.status') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.status }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm md:col-span-2">
            <span class="text-sm font-medium text-gray-600">{{ t('parties.remarks') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.remarks || '—' }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.created_at') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.created_at }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.created_by') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.created_by || '—' }}</p>
          </div>
        </div>
      </div>
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { usePartyStore } from '@/modules/parties/store/partyStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = usePartyStore()
</script>

<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('journal_transaction_types.view')"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
    @close="store.handleToggleModal"
  >
    <ViewModalLayout>
      <div class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-lg">
        <div class="mb-6 flex items-center gap-3">
          <div class="rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 p-3 text-white shadow-md">
            <i class="fa fa-exchange text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">
            {{ t('journal_transaction_types.information') }}
          </h3>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.id') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.id }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('journal_transaction_types.code') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.code }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('journal_transaction_types.name') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.name }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('journal_transaction_types.sort_order') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.sort_order }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('shared.labels.status') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.status }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('journal_transaction_types.subledger_required') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.subledger_required_label || 'No' }}</p>
          </div>
          <div class="rounded-lg bg-white p-4 shadow-sm">
            <span class="text-sm font-medium text-gray-600">{{ t('journal_transaction_types.demand_letter_required') }}</span>
            <p class="font-semibold text-gray-800">{{ store.item?.demand_letter_required_label || 'No' }}</p>
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
import { useJournalTransactionTypeStore } from '@/modules/journals/store/journalTransactionTypeStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useJournalTransactionTypeStore()
</script>

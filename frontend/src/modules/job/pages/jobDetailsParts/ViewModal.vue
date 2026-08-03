<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('job_price.view')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[50vw] w-full max-w-[95vw] sm:max-w-[90vw]'"
  >
    <!-- Scrollable Content -->
    <div class="space-y-3 max-h-[70vh] overflow-y-auto pb-5 pr-3">
      <p class="text-sm sm:text-lg break-words">
        <strong>{{t('shared.labels.id')}}: </strong>
        {{ store.item.id }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('shared.labels.job_name')}}: </strong>
        {{ store.item.job_name }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('job_price.fee_name')}}: </strong>
        {{ store.item.fee_name }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('job_price.fee_category')}}: </strong>
        {{ store.item.fee_category }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('job_price.amount')}}: </strong>
        ৳{{ store.item.amount }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('job_price.amount_usd')}}: </strong>
        ${{ store.item.amount_usd }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('shared.labels.created_at')}}: </strong>
        {{ store.item.created_at }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('shared.labels.updated_at')}}: </strong>
        {{ store.item.updated_at }}
      </p>
    </div>
  </BaseModal>
</template>

<script setup>
import { useJobDetailsStore } from '../../store/jobDetailsStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const store = useJobDetailsStore()

// Set modal width
store.modalWidth = '60vw'
</script>

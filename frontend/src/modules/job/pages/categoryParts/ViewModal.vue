<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('job_price.view_fee_category_details')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[50vw] w-full max-w-[95vw] sm:max-w-[90vw]'"
  >
    <div class="space-y-2">
      <p class="text-sm sm:text-base break-words">
        <strong>{{t('shared.labels.id')}}:</strong>
        {{ store.item.id }}
      </p>
      <p class="text-sm sm:text-base break-words">
        <strong>{{t('job_price.category')}}:</strong>
        {{ store.item.name }}
      </p>

      <p class="text-sm sm:text-base break-words">
        <strong>{{t('shared.labels.created_at')}}:</strong>
        {{ store.item.created_at }}
      </p>
      <p class="text-sm sm:text-base break-words">
        <strong>{{t('shared.labels.updated_at')}}:</strong>
        {{ store.item.updated_at }}
      </p>
    </div>
  </BaseModal>
</template>

<script setup>
import { useJobDetailsCategoryStore } from '../../store/jobDetailsCategoryStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const store = useJobDetailsCategoryStore()
</script>

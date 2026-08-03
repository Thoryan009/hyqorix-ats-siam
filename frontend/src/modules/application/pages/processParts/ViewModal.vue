<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('process.view_title')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[50vw]'"
  >
    <div class="space-y-2">
      <p class="text-base">
        <strong>{{ t('shared.labels.id') }}:</strong>
        {{ store.item.id }}
      </p>
      <p class="text-base">
        <strong>{{ t('shared.labels.name') }}:</strong>
        {{ store.item.name }}
      </p>

      <p class="text-base">
        <strong>{{ t('process.duration') }}:</strong>
        {{ store.item.duration }}
      </p>
      <p v-if="store.item.validity" class="text-base">
        <strong>{{ t('process.validity') }}:</strong>
        {{ store.item.validity }} days
      </p>
      <p v-if="store.item.notify_before" class="text-base">
        <strong>{{ t('process.notify_before') }}:</strong>
        {{ store.item.notify_before }} days
      </p>
      <p class="text-base">
        <strong>{{ t('shared.labels.created_at') }}:</strong>
        {{ store.item.created_at }}
      </p>
      <p class="text-base">
        <strong>{{ t('shared.labels.updated_at') }}:</strong>
        {{ store.item.updated_at }}
      </p>
    </div>
  </BaseModal>
</template>

<script setup>
import { useProcessStore } from '../../store/processStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useProcessStore()
</script>

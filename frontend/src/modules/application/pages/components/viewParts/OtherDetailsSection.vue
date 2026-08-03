<template>
  <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm">

    <!-- Header -->
    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-emerald-100">
      <div class="bg-emerald-500 text-white rounded-md p-1.5 flex items-center justify-center">
        <i class="fa fa-folder-open text-sm"></i>
      </div>
      <h3 class="text-base font-semibold text-emerald-800 tracking-wide">{{ t('application.other_documents') }}</h3>
    </div>

    <!-- Two-column grid -->
    <div class="grid grid-cols-2 gap-3">

      <!-- General Documents -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-file-text text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.general_documents') }}</span>
        </div>
        <a
          v-if="item.documents_url"
          :href="item.documents_url"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>

      <!-- Offer Letter -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-envelope-open text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.offer_letter') }}</span>
        </div>
        <a
          v-if="offerLetter"
          :href="offerLetter"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>

      <!-- Acknowledgment -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-check-square text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.acknowledgment') }}</span>
        </div>
        <a
          v-if="item.acknowledgment_url"
          :href="item.acknowledgment_url"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>

      <!-- Visa Copy -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-id-card text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.visa_copy') }}</span>
        </div>
        <a
          v-if="item.visa_copy_url"
          :href="item.visa_copy_url"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>

      <!-- Immigration Clearance -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-globe text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.immigration_clearance') }}</span>
        </div>
        <a
          v-if="item.immigration_clearance_url"
          :href="item.immigration_clearance_url"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>

      <!-- SVP -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-certificate text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.svp') }}</span>
        </div>
        <a
          v-if="item.svp_url"
          :href="item.svp_url"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>

      <!-- QVP -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-certificate text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.qvp') }}</span>
        </div>
        <a
          v-if="item.qvp_url"
          :href="item.qvp_url"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>

         <!-- Ticket copy -->
      <div
        class="flex flex-col gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg p-3 hover:border-emerald-300 transition-colors"
      >
        <div class="flex items-center gap-1.5 text-emerald-700">
          <i class="fa fa-ticket text-xs"></i>
          <span class="text-xs font-semibold uppercase tracking-wider">{{ t('application.ticket') }}</span>
        </div>
        <a
          v-if="item.ticket_url"
          :href="item.ticket_url"
          target="_blank"
          class="inline-flex items-center gap-1.5 self-start bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-md font-medium transition-colors"
        >
          <i class="fa fa-external-link text-xs"></i>
          {{ t('shared.action.view') }}
        </a>
        <span v-else class="inline-flex items-center gap-1 text-gray-400 text-xs italic">
          <i class="fa fa-minus-circle text-xs"></i> {{ t('application.not_available') }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const props = defineProps({
  item: Object,
})

const offerLetter = computed(() => {
  return props.item?.offer_letter_link || props.item?.offer_letter_url || null
})
</script>

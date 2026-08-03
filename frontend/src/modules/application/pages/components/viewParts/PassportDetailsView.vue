<template>
  <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl p-6 shadow-lg border border-teal-200">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-gradient-to-br from-teal-500 to-teal-600 text-white rounded-lg p-3 shadow-md">
        <i class="fa fa-book text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-teal-800">{{t('application.passport_details')}}</h3>
    </div>

    <!-- Content Grid -->
    <div class="space-y-4">
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-hashtag text-teal-500"></i>
          <span class="text-sm font-medium">{{t('shared.labels.passport_no')}}</span>
        </div>
        <p class="text-gray-800 font-semibold text-lg">{{ item.passport_no }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center gap-2 text-gray-600 mb-1">
            <i class="fa fa-calendar-check-o text-teal-500"></i>
            <span class="text-sm font-medium">{{t('application.date_of_issue')}}</span>
          </div>
          <p class="text-gray-800 font-semibold">{{ item.date_of_issue }}</p>
        </div>

        <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center gap-2 text-gray-600 mb-1">
            <i class="fa fa-calendar-times-o text-teal-500"></i>
            <span class="text-sm font-medium">{{t('application.date_of_expiry')}}</span>
          </div>
          <p class="text-gray-800 font-semibold">{{ item.date_of_expiry }}</p>
        </div>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-2">
          <i class="fa fa-paperclip text-teal-500"></i>
          <span class="text-sm font-medium">{{t('application.passport_document')}}</span>
        </div>
        <div class="flex flex-wrap gap-3">
          <a v-if="passportUrl" :href="passportUrl" target="_blank"
             class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm hover:shadow-md">
            <i class="fa fa-external-link"></i>
            {{t('shared.labels.preview_url')}}
          </a>
          <span v-else class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
            <i class="fa fa-times-circle"></i>
            URL: N/A
          </span>

          <a v-if="passportLink" :href="passportLink" target="_blank"
             class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm hover:shadow-md">
            <i class="fa fa-external-link"></i>
             {{t('shared.labels.preview_link')}}
          </a>
          <span v-else class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
            <i class="fa fa-times-circle"></i>
            Link: N/A
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const props = defineProps({
  item: Object
});

const passportUrl = computed(() => {
  return props.item?.passport_url || null
})

const passportLink = computed(() => {
  return props.item?.passport_link || null
})
</script>

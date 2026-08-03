<template>
  <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 shadow-lg border border-orange-200">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg p-3 shadow-md">
        <i class="fa fa-id-card text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-orange-800">{{t('application.nid_details')}}</h3>
    </div>

    <!-- Content Grid -->
    <div class="space-y-4">
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-hashtag text-orange-500"></i>
          <span class="text-sm font-medium">{{t('application.nid_number')}}</span>
        </div>
        <p class="text-gray-800 font-semibold text-lg">{{ item.nid_no }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-2">
          <i class="fa fa-paperclip text-orange-500"></i>
          <span class="text-sm font-medium">{{t('application.nid_document')}}</span>
        </div>
        <div class="flex flex-wrap gap-3">
          <a v-if="nidUrl" :href="nidUrl" target="_blank"
             class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm hover:shadow-md">
            <i class="fa fa-external-link"></i>
            {{t('shared.labels.preview_url')}}
          </a>
          <span v-else class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
            <i class="fa fa-times-circle"></i>
            URL: N/A
          </span>

          <a v-if="nidLink" :href="nidLink" target="_blank"
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

const nidUrl = computed(() => {
  return props.item?.nid_url || null
})

const nidLink = computed(() => {
  return props.item?.nid_link || null
})
</script>

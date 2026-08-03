<template>
  <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-lg border border-indigo-200">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3 shadow-md">
        <i class="fa fa-file-text text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-indigo-800">{{t('application.resume')}} {{t('shared.titles.details')}}</h3>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow text-center">
      <div class="flex flex-col items-center gap-4">
        <div class="bg-indigo-100 rounded-full p-6">
          <i class="fa fa-file-pdf-o text-5xl text-indigo-600"></i>
        </div>

        <div>
          <p class="text-gray-600 mb-3 font-medium">{{t('application.resume')}} {{t('shared.labels.document')}}</p>
          <div class="flex flex-wrap gap-3 justify-center">
            <a v-if="resumeUrl" :href="resumeUrl" target="_blank"
               class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-md hover:shadow-lg">
              <i class="fa fa-external-link"></i>
              {{t('shared.labels.preview_url')}}
            </a>
            <span v-else class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-6 py-3 rounded-lg">
              <i class="fa fa-times-circle"></i>
              URL: N/A
            </span>

            <a v-if="resumeLink" :href="resumeLink" target="_blank"
               class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-md hover:shadow-lg">
              <i class="fa fa-external-link"></i>
              {{t('shared.labels.preview_link')}}
            </a>
            <span v-else class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-6 py-3 rounded-lg">
              <i class="fa fa-times-circle"></i>
              Link: N/A
            </span>
          </div>
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

const resumeUrl = computed(() => {
  return props.item?.resume_url || null
})

const resumeLink = computed(() => {
  return props.item?.resume_link || null
})
</script>

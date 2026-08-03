<template>
  <div
    class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-lg border border-blue-200"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-3 shadow-md">
        <i class="fa fa-user text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-blue-800">Personal Information</h3>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-hashtag text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.id') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.id }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-id-badge text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('application.name') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.full_name }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-calendar text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('application.date_of_birth') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.date_of_birth }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-venus-mars text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('application.sex') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.sex }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-flag text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('application.nationality') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.nationality }}</p>
      </div>

      <div
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow md:col-span-2"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-map-marker text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('application.place_of_birth') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.place_of_birth }}</p>
      </div>

      <div
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow md:col-span-2"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-2">
          <i class="fa fa-image text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('application.worker_image') }}</span>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            v-if="workerImageUrl"
            :href="workerImageUrl"
            target="_blank"
            class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm hover:shadow-md"
          >
            <i class="fa fa-external-link"></i>
            {{ t('shared.labels.preview_url') }}
          </a>
          <span
            v-else
            class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm"
          >
            <i class="fa fa-times-circle"></i>
            URL: N/A
          </span>

          <a
            v-if="workerImageLink"
            :href="workerImageLink"
            target="_blank"
            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm hover:shadow-md"
          >
            <i class="fa fa-external-link"></i>
            {{ t('shared.labels.preview_link') }}
          </a>
          <span
            v-else
            class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm"
          >
            <i class="fa fa-times-circle"></i>
            Link: N/A
          </span>
        </div>
      </div>
      <div
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow md:col-span-2"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-2">
          <i class="fa fa-file-pdf-o text-blue-500"></i>
          <span class="text-sm font-medium">{{ t('application.single_document') }}</span>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            v-if="singleDocumentUrl"
            :href="singleDocumentUrl"
            target="_blank"
            class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm hover:shadow-md"
          >
            <i class="fa fa-external-link"></i>
           {{ t('shared.labels.preview_url') }}
          </a>
          <span
            v-else
            class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm"
          >
            <i class="fa fa-times-circle"></i>
            URL: N/A
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
  item: Object,
})

const workerImageUrl = computed(() => {
  return props.item?.worker_image_url || null
})

const workerImageLink = computed(() => {
  return props.item?.worker_image_link || null
})

const singleDocumentUrl = computed(() => {
  return props.item?.single_document_url || null
})


</script>

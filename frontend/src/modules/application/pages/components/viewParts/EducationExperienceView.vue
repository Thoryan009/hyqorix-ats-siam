<template>
  <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-lg border border-purple-200">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-3 shadow-md">
        <i class="fa fa-graduation-cap text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-purple-800">{{t('application.education_experience')}}</h3>
    </div>

    <!-- Content Grid -->
    <div class="space-y-4">
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-certificate text-purple-500"></i>
          <span class="text-sm font-medium">{{t('job.qualification')}}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.qualification }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-book text-purple-500"></i>
          <span class="text-sm font-medium">{{t('application.subject')}}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.subject }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-briefcase text-purple-500"></i>
          <span class="text-sm font-medium">{{t('application.bd_experience')}}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.bd_exp }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-globe text-purple-500"></i>
          <span class="text-sm font-medium">{{t('application.overseas_experience')}}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.overseas_exp }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-language text-purple-500"></i>
          <span class="text-sm font-medium">{{t('job.language')}}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.language }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
defineProps({
  item: Object
});
</script>

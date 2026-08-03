<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <!-- Toggle Header -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="w-full flex items-center justify-between pb-3 border-b-2 border-cyan-500 text-left group"
    >
      <div class="flex items-center gap-3">
        <span
          class="flex items-center justify-center w-8 h-8 rounded-full bg-cyan-500 text-white text-sm font-bold shrink-0"
        >8</span>
        <i class="fa fa-link text-cyan-500 text-xl"></i>
        <span
          class="font-semibold text-lg text-gray-800 group-hover:text-cyan-600 transition-colors"
        >{{ t('application.document_links') }}</span>
      </div>
      <i
        class="fa text-gray-400 transition-transform duration-200"
        :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"
      ></i>
    </button>

    <!-- Collapsible Links Form -->
    <div v-show="isOpen" class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
      <div>
        <BaseLabel for="nid_link">{{ t('application.nid_link_optional') }}</BaseLabel>
        <BaseInput
          id="nid_link"
          v-model="store.formData.nid_link"
          :placeholder="t('application.drive_link_example')"
        />
      </div>

      <div>
        <BaseLabel for="passport_link">{{ t('application.passport_link_optional') }}</BaseLabel>
        <BaseInput
          id="passport_link"
          v-model="store.formData.passport_link"
          :placeholder="t('application.drive_link_example')"
        />
      </div>

      <div>
        <BaseLabel for="resume_link">{{ t('application.resume_link_optional') }}</BaseLabel>
        <BaseInput
          id="resume_link"
          v-model="store.formData.resume_link"
          :placeholder="t('application.drive_link_example')"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useApplicationStore()
const isOpen = ref(false)
</script>

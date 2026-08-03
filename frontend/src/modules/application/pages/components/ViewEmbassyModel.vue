<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`Embassy Submission`"
    @close="store.handleToggleModal"
    :className="'max-w-[55vw] xl:max-w-[55vw] h-[55vh]'"
  >
    <div class=" px-2 py-4 space-y-6">
        <!-- Top: Application Summary -->
        <div class="flex items-center gap-4 p-4 bg-white rounded-lg shadow-sm">
          <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
            <img v-if="store.item.worker_image_url" :src="store.item.worker_image_url" class="w-full h-full object-cover" />
            <i v-else class="fa fa-user text-gray-400 text-2xl"></i>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-800">{{ store.item.full_name }}</h3>
            <p class="text-sm text-gray-600">Job: <span class="font-medium">{{ store.item.job }}</span></p>
            <p class="text-sm text-gray-600">Passport: <span class="font-medium">{{ store.item.passport_no || 'N/A' }}</span></p>
            <p class="text-sm text-gray-600">Nationality: <span class="font-medium">{{ store.item.nationality || 'N/A' }}</span></p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">Applied ID</p>
            <p class="text-sm font-semibold text-gray-800">{{ store.item.application_id || store.item.id }}</p>
          </div>
        </div>

        <!-- Form fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div>
  <label class="text-sm font-medium text-gray-700 mb-2 block">
    Religion
  </label>

  <div class="grid grid-cols-2 gap-3">

    <!-- Muslim -->
    <label
      class="cursor-pointer rounded-xl border p-4 transition-all duration-200"
      :class="
        formData.religion === 'muslim'
          ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200'
          : 'border-gray-200 hover:border-indigo-300'
      "
    >
      <input
        v-model="formData.religion"
        type="radio"
        value="muslim"
        class="hidden"
      />

      <div class="flex items-center justify-between">
        <div>
          <p class="font-semibold text-gray-800">Muslim</p>
          <p class="text-xs text-gray-500">Islam religion</p>
        </div>

        <div
          class="w-5 h-5 rounded-full border flex items-center justify-center"
          :class="
            formData.religion === 'muslim'
              ? 'border-indigo-600'
              : 'border-gray-300'
          "
        >
          <div
            v-if="formData.religion === 'muslim'"
            class="w-2.5 h-2.5 rounded-full bg-indigo-600"
          ></div>
        </div>
      </div>
    </label>

    <!-- Non Muslim -->
    <label
      class="cursor-pointer rounded-xl border p-4 transition-all duration-200"
      :class="
        formData.religion === 'non-muslim'
          ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200'
          : 'border-gray-200 hover:border-indigo-300'
      "
    >
      <input
        v-model="formData.religion"
        type="radio"
        value="non-muslim"
        class="hidden"
        readonly=""
      />

      <div class="flex items-center justify-between">
        <div>
          <p class="font-semibold text-gray-800">Non-Muslim</p>
          <p class="text-xs text-gray-500">Other religion</p>
        </div>

        <div
          class="w-5 h-5 rounded-full border flex items-center justify-center"
          :class="
            formData.religion === 'non-muslim'
              ? 'border-indigo-600'
              : 'border-gray-300'
          "
        >
          <div
            v-if="formData.religion === 'non-muslim'"
            class="w-2.5 h-2.5 rounded-full bg-indigo-600"
          ></div>
        </div>
      </div>
    </label>

  </div>
</div>

          <div>
            <label class="text-sm font-medium text-gray-700">Visa Profession (Arabic)</label>
            <input v-model="formData.visa_profession_ar" type="text"  class="mt-1 block w-full border rounded-md p-2" placeholder="Arabic profession" />
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Visa Profession (English)</label>
            <input v-model="formData.visa_profession_en" type="text" class="mt-1 block w-full border rounded-md p-2" placeholder="English profession (optional)" />
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Visit / Work For (Arabic)</label>
            <input v-model="formData.visit_work_for_ar" type="text" class="mt-1 block w-full border rounded-md p-2" placeholder="Arabic text" />
          </div>
        </div>

        <!-- Embassy data display -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Religion</h4>
            <p class="text-gray-800">{{ embData.religion ? (embData.religion === 'muslim' ? 'Muslim' : 'Non-Muslim') : '—' }}</p>
          </div>

          <div class="bg-white p-4 rounded-lg shadow-sm">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Visa Profession (Arabic)</h4>
            <p class="text-gray-800 break-words">{{ embData.visa_profession_ar || '—' }}</p>
          </div>

          <div class="bg-white p-4 rounded-lg shadow-sm">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Visa Profession (English)</h4>
            <p class="text-gray-800 break-words">{{ embData.visa_profession_en || '—' }}</p>
          </div>

          <div class="bg-white p-4 rounded-lg shadow-sm">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Visit / Work For (Arabic)</h4>
            <p class="text-gray-800 break-words">{{ embData.visit_work_for_ar || '—' }}</p>
          </div>
        </div>

        <div class="flex justify-end pt-4">
          <BaseButton type="button" class="bg-gray-200" @click="store.handleToggleModal">Close</BaseButton>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { watch, ref } from 'vue'
import { useApplicationStore } from '../../store/applicationStore'

const store = useApplicationStore()

const embData = ref({
  application_id: null,
  religion: null,
  visa_profession_ar: '',
  visa_profession_en: '',
  visit_work_for_ar: '',
})

defineProps({
  jobs: {
    type: Array,
    default: () => [],
  },
  subjects: {
    type: Array,
    default: () => [],
  },
  qualifications: {
    type: Array,
    default: () => [],
  },
  agents: {
    type: Array,
    default: () => [],
  defineProps({
    jobs: {
      type: Array,
      default: () => [],
    },
    subjects: {
      type: Array,
      default: () => [],
    },
    qualifications: {
      type: Array,
      default: () => [],
    },
    agents: {
      type: Array,
      default: () => [],
    },
  })

  // Prefill when modal opens or selected row changes
  watch(
    () => store.item,
    (val) => {
      if (!val) return
      const emb = val.embassy_submission || {}
      embData.value = {
        application_id: val.id,
        religion: emb.religion ?? null,
        visa_profession_ar: emb.visa_profession_ar ?? '',
        visa_profession_en: emb.visa_profession_en ?? '',
        visit_work_for_ar: emb.visit_work_for_ar ?? '',
      }
    },
    { immediate: true },
  )

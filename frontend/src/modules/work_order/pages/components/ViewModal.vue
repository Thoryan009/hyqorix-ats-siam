<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('demand_letter.view')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[80vw] w-full max-w-[95vw] sm:max-w-[90vw]'"
  >
    <ViewModalLayout height="80vh">
      <!-- Main Content - Colorful Summary Card -->

      <div
        v-can="'demand_letter.view_summary'"
        class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-lg border border-purple-200 mb-6"
      >
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
          <div
            class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-3 shadow-md"
          >
            <i class="fa fa-file-text text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-purple-800">{{ t('demand_letter.summary') }}</h3>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- ID -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-hashtag text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.id') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.id }}</p>
          </div>

          <!-- Demand Letter -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-file-text-o text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.demand_letter') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.work_order_id }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-2">
              <i class="fa fa-image text-blue-500"></i>
              <span class="text-sm font-medium">{{ t('demand_letter.image') }}</span>
            </div>

            <!-- <pre>{{ store.item }}</pre> -->
            <div class="flex flex-wrap gap-3">
              <a
                v-if="store.item.work_order_url"
                :href="store.item.work_order_url"
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

          <!-- Candidates -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-users text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.candidates') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.candidates }}</p>
          </div>

          <!-- End Date -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-calendar text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('demand_letter.end_date') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.end_date }}</p>
          </div>

          <!-- Client -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-building text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.client') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.client }}</p>
          </div>

          <!-- Created At -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-calendar-plus-o text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.created_at') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.created_at }}</p>
          </div>

          <!-- Updated At -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-calendar-check-o text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.updated_at') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.updated_at }}</p>
          </div>
          <!-- Created by -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.created_by') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.created_by }}</p>
          </div>
          <!-- Updated by -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-purple-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.updated_by') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.updated_by || 'N/A' }}</p>
          </div>
        </div>
      </div>

     
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useWorkOrderStore } from '@/modules/work_order/store/workOrderStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('work_order')
const store = useWorkOrderStore()
const activeTab = ref('price')

const columnTemp = computed(() => {
  return [
    { key: 'job_code', label: t('shared.labels.job_code') },
    { key: 'work_order', label: t('shared.labels.demand_letter') },
    { key: 'name', label: t('shared.labels.job_name') },
    { key: 'vacancy', label: t('shared.labels.vacancy') },
    { key: 'experience', label: t('shared.labels.experience') },
    { key: 'min_age', label: t('job.min_age') },
    { key: 'max_age', label: t('job.max_age') },
    { key: 'qualification', label: t('job.qualification') },
    { key: 'salary', label: t('shared.labels.salary') },
  ]
})

const { columns } = useCrudTable(
  store,
  columnTemp,
  {
    trackUser: false,
    timestamps: false,
  },
)
</script>

<style scoped>
/* Fade in animation for tab content */
.animate-fadeIn {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

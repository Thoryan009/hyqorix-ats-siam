<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('principal.view')"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
  >
    <ViewModalLayout>
      <!-- Principal Details Section -->
      <div
        v-can="'principal.view_summary'"
        class="bg-linear-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-lg border border-indigo-200"
      >
        <!-- Header -->

        <div class="flex items-center gap-3 mb-6">
          <div
            class="bg-linear-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3 shadow-md"
          >
            <i class="fa fa-building text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">{{ t('principal.information') }}</h3>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- ID -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-hashtag text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('principal.principal_id') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.principal_id }}</p>
          </div>

          <!-- Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('principal.organization_name') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.organization_name || 'N/A' }}</p>
          </div>
          <!-- Email -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-envelope text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.email') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.email || 'N/A' }}</p>
          </div>
           <!-- Role -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user-shield text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.role') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.role ?? 'N/A' }}</p>
          </div>


          <!-- NID No -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-id-card text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.country') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.country || 'N/A' }}</p>
          </div>

          <!-- Address -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-map-marker text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.address') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.address || 'N/A' }}</p>
          </div>

          <!-- Phone -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-phone text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.phone') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.contact_no || 'N/A' }}</p>
          </div>


          <!-- Whatsapp No -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-whatsapp text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.whatsapp_no') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.whatsapp_no || 'N/A' }}</p>
          </div>

          <!-- Contact Person Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('principal.contact_person_name') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.contact_person_name || 'N/A' }}</p>
          </div>

          <!-- Designation -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-briefcase text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('principal.designation') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.designation || 'N/A' }}</p>
          </div>

          <!-- Contact Person No -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-phone text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('principal.contact_person_number') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.contact_person_no || 'N/A' }}</p>
          </div>

          <!-- Send Notification -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-bell text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.send_notification') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.send_notification ? 'Yes' : 'No' }}</p>
          </div>

          <!-- Created At -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-calendar-plus-o text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.created_at') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.created_at }}</p>
          </div>

          <!-- Updated At -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-calendar-check-o text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.updated_at') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.updated_at }}</p>
          </div>
        </div>
      </div>

      <!-- Job Lists -->
      <div class="pb-5!">
        <h2 class="text-lg font-semibold py-5">{{ t('principal.module') }} {{ t('shared.labels.job_lists') }}</h2>
        <BaseTable
          v-can="'principal.view_job_lists'"
          v-if="!isLoading"
          :columns="columns"
          :rows="store.item.job_lists"
        />
      </div>
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { usePrincipalStore } from '@/modules/principal/store/principalStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import {useTranslate} from '@/shared/composables/useTranslate'
import { computed } from 'vue'

const {t} = useTranslate()
const store = usePrincipalStore()

const columnTemp = computed (() => {
  return [
    { key: 'name', label: t('shared.labels.job_name') },
    { key: 'job_code', label: t('shared.labels.job_code') },
    { key: 'payer', label: t('shared.labels.payer') },
    { key: 'principal_name', label: t('principal.principal_name') },
    { key: 'work_order', label: t('shared.labels.demand_letter') },
    { key: 'vacancy', label: t('shared.labels.vacancy') },
    { key: 'experience', label: t('shared.labels.experience') },
    { key: 'salary', label: t('shared.labels.salary') },
    { key: 'status', label: t('shared.labels.status') },
    { key: 'price', label: t('shared.labels.price_taka') },
    { key: 'contract_length', label: t('shared.labels.contract_length') },
    { key: 'deadline', label: t('shared.labels.deadline') },
    { key: 'interview_date', label: t('shared.labels.interview_date') },
  ]
})
const { columns } = useCrudTable(
  store,
  columnTemp,
  { timestamps: false, trackUser: false },
)
</script>

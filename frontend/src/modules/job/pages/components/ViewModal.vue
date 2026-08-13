<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('job.view')"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
  >
    <ViewModalLayout>
      <!-- Job Details Section -->
      <div
        v-can="'job.view_summary'"
        class="bg-linear-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-lg border border-indigo-200 mb-6"
      >
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
          <div
            class="bg-linear-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3 shadow-md"
          >
            <i class="fa fa-briefcase text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">{{ t('job.job_information') }}</h3>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Job Code -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-code text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.job_code') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.job_code }}</p>
          </div>

          <!-- Job Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-tag text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.job_name') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.name }}</p>
          </div>

          <!-- Vacancy -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-users text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.vacancy') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.vacancy }}</p>
          </div>

          <!-- Status -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i
                :class="[
                  'fa',
                  store.item.status === 'open'
                    ? 'fa-check-circle text-green-500'
                    : store.item.status === 'closed'
                      ? 'fa-times-circle text-red-500'
                      : 'fa-pause-circle text-yellow-500',
                ]"
              ></i>
              <span class="text-sm font-medium">{{ t('shared.labels.status') }}</span>
            </div>
            <p
              class="font-semibold"
              :class="{
                'text-green-700': store.item.status === 'open',
                'text-red-700': store.item.status === 'closed',
                'text-yellow-700': store.item.status === 'hold',
              }"
            >{{ store.item.status }}</p>
          </div>
        </div>
      </div>

      <!-- Tabs Section -->
      <div class="bg-white rounded-xl border-2 border-gray-200 shadow-md overflow-hidden">
        <!-- Tab Headers -->
        <!-- <div class="flex bg-gray-50">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex-1 px-4 py-4 text-sm font-semibold transition-all duration-300 flex items-center justify-center gap-2 relative',
              activeTab === tab.id
                ? `${tab.activeClass} border-b-4 shadow-md transform scale-105`
                : `${tab.inactiveClass} hover:bg-gray-100 border-b-4 border-transparent`,
            ]"
          >
            <i :class="['fa', tab.icon, 'text-lg']"></i>
            <span>{{ tab.label }}</span>
          </button>
        </div> -->

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Job Overview Tab -->
          <div v-can="'job.view_job_overviews'" v-if="activeTab === 'overview'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Experience -->
              <div class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-clock-o text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">{{ t('shared.labels.experience_required') }}</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.experience }}</p>
                  </div>
                </div>
              </div>

              <!-- Contract Length -->
              <div class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">{{ t('shared.labels.contract_length') }}</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.contract_length }}</p>
                  </div>
                </div>
              </div>

              <!-- Age Range -->
              <div class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-birthday-cake text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">{{ t('shared.labels.age_range') }}</p>
                    <p
                      class="text-lg font-bold text-blue-900"
                    >{{ store.item.min_age }} - {{ store.item.max_age }} years</p>
                  </div>
                </div>
              </div>

              <!-- Qualification -->
              <div class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-graduation-cap text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">{{ t('job.qualification') }}</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.qualification }}</p>
                  </div>
                </div>
              </div>

              <!-- Language -->
              <div class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-language text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">{{ t('job.language') }}</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.language }}</p>
                  </div>
                </div>
              </div>

              <!-- Applications Count -->
              <div class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-file-text text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">{{t('shared.labels.applications')}}</p>
                    <p
                      class="text-lg font-bold text-blue-900"
                    >{{ store.item.applications ? store.item.applications.length : 0 }}</p>
                  </div>
                </div>
              </div>
              <!-- Principal Name -->
              <div v-can="'job.select_principal'" class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow lg:col-span-2">
                <div class="flex items-center gap-3">
                  <i class="fa fa-file-text text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">{{ t('shared.labels.principal') }} {{ t('shared.labels.name') }}</p>
                    <p
                      class="text-lg font-bold text-blue-900"
                    >{{ store.item.principal_name ?? 'N/A' }}</p>
                  </div>
                </div>
              </div>


              <!-- Description (Full Width) -->
              <div
                class="bg-blue-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow md:col-span-2 lg:col-span-3"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-align-left text-2xl text-blue-600"></i>
                  <div class="flex-1">
                    <p class="text-xs font-medium text-blue-600 uppercase">{{ t('job.description') }} </p>
                    <p class="text-base font-semibold text-blue-900">{{ store.item.description }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Application Details Tab -->
          <div
            v-can="'job.view_applicant_details'"
            v-if="activeTab === 'details'"
            class="space-y-4"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Salary -->
              <div class="bg-green-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-money text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">{{ t('shared.labels.salary') }}</p>
                    <p class="text-lg font-bold text-green-900">{{ store.item.salary }}</p>
                  </div>
                </div>
              </div>

              <!-- Work Order -->
              <div class="bg-green-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-file-text-o text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">{{ t('shared.labels.demand_letter') }}</p>
                    <p class="text-lg font-bold text-green-900">{{ store.item.work_order }}</p>
                  </div>
                </div>
              </div>

              <!-- Deadline -->
              <div class="bg-green-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar-times-o text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">{{ t('shared.labels.deadline') }}</p>
                    <p
                      class="text-lg font-bold text-green-900"
                    >{{ formatDate(store.item.deadline) }}</p>
                  </div>
                </div>
              </div>

              <!-- Interview Date -->
              <div class="bg-green-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar-check-o text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">{{ t('shared.labels.interview_date') }}</p>
                    <p
                      class="text-lg font-bold text-green-900"
                    >{{ formatDate(store.item.interview_date) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- System Information Tab -->
          <div
            v-can="'job.view_system_informations'"
            v-if="activeTab === 'system'"
            class="space-y-4"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Created At -->
              <div class="bg-gray-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar-plus-o text-2xl text-gray-600"></i>
                  <div>
                    <p class="text-xs font-medium text-gray-600 uppercase">{{ t('shared.labels.created_at') }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ store.item.created_at }}</p>
                  </div>
                </div>
              </div>

              <!-- Updated At -->
              <div class="bg-gray-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar-check-o text-2xl text-gray-600"></i>
                  <div>
                    <p class="text-xs font-medium text-gray-600 uppercase">{{ t('shared.labels.updated_at') }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ store.item.updated_at }}</p>
                  </div>
                </div>
              </div>

              <!-- Created By -->
              <div class="bg-gray-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-user-plus text-2xl text-gray-600"></i>
                  <div>
                    <p class="text-xs font-medium text-gray-600 uppercase">{{ t('shared.labels.created_by') }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ store.item.created_by }}</p>
                  </div>
                </div>
              </div>

              <!-- Updated By -->
              <div class="bg-gray-50 rounded-lg p-4 shadow hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                  <i class="fa fa-user-circle-o text-2xl text-gray-600"></i>
                  <div>
                    <p class="text-xs font-medium text-gray-600 uppercase">{{ t('shared.labels.updated_by') }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ store.item.updated_by || 'N/A' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    



      <!-- Application Details -->
      <!-- <h2 class="text-lg font-semibold py-5">{{ store.item.name }} {{ t('shared.labels.applicants') }}</h2>
      <BaseTable v-if="!isLoading" :columns="columns" :rows="store.item.applications" />-->
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useJobStore } from '@/modules/job/store/jobStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import JobListDetailsTables from './JobListDetailsTables.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const store = useJobStore()

// Tab configuration
const activeTab = ref('overview')
const activeTab2 = ref('applicants')
const tabs = computed(() => [
  {
    id: 'overview',
    label: t('job.job_overview'),
    icon: 'fa-info-circle',
    activeClass: 'bg-gradient-to-br from-indigo-500 to-indigo-600 text-white',
    inactiveClass: 'text-gray-600',
  },
  {
    id: 'details',
    label: t('application.application_details'),
    icon: 'fa-file-text-o',
    activeClass: 'bg-gradient-to-br from-green-500 to-green-600 text-white',
    inactiveClass: 'text-gray-600',
  },
  {
    id: 'system',
    label: t('job.system_information'),
    icon: 'fa-cogs',
    activeClass: 'bg-gradient-to-br from-yellow-500 to-yellow-600 text-white',
    inactiveClass: 'text-gray-600',
  },
])

const columnTemp = computed(() => {
  return [
    { key: 'job', label: t('shared.labels.job_name') },
    { key: 'given_name', label: t('application.given_name') },
    { key: 'sur_name', label: t('application.surname') },
    { key: 'sex', label: t('application.sex') },
    { key: 'date_of_birth', label: t('application.date_of_birth') },
    { key: 'mobile', label: t('shared.labels.phone') },
    { key: 'email', label: t('shared.labels.email') },
    { key: 'passport_no', label: t('shared.labels.passport_no') },
    { key: 'date_of_expiry', label: t('application.date_of_expiry') },
    { key: 'application_id', label: t('shared.labels.application_id') },
    { key: 'status', label: t('shared.labels.status') },
  ]
})


const { columns } = useCrudTable(store, columnTemp)

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}
</script>

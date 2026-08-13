<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('application.view')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[80vw]'"
  >
    <ViewModalLayout :height="'70vh'">
      <!-- Tab Navigation -->
      <div class="bg-linear-to-r from-gray-50 to-gray-100 rounded-lg p-2 shadow-sm">

        <div class="flex overflow-x-auto scrollbar-hide gap-2 px-2">
          <!-- Personal Information Tab -->
          <button
          v-can="'application.view_personal_information'"
            @click="activeTab = 'personal'"
            :class="[
              activeTab === 'personal'
                ? 'bg-linear-to-br from-blue-500 to-blue-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-blue-50 hover:text-blue-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-user text-lg"></i>
            <span>{{ t('application.personal_info') }}</span>
          </button>

          <!-- Education Tab -->
          <button
            v-can="'application.view_education'"
            @click="activeTab = 'education'"
            :class="[
              activeTab === 'education'
                ? 'bg-linear-to-br from-purple-500 to-purple-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-purple-50 hover:text-purple-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-graduation-cap text-lg"></i>
            <span>{{ t('application.education') }}</span>
          </button>

          <!-- Contact Tab -->
          <button
            v-can="'application.view_contact'"
            @click="activeTab = 'contact'"
            :class="[
              activeTab === 'contact'
                ? 'bg-linear-to-br from-green-500 to-green-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-green-50 hover:text-green-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-phone text-lg"></i>
            <span>{{ t('application.contact') }}</span>
          </button>

          <!-- NID Tab -->
          <button
            v-can="'application.view_nid_details'"
            @click="activeTab = 'nid'"
            :class="[
              activeTab === 'nid'
                ? 'bg-linear-to-br from-orange-500 to-orange-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-orange-50 hover:text-orange-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-id-card text-lg"></i>
            <span>{{ t('application.nid_details') }}</span>
          </button>

          <!-- Passport Tab -->
          <button
            v-can="'application.view_passport'"
            @click="activeTab = 'passport'"
            :class="[
              activeTab === 'passport'
                ? 'bg-linear-to-br from-teal-500 to-teal-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-teal-50 hover:text-teal-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-book text-lg"></i>
            <span>{{ t('application.passport') }}</span>
          </button>

          <!-- Resume Tab -->
          <button
            v-can="'application.view_resume'"
            @click="activeTab = 'resume'"
            :class="[
              activeTab === 'resume'
                ? 'bg-linear-to-br from-indigo-500 to-indigo-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-indigo-50 hover:text-indigo-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-file-text text-lg"></i>
            <span>{{ t('application.resume') }}</span>
          </button>

          <!-- Application Tab -->
          <button
            v-can="'application.view_application'"
            @click="activeTab = 'application'"
            :class="[
              activeTab === 'application'
                ? 'bg-linear-to-br from-pink-500 to-pink-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-pink-50 hover:text-pink-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-briefcase text-lg"></i>
            <span>{{t('application.module')}}</span>
          </button>

          <!-- Others Tab -->
          <button
            v-can="'application.view_documents'"
            @click="activeTab = 'others'"
            :class="[
              activeTab === 'others'
                ? 'bg-linear-to-br from-gray-600 to-gray-700 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-700',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-ellipsis-h text-lg"></i>
            <span>{{ t('application.other_documents') }}</span>
          </button>

          <!-- System Information Tab -->
          <button
            v-can="'application.view_system_info'"
            @click="activeTab = 'system'"
            :class="[
              activeTab === 'system'
                ? 'bg-linear-to-br from-cyan-500 to-cyan-600 text-white shadow-md scale-105'
                : 'bg-white text-gray-600 hover:bg-cyan-50 hover:text-cyan-600',
              'flex items-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 whitespace-nowrap min-w-fit',
            ]"
          >
            <i class="fa fa-database text-lg"></i>
            <span>{{ t('application.system_info') }}</span>
          </button>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="py-6">
        <!-- Personal Information Tab -->
        <div v-can="'application.view_personal_information'" v-show="activeTab === 'personal'" class="animate-fadeIn">
          <PersonalInformationView :item="store.item" />
        </div>

        <!-- Education Tab -->
        <div v-can="'application.view_education'" v-show="activeTab === 'education'" class="animate-fadeIn">
          <EducationExperienceView :item="store.item" />
        </div>

        <!-- Contact Tab -->
        <div v-can="'application.view_contact'" v-show="activeTab === 'contact'" class="animate-fadeIn">
          <ContactInformationView :item="store.item" />
        </div>

        <!-- NID Tab -->
        <div v-can="'application.view_nid_details'" v-show="activeTab === 'nid'" class="animate-fadeIn">
          <NidDetailsView :item="store.item" />
        </div>

        <!-- Passport Tab -->
        <div v-can="'application.view_passport'" v-show="activeTab === 'passport'" class="animate-fadeIn">
          <PassportDetailsView :item="store.item" />
        </div>

        <!-- Resume Tab -->
        <div v-can="'application.view_resume'" v-show="activeTab === 'resume'" class="animate-fadeIn">
          <ResumeDetailsView :item="store.item" />
        </div>

        <!-- Application Tab -->
        <div v-can="'application.view_application'" v-show="activeTab === 'application'" class="animate-fadeIn">
          <ApplicationDetailsView :item="store.item" />
        </div>

        <!-- Others Tab -->
        <div v-can="'application.view_documents'" v-show="activeTab === 'others'" class="animate-fadeIn">
          <OtherDetailsSection :item="store.item" />
        </div>

        <!-- System Information Tab -->
        <div v-can="'application.view_system_info'" v-show="activeTab === 'system'" class="animate-fadeIn">
          <SystemInformationView :item="store.item" />
        </div>
      </div>

   
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useApplicationStore } from '@/modules/application/store/applicationStore'

// Import components
import PersonalInformationView from './viewParts/PersonalInformationView.vue'
import EducationExperienceView from './viewParts/EducationExperienceView.vue'
import ContactInformationView from './viewParts/ContactInformationView.vue'
import NidDetailsView from './viewParts/NidDetailsView.vue'
import PassportDetailsView from './viewParts/PassportDetailsView.vue'
import ResumeDetailsView from './viewParts/ResumeDetailsView.vue'
import ApplicationDetailsView from './viewParts/ApplicationDetailsView.vue'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import OtherDetailsSection from './viewParts/OtherDetailsSection.vue'
import SystemInformationView from './viewParts/SystemInformationView.vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useApplicationStore()
const activeTab = ref('personal')


const columnTemp = computed(() => {
  return [
   // { key: 'bill_no', label: 'Bill No' },
  { key: 'transaction_id', label: t('application.transaction_id') },
  { key: 'applied_job', label: t('job.job_name') },
  { key: 'payer', label: t('shared.labels.payer') },
  { key: 'payer_name', label: t('application.payer_name') },
  { key: 'total_amount', label: t('application.total_amount') },
  { key: 'total_paid_amount', label: t('application.paid_total') },
  // { key: 'paid_amount', label: t('application.paid_now') },
  { key: 'discount_amount', label: t('application.discount') },
  { key: 'due_amount', label: t('application.due_amount') },
  { key: 'payment_method', label: t('application.payment_method') },
  { key: 'status', label: t('application.payment_status') },
  { key: 'payment_date_time_formatted', label: t('application.payment_date') },
  { key: 'payer_mobile', label: t('shared.labels.phone') },
  // { key: 'payer_email', label: t('application.email') },
  { key: 'payer_application_id', label: t('application.module') + ' ' + t('shared.labels.id') },
  ]
})

const { columns } = useCrudTable(store, columnTemp)
</script>

<style scoped>
/* Scrollbar hide for tab navigation */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

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

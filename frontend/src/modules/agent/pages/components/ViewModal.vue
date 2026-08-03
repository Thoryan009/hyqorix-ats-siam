<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('agent.view')"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
  >
    <ViewModalLayout>
      <!-- Agent Details Section -->
      <div
        v-can="'agent.view_summary'"
        class="bg-linear-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-lg border border-indigo-200"
      >
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
          <div
            class="bg-linear-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3 shadow-md"
          >
            <i class="fa fa-building text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">{{ t('agent.information') }}</h3>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- ID -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-hashtag text-indigo-500"></i>
              <span class="text-sm font-medium">{{t('shared.labels.id')}}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.id }}</p>
          </div>

          <!-- Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.name') }}</span>
            </div>
            <a
              :href="`mailto:${store.item.email}`"
              class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors break-all"
            >
              {{ store.item.name }}
            </a>
          </div>
          <!-- agent image -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-2">
              <i class="fa fa-image text-blue-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.agent') }} {{ t('shared.labels.image') }}</span>
            </div>

            <!-- <pre>{{ store.item }}</pre> -->
            <div class="flex flex-wrap gap-3">
              <a
                v-if="store.item.agent_image_url"
                :href="store.item.agent_image_url"
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

          <!-- Email -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-envelope text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.email') }}</span>
            </div>
            <a
              :href="`mailto:${store.item.email}`"
              class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors break-all"
            >
              {{ store.item.email }}
            </a>
          </div>

          <!-- Manager Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.manager_name') }}</span>
            </div>
            <a

              class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors"
            >
              {{ store.item.manager_name }}
            </a>
          </div>

          <!-- Agent ID -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user-circle text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.agent') }} {{ t('shared.labels.id') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.agent_id }}</p>
          </div>

          <!-- NID No -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-id-card text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.nid_no') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.nid_no }}</p>
          </div>

          <!-- Address -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-map-marker text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.address') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.address }}</p>
          </div>

          <!-- Phone -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-phone text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.phone') }}</span>
            </div>
            <a

              class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors"
            >
              {{ store.item.phone }}
            </a>
          </div>

           <!-- WhatsApp -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-whatsapp text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.whatsapp') }}</span>
            </div>
            <a

              class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors"
            >
              {{ store.item.whatsapp_no }}
            </a>
          </div>

           <!-- Staff Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.staff_name') }}</span>
            </div>
            <a

              class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors"
            >
              {{ store.item.staff_name }}
            </a>
          </div>

           <!-- Staff Phone -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-phone text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.staff_phone') }}</span>
            </div>
            <a

              class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors"
            >
              {{ store.item.stuff_phone }}
            </a>
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
      <!-- Applications -->
      <div class="pb-5!">
        <h2 class="text-lg font-semibold py-5">{{ store.item.name }} {{ t('shared.labels.applications') }}</h2>
        <BaseTable
          v-can="'agent.view_applications'"
          v-if="!isLoading"
          :columns="columns"
          :rows="store.item.applications"
        />
      </div>
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { useAgentStore } from '@/modules/agent/store/agentStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTranslate } from '@/shared/composables/useTranslate'
import { computed } from 'vue'

const { t } = useTranslate('agent')
const store = useAgentStore()

const columnTemp = computed (() => [
  { key: 'application_id', label: t('shared.labels.application_id') },
  { key: 'full_name', label: t('shared.labels.candidate_name') },
  { key: 'job', label: t('shared.labels.job') },
  { key: 'work_order_id', label: t('shared.labels.demand_letter_id') },
  { key: 'mobile', label: t('shared.labels.phone') },
  { key: 'passport_no', label: t('shared.labels.passport_no') },
  { key: 'application_price', label: t('shared.labels.application_price') },
  { key: 'status', label: t('shared.labels.status') },
])
const { columns } = useCrudTable(
  store,
  columnTemp,
  { timestamps: false, trackUser: false },
)
</script>

<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('shared.actions.view') + ' ' + t('country.module') + ' ' + t('shared.titles.details')"

    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
  >
    <ViewModalLayout>
      <!-- Country Details Section -->
      <div
      v-can="'country.view_summary'"
        class="bg-linear-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-lg border border-indigo-200"
      >
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
          <div
            class="bg-linear-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3 shadow-md"
          >
            <i class="fa fa-globe text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">{{ t('country.country_information') }}</h3>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- ID -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-hashtag text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.id') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.id }}</p>
          </div>

          <!-- Country Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-globe text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('country.country_name') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.name }}</p>
          </div>

          <!-- Total Clients -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-users text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('country.total_clients') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">
              {{ store.item.clients ? store.item.clients.length : 0 }}
            </p>
          </div>

          <!-- Created At -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-calendar-plus-o text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.created_at') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.created_at }}</p>
          </div>

          <!-- Created By -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.created_by') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.created_by }}</p>
          </div>

          <!-- Updated At -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-calendar-check-o text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.updated_at') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.updated_at }}</p>
          </div>

          <!-- Updated By -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user-circle text-indigo-500"></i>
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
import { useCountryStore } from '@/modules/country/store/countryStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTranslate } from '@/shared/composables/useTranslate'
import { computed } from 'vue'

const { t } = useTranslate('country')
const store = useCountryStore()

const columnsTemp = computed (() => [
  { key: 'name', label: t('shared.labels.name') },
  { key: 'email', label: t('shared.labels.email') },
  { key: 'phone', label: t('shared.labels.phone') },
  { key: 'client_id', label: t('shared.labels.client_id') },
  { key: 'country', label: t('shared.labels.country') },
])
const { columns } = useCrudTable(store, columnsTemp)
</script>

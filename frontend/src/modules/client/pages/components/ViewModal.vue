<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('client.view')"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
  >
    <ViewModalLayout>
      <!-- Client Details Section -->
      <div v-can="'client.view_summary'" class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-lg border border-indigo-200">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
          <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-3 shadow-md">
            <i class="fa fa-building text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-indigo-800">{{ t('client.information') }}</h3>
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

          <!-- Name -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-user-circle text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.name') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.name }}</p>
          </div>


             <!-- client image -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-2">
              <i class="fa fa-image text-blue-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.client') }} {{ t('shared.labels.image') }}</span>
            </div>

            <!-- <pre>{{ store.item }}</pre> -->
            <div class="flex flex-wrap gap-3">
              <a
                v-if="store.item.client_image_url"
                :href="store.item.client_image_url"
                target="_blank"
                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm hover:shadow-md"
              >
                <i class="fa fa-external-link"></i>
                Preview URL
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
            <a :href="`mailto:${store.item.email}`" class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors break-all">
              {{ store.item.email }}
            </a>
          </div>

          <!-- Phone -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-phone text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.phone') }}</span>
            </div>
            <a :href="`tel:${store.item.phone}`" class="text-gray-800 font-semibold hover:text-indigo-600 transition-colors">
              {{ store.item.phone }}
            </a>
          </div>


          <!-- WhatsApp -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-whatsapp text-green-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.whatsapp') }}</span>
            </div>
            <a :href="`https://wa.me/${store.item.whatsapp_no}`" class="text-gray-800 font-semibold hover:text-green-600 transition-colors">
              {{ store.item.whatsapp_no }}
            </a>
          </div>

          <!-- Client ID -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-id-badge text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.client') }} {{ t('shared.labels.id') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.client_id }}</p>
          </div>

          <!-- Country -->
          <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-gray-600 mb-1">
              <i class="fa fa-flag text-indigo-500"></i>
              <span class="text-sm font-medium">{{ t('shared.labels.country') }}</span>
            </div>
            <p class="text-gray-800 font-semibold">{{ store.item.country }}</p>
          </div>

          <!-- send notification -->
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

    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { useClientStore } from '@/modules/client/store/clientStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTranslate } from '@/shared/composables/useTranslate'
import { computed } from 'vue'

const { t } = useTranslate('client')

const store = useClientStore()

const columnTemp = computed(() => [
  { key: 'work_order_id', label: t('shared.labels.demand_letters') },
  { key: 'candidates', label: t('shared.labels.candidates') },
  { key: 'end_date_formatted', label: t('shared.labels.end_date') },
  { key: 'client', label: t('shared.labels.client') },
  { key: 'price', label: t('client.price_per_candidate') },
])
const { columns } = useCrudTable(store, columnTemp)
</script>

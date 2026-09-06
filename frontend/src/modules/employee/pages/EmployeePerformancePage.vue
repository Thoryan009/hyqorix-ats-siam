<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ t('employees.performance') }}</PageTitle>
      </div>
    </PageHeader>
<!-- <pre>{{ employeeData }}</pre> -->
    <!-- Bulk Delete & All Kinds of Filters -->
 <router-link to="/employees" class="no-underline">
        <div class="px-4 py-2 my-5 rounded-xl border cursor-pointer transition-all bg-white text-gray-700 w-fit shadow-sm hover:shadow-md hover:-translate-y-0.5">
          {{ t('employees.management') }}
        </div>
      </router-link>
    <div class="mb-4 underline flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">

      <!-- <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'country.delete'"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">Deleting...</span>
          <span v-else>Delete Selected ({{ selectedIds.length }})</span>
        </BaseButton>
      </div> -->
      <div></div>
      <!-- FILTERS -->
      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      />
    </div>
    <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <BaseTableSkeleton v-if="isLoading" :columns="columns" selectable show-actions />

        <BaseTable
          v-else
          :columns="columns"
          :rows="rows"
          :current-page="page"
          :per-page="perPage"
          @toggleAll="(checked) => toggleAll(rows, checked)"
          @toggleRow="toggleRow"
        >
          <template #cell-image_url="{ row }">
            <img
              v-if="row.image_url"
              :src="row.image_url"
              class="w-9 h-9 rounded object-cover border border-gray-200"
              alt="employee image"
            />
            <div v-else class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
              <i class="fa fa-user text-gray-400 text-sm"></i>
            </div>
          </template>

        </BaseTable>

        <!-- Pagination, Per Page & Showing Records -->
        <BasePagination
          v-if="!isLoading"
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @update:page="setPage"
          @update:perPage="setPerPage"
        />


      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { useEmployeeStore } from '../stores/employeeStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { computed } from 'vue'
import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useEmployeeStore()

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})


import { ref, onMounted } from 'vue'

const employeeData = ref(null)


const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const data = async (page = 1, perPage = 10, filters = {}) => {
  const api = useApi()

  const url = buildUrl('/employees-all-data-with-points', {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)

  return response(api)
}

onMounted(async () => {
  employeeData.value = await data()
})


pagination.bindMeta(employeeData)

const { columns} = useCrudTable(store, [
  { key: 'image_url', label: t('shared.labels.image') },
  { key: 'name', label: t('employees.employee_name') },
  { key: 'points', label: t('employees.points') },
])

const rows = computed(() => employeeData.value?.data?.data ?? [])



</script>

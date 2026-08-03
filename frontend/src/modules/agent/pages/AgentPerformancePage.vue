<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ t('agent.performance') }}</PageTitle>
      </div>
    </PageHeader>
    <!-- Bulk Delete & All Kinds of Filters -->
 <router-link to="/agents" class="no-underline">
        <div class="px-4 py-2 my-5 rounded-xl border cursor-pointer transition-all bg-white text-gray-700 w-fit shadow-sm hover:shadow-md hover:-translate-y-0.5">
          {{ t('agent.management') }}
        </div>
      </router-link>
    <div class="mb-4 underline flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">

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
          <template #cell-agent_image_url="{ row }">
            <img
              v-if="row.agent_image_url"
              :src="row.agent_image_url"
              class="w-9 h-9 rounded object-cover border border-gray-200"
              alt="agent image"
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
import { usePagination } from '@/shared/composables/usePagination'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { computed } from 'vue'
import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('agent')
const store = useAgentStore()

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})


import { ref, onMounted } from 'vue'
import { useAgentStore } from '../store/agentStore'

const agentData = ref(null)


const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const data = async (page = 1, perPage = 10, filters = {}) => {
  const api = useApi()

  const url = buildUrl('/agent-all-data-with-points', {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)

  return response(api)
}

onMounted(async () => {
  agentData.value = await data()
})


pagination.bindMeta(agentData)

const { columns} = useCrudTable(store, [
  { key: 'agent_image_url', label: t('shared.labels.image') },
  { key: 'name', label: t('agent.agent_name') },
  { key: 'points', label: t('shared.labels.points') },
])

const rows = computed(() => agentData.value?.data?.data ?? [])



</script>

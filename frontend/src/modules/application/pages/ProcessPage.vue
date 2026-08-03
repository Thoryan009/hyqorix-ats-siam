<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{t('process.title')}}</PageTitle>
      </div>
    </PageHeader>

    <!-- Bulk Delete & All Kinds Of Filters  -->
    <div class="flex justify-start md:justify-between items-center my-6 md:my-4">
      <div></div>
      <div></div>
      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
      </TableFilters>
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
          show-actions
        >
          <template #actions="{ row }">
            <BaseTableButton icon="fa fa-eye" variant="primary" title="View" @click="onView(row)" v-can="'process.view'" />
            <BaseTableButton
              v-can="'process.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
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

        <EditModal v-can="'process.edit'" />
        <ViewModal v-can="'process.view'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { usePagination } from '@/shared/composables/usePagination'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useProcessStore } from '../store/processStore'
import { useProcessesQuery } from '../queries/useProcessesQuery'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const ViewModal = defineAsyncComponent(() => import('./processParts/ViewModal.vue'))
const EditModal = defineAsyncComponent(() => import('./processParts/EditModal.vue'))
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'

const store = useProcessStore()
const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})


const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination
pagination.setPerPage(25)
const { data, isLoading } = useProcessesQuery(page, perPage, filters)

pagination.bindMeta(data)

const columnTemp = computed(() => [
  { key: 'name', label: t('process.name') },
  { key: 'validity', label: t('process.validity') },
  { key: 'notify_before', label: t('process.notify_before') },
  { key: 'duration', label: t('process.duration') },
])
const { columns, onView, onEdit } = useCrudTable(store, columnTemp)

const rows = computed(() => data.value?.data?.data ?? [])
</script>

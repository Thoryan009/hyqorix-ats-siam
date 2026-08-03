<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('embassyList.title') }}</PageTitle>
      </div>

      <div>
        <router-link v-can="'embassy_list.create'" :to="{ name: 'Add Embassy List' }">
          <BaseButton class="bg-slate-800 text-white hover:bg-slate-900">{{ t('embassyList.add') }}</BaseButton>
        </router-link>
      </div>
    </PageHeader>

    <div class="rounded-lg bg-white shadow-sm mt-4">
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-b border-gray-100"
      >
        <div class="flex items-center gap-2 text-sm text-gray-700">
          <span>{{ t('passport.show') }}</span>
          <select
            class="border border-gray-300 rounded-md px-2 py-1 text-sm"
            :value="perPage"
            @change="setPerPage(Number($event.target.value))"
          >
            <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">{{ size }}</option>
          </select>
          <span>{{ t('passport.entries') }}</span>
        </div>

        <div class="flex items-center gap-2">
          <label for="embassy-search" class="text-sm text-gray-700">{{ t('shared.actions.search') }}:</label>
          <input
            id="embassy-search"
            v-model="filters.searchQuery"
            type="text"
            class="border border-gray-300 rounded-md px-3 py-1.5 text-sm w-56"
            placeholder="Search list"
          />
        </div>
      </div>

      <BaseTableSkeleton v-if="isLoading" :columns="columns" show-actions />

      <BaseTable
        v-else
        :columns="columns"
        :rows="rows"
        :current-page="page"
        :per-page="perPage"
        show-actions
      >
        <template #actions="{ row }">
          <BaseTableButton
            icon="fa fa-print"
            variant="primary"
            title="Print"
            @click="openPrint(row)"
          />
          <router-link v-can="'embassy_list.edit'" :to="{ name: 'Edit Embassy List', params: { id: row.id } }">
            <BaseTableButton icon="fa fa-pencil" variant="success" title="Edit" />
          </router-link>
          <BaseTableButton
            v-can="'embassy_list.delete'"
            icon="fa fa-trash"
            variant="danger"
            title="Delete"
            @click="confirmDelete(row.id)"
          />
        </template>
      </BaseTable>

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
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import { useEmbassyListsQuery } from '../queries/useEmbassyListsQuery'
import { useEmbassyListMutations } from '../queries/useEmbassyListMutations'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const { filters } = useTableFilters({
  searchQuery: '',
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useEmbassyListsQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove } = useEmbassyListMutations('Embassy List')
const { confirmDelete } = useDeleteWithConfirm(remove)
const router = useRouter()

const openPrint = (row) => {
  const routeData = router.resolve({
    name: 'Embassy List Print',
    params: { id: row.id },
  })

  window.open(routeData.href, '_blank')
}

// const columns = [
//   { key: 'sl', label: '#' },
//   { key: 'submit_date', label: 'Submit Date' },
//   { key: 'new_stamping', label: 'No. of New Stamping' },
//   { key: 'cancel_stamping', label: 'No. of Cancel Stamping' },
//   { key: 'restamping', label: 'No. of Re-stamping' },
//   { key: 'created_at', label: 'Created at' },
//   { key: 'last_update', label: 'Last Update' },
// ]

const columns = computed(() => [
  { key: 'sl', label: t('shared.labels.sl') },
  { key: 'submit_date', label: t('embassyList.submit_date') },
  { key: 'new_stamping', label: t('embassyList.new_stamping') },
  { key: 'cancel_stamping', label: t('embassyList.cancellation') },
  { key: 'restamping', label: t('embassyList.re_stamping') },
  { key: 'created_at', label: t('shared.labels.created_at') },
  { key: 'last_update', label: t('embassyList.last_update') },
])

const rows = computed(() => {
  const start = (page.value - 1) * perPage.value
  const items = data.value?.data?.data ?? []

  return items.map((row, index) => ({
    ...row,
    sl: start + index + 1,
  }))
})
</script>

<template>
  <SectionHeader>
   <PageHeader>
  <div>
    <PageTitle>{{ t('passport.module') }}</PageTitle>
    <p class="mt-1 text-sm text-gray-500">
      {{ t('passport.manage_records') }}
    </p>
  </div>

  <BaseButton
    v-can="'passport_handover.create'"
    class="bg-primary text-white hover:opacity-90"
    @click="openCreateModal"
  >
    <i class="fa fa-plus mr-1"></i>
    {{ t('passport.create') }}
  </BaseButton>
</PageHeader>

<div class="rounded-lg bg-white shadow-sm">
  <div
    class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <div class="flex items-center gap-2 text-sm text-gray-700">
      <span>{{ t('passport.show') }}</span>
      <select
        class="rounded-md border border-gray-300 px-2 py-1 text-sm"
        :value="perPage"
        @change="setPerPage(Number($event.target.value))"
      >
        <option
          v-for="size in [10, 25, 50, 100]"
          :key="size"
          :value="size"
        >
          {{ size }}
        </option>
      </select>
      <span>{{ t('passport.entries') }}</span>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <select
        v-model="filters.status"
        class="rounded-md border border-gray-300 px-3 py-1.5 text-sm"
      >
        <option value="">All Status</option>
        <option value="handed_over">Handed Over</option>
            <option value="partially_collected">Partially Collected</option>
            <option value="collected">Collected</option>
            <option value="rejected">Rejected</option>
      </select>

      <PassportHandoverPassportSearch
        @select="goToHandoverDetails"
      />

      <input
        v-model="filters.searchQuery"
        type="text"
        class="w-56 rounded-md border border-gray-300 px-3 py-1.5 text-sm"
        :placeholder="t('passport.search_handover_or_taker')"
      />
    </div>
  </div>

  <BaseTableSkeleton
    v-if="isLoading"
    :columns="columns"
    show-actions
  />

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
        v-can="'passport_handover.view'"
        icon="fa fa-eye"
        variant="info"
        :title="t('passport.view_details')"
        @click="$router.push(`/passport-handover/${row.id}`)"
      />

      <BaseTableButton
        v-if="row.pending_items_count > 0 && !row.is_permanent"
        v-can="'passport_handover.collect'"
        icon="fa fa-check-circle"
        variant="success"
        :title="t('passport.collect_reject')"
        @click="openCollectModal(row.id)"
      />

      <BaseTableButton
        v-can="'passport_handover.delete'"
        icon="fa fa-trash"
        variant="danger"
        :title="t('passport.delete')"
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

    <PassportHandoverCreateModal
      :is-visible="isCreateModalOpen"
      @close="closeCreateModal"
      @created="handleCreated"
    />

    <PassportHandoverCollectModal
      :is-visible="isCollectModalOpen"
      :handover-id="selectedHandoverId"
      @close="closeCollectModal"
      @collected="handleCollected"
    />
  </SectionHeader>
</template>

<script setup>
import { computed, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseTable from '@/shared/components/base/BaseTable.vue'
import BaseTableSkeleton from '@/shared/components/base/BaseTableSkeleton.vue'
import BaseTableButton from '@/shared/components/base/BaseTableButton.vue'
import BasePagination from '@/shared/components/base/BasePagination.vue'
import PassportHandoverCreateModal from './components/PassportHandoverCreateModal.vue'
import PassportHandoverCollectModal from './components/PassportHandoverCollectModal.vue'
import PassportHandoverPassportSearch from './components/PassportHandoverPassportSearch.vue'
import { usePassportHandoversQuery } from '../queries/usePassportHandoversQuery'
import { usePassportHandoverMutations } from '../queries/usePassportHandoverMutations'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useRouter } from 'vue-router'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate('passport')

const { filters } = useTableFilters({
  searchQuery: '',
  status: '',
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading, refetch } = usePassportHandoversQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove } = usePassportHandoverMutations('Passport Handover')
const { confirmDelete } = useDeleteWithConfirm(remove)

const router = useRouter()

const isCreateModalOpen = ref(false)
const isCollectModalOpen = ref(false)
const selectedHandoverId = ref(null)

const columns = computed (() => {
  return [
 { key: 'sl', label: 'SL' },
  { key: 'handover_no', label: t('passport.handover_no') },
  { key: 'type_label', label: t('passport.type') },
  { key: 'taker_name', label: t('passport.taker_name') },
  { key: 'taker_phone', label: t('passport.taker_phone') },
  { key: 'taken_at', label: t('passport.taken_at') },
  { key: 'items_summary', label: t('passport.passports') },
  { key: 'status_label', label: t('passport.status') },
  { key: 'created_at', label: t('passport.created_at') },

  ]
})


const rows = computed(() => {
  const start = (page.value - 1) * perPage.value
  const items = data.value?.data?.data ?? []

  return items.map((row, index) => ({
    ...row,
    sl: start + index + 1,
    type_label: row.type_label || (row.is_permanent
      ? `Permanent ${row.type === 'group' ? 'Group' : 'Single'}`
      : row.type === 'group' ? 'Group' : 'Single'),
    items_summary: row.is_permanent
      ? `${row.total_items_count || 0} permanent`
      : `${row.collected_items_count || 0} collected · ${row.rejected_items_count || 0} rejected · ${row.pending_items_count || 0} pending`,
  }))
})

function openCreateModal() {
  isCreateModalOpen.value = true
}

function closeCreateModal() {
  isCreateModalOpen.value = false
}

function handleCreated() {
  closeCreateModal()
  refetch()
}

function openCollectModal(id) {
  selectedHandoverId.value = id
  isCollectModalOpen.value = true
}

function closeCollectModal() {
  isCollectModalOpen.value = false
  selectedHandoverId.value = null
}

function handleCollected() {
  closeCollectModal()
  refetch()
}

function goToHandoverDetails({ handoverId, passportNo }) {
  if (!handoverId) return

  router.push(
    `/passport-handover/${handoverId}?passport_no=${encodeURIComponent(passportNo || '')}`
  )
}
</script>

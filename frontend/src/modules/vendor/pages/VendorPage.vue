<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('vendor.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'vendor.create'" @click="headerAction.onClick()">
        {{ headerAction.label }}
      </BaseButton>
    </PageHeader>

    <div class="mb-6 flex flex-wrap gap-2">
      <button
        v-for="tab in pageTabs"
        :key="tab.id"
        type="button"
        class="rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
        :class="
          activeTab === tab.id
            ? 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
            : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:bg-primary-light!'
        "
        @click="activeTab = tab.id"
      >
        <i :class="[tab.icon, 'mr-1.5']"></i>{{ tab.label }}
      </button>
    </div>

    <div v-if="activeTab === 'vendors'">
      <div class="flex justify-start md:justify-between items-center my-4">
        <div>
          <BaseButton
            v-can="'vendor.delete'"
            v-if="selectedIds.length"
            class="bg-red-600 text-white hover:bg-red-700"
            @click="bulkDelete"
            :disabled="removeItemsLoading"
          >
            <span v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
            <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
          </BaseButton>
        </div>
        <div></div>
        <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters" />
      </div>

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
            selectable
            :selected-ids="selectedIds"
            @toggleAll="(checked) => toggleAll(rows, checked)"
            @toggleRow="toggleRow"
          >
            <template #cell-vendor_image_url="{ row }">
              <img
                v-if="row.vendor_image_url"
                :src="row.vendor_image_url"
                class="w-9 h-9 rounded-full object-cover border border-gray-200"
                alt="vendor logo"
              />
              <div v-else class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                <i class="fa fa-building text-gray-400 text-sm"></i>
              </div>
            </template>
            <template #actions="{ row }">
              <BaseTableButton
                icon="fa fa-eye"
                variant="primary"
                title="View"
                @click="onView(row)"
                v-can="'vendor.view'"
              />
              <BaseTableButton
                v-can="'vendor.edit'"
                icon="fa fa-pencil"
                variant="success"
                title="Edit"
                @click="onEdit(row)"
              />
              <BaseTableButton
                v-can="'vendor.delete'"
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

          <AddModal v-can="'vendor.create'" :vendorData="vendorData" />
          <EditModal v-can="'vendor.edit'" :vendorData="vendorData" />
          <ViewModal v-can="'vendor.view'" />
          <DeleteModal v-can="'vendor.delete'" />
        </div>
      </div>
    </div>

    <div v-else class="rounded-lg bg-white shadow-sm p-4">
      <VendorTypePanel />
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent, ref } from 'vue'
import { useVendorDataQuery, useVendorsQuery } from '../queries/useVendorsQuery'
import { useVendorStore } from '../store/vendorStore'
import { useVendorTypeStore } from '../store/vendorTypeStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useVendorMutations } from '../queries/useVendorMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('vendor')

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))
const VendorTypePanel = defineAsyncComponent(
  () => import('./components/vendorType/VendorTypePanel.vue')
)

const store = useVendorStore()
const typeStore = useVendorTypeStore()
const activeTab = ref('vendors')

const pageTabs = computed(() => [
  { id: 'vendors', label: t('vendor.module'), icon: 'fa fa-building' },
  { id: 'types', label: t('vendor.types'), icon: 'fa fa-tags' },
])

const headerAction = computed(() => {
  if (activeTab.value === 'types') {
    return {
      label: t('vendor.add_type'),
      onClick: () => typeStore.handleToggleModal('add'),
    }
  }

  return {
    label: t('vendor.add'),
    onClick: () => store.handleToggleModal('add'),
  }
})

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useVendorsQuery(page, perPage, filters)
const { data: vendorData } = useVendorDataQuery()

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useVendorMutations(store.moduleName)
const { confirmDelete } = useDeleteWithConfirm(remove)
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const rows = computed(() => data.value?.data?.data ?? [])

const columnTemp = computed(() => [
  { key: 'vendor_image_url', label: t('shared.labels.image') },
  { key: 'organization_name', label: t('vendor.organization_name') },
  { key: 'vendor_type_formatted', label: t('vendor.vendor_type') },
  { key: 'contact_person', label: t('vendor.contact_person') },
  { key: 'email', label: t('shared.labels.email') },
  { key: 'phone', label: t('shared.labels.phone') },
  { key: 'vendor_id', label: 'Vendor ID' },
  { key: 'address', label: t('shared.labels.address') },
  { key: 'status_formatted', label: t('shared.labels.status') },
])

const { columns, onView, onEdit } = useCrudTable(store, columnTemp, {
  timestamps: false,
  trackUser: false,
})
</script>

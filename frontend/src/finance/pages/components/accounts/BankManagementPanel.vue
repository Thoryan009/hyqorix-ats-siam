<template>
  <div class="rounded-lg bg-white shadow-sm">
    <div class="flex flex-col items-start justify-between gap-4 border-b border-gray-100 p-4 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-base font-semibold text-gray-900">Bank Management</h3>
        <p class="mt-1 text-sm text-gray-500">Manage bank master records used for bank accounts</p>
      </div>

      <BaseButton v-can="'finance_bank.create'" @click="store.handleToggleModal('add')">
        <i class="fa fa-plus mr-1"></i> Add Bank
      </BaseButton>
    </div>

    <div class="p-4">
      <div class="mb-4">
        <BaseButton
          v-if="selectedIds.length"
          v-can="'finance_bank.delete'"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
        >
          Delete Selected ({{ selectedIds.length }})
        </BaseButton>
      </div>

      <BaseTable
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
        <template #cell-status="{ row }">
          <span
            class="rounded-full px-3 py-1 text-xs font-semibold"
            :class="
              row.status === 'Active'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700'
            "
          >
            {{ row.status }}
          </span>
        </template>

        <template #actions="{ row }">
          <BaseTableButton
            v-can="'finance_bank.edit'"
            icon="fa fa-pencil"
            variant="success"
            title="Edit"
            @click="onEdit(row)"
          />

          <BaseTableButton
            v-can="'finance_bank.delete'"
            icon="fa fa-trash"
            variant="danger"
            title="Delete"
            @click="confirmDelete(row.id)"
          />
        </template>
      </BaseTable>

      <BasePagination
        :total="total"
        :showing="showing"
        :links="links"
        :per-page="perPage"
        @update:page="setPage"
        @update:perPage="setPerPage"
      />
    </div>

    <BankAddModal v-can="'finance_bank.create'" />
    <BankEditModal v-can="'finance_bank.edit'" />
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, onMounted, ref, watch } from 'vue'
import { useBankStore } from '@/finance/store/bankStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { toast } from '@/shared/config/toastConfig'

const BankAddModal = defineAsyncComponent(() => import('../AddModal.vue'))
const BankEditModal = defineAsyncComponent(() => import('../EditModal.vue'))

const store = useBankStore()

const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const showing = ref(0)
const links = ref([])

const { columns, onEdit } = useCrudTable(store, [
  { key: 'bank_name', label: 'Bank Name' },
  { key: 'swift_code', label: 'SWIFT Code' },
  { key: 'address', label: 'Address' },
  { key: 'branch_name', label: 'Branch Name' },
  { key: 'status', label: 'Status' },
])

const rows = computed(() => store.getPaginatedBanks(page.value, perPage.value))

const updatePagination = () => {
  const meta = store.getPaginationMeta(page.value, perPage.value)
  total.value = meta.total
  showing.value = meta.to
  links.value = meta.links

  if (page.value > meta.lastPage) {
    page.value = meta.lastPage
  }
}

watch([() => store.banks.length, page, perPage], updatePagination, { immediate: true })

const setPage = (value) => {
  if (value && value !== page.value) {
    page.value = value
  }
}

const setPerPage = (value) => {
  perPage.value = Number(value)
  page.value = 1
}

const removeMutation = {
  mutateAsync: async (id) => {
    const result = await store.deleteBank(id)
    if (!result.ok) {
      toast.error(result.message)
      throw new Error(result.message)
    }
    toast.success(`${store.moduleName} operation successful`)
  },
}

const removeItemsMutation = {
  mutate: async (ids, { onSuccess, onError } = {}) => {
    try {
      const result = await store.deleteBanks(ids)
      if (!result.ok) {
        toast.error(result.message)
        throw new Error(result.message)
      }
      toast.success(`${store.moduleName} operation successful`)
      onSuccess?.()
    } catch (error) {
      onError?.(error)
    }
  },
}

const { confirmDelete } = useDeleteWithConfirm(removeMutation)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItemsMutation, {
  confirmText: 'Are you sure you want to delete selected banks?',
})

onMounted(async () => {
  await store.fetchBanks()
})
</script>

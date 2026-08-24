<template>
  <BaseModal
    :isVisible="store.isBulkModal"
    :title="t('parties.bulk_add_title')"
    className="w-[98vw] max-w-[1680px] max-h-[92vh]"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="max-h-[calc(92vh-8rem)] overflow-y-auto pr-1">
      <p class="mb-4 text-sm text-slate-500">{{ t('parties.bulk_add_hint') }}</p>

      <div class="mb-4 max-w-xs space-y-2">
        <BaseLabel for="bulk_party_type">{{ t('parties.party_type') }}</BaseLabel>
        <BaseSelect
          id="bulk_party_type"
          v-model="partyType"
          :options="partyTypeOptions"
          placeholder="Select type"
          :required="true"
        />
      </div>

      <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm font-medium text-slate-700">
          {{ t('parties.total_rows') }}: {{ rows.length }}
        </p>
        <BaseButton
          type="button"
          className="bg-slate-800 text-white hover:bg-slate-900"
          @click="addRow"
        >
          + {{ t('parties.add_row') }}
        </BaseButton>
      </div>

      <div class="overflow-x-auto rounded-md border border-slate-200 pb-4">
        <table class="min-w-[1200px] w-full border-collapse text-sm">
          <thead>
            <tr class="bg-slate-50 text-left text-slate-600">
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 font-semibold">#</th>
              <th
                v-if="showSourceSelect"
                class="min-w-[220px] border-b border-slate-200 px-3 py-2.5 font-semibold"
              >
                {{ sourceSelectLabel }}
              </th>
              <th class="min-w-[120px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('parties.party_id') }}
              </th>
              <th class="min-w-[180px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('parties.name') }}
              </th>
              <th class="min-w-[110px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('parties.opening_debit') }}
              </th>
              <th class="min-w-[110px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('parties.opening_credit') }}
              </th>
              <th class="min-w-[120px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('shared.labels.status') }}
              </th>
              <th class="min-w-[140px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('parties.remarks') }}
              </th>
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('parties.action') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in rows" :key="row.id" class="align-middle">
              <td class="border-b border-slate-100 px-3 py-2 text-slate-700">{{ index + 1 }}</td>
              <td v-if="showSourceSelect" class="relative overflow-visible border-b border-slate-100 px-3 py-2 align-top">
                <BaseSearchSelect
                  v-model="row.source_id"
                  :options="getSourceOptionsForRow(row)"
                  :placeholder="sourcePlaceholder"
                  :disabled="isSourceLoading"
                  :filter-fn="filterByCodeOrName"
                  :required="true"
                  teleport-dropdown
                  list-class-name="max-h-96"
                  @update:modelValue="(value) => fillRowFromSource(row, value)"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  v-model="row.code"
                  placeholder="Eg: CL001"
                  :required="true"
                  className="w-full min-w-[110px] rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  v-model="row.name"
                  placeholder="Eg: Gulf Engineering Co."
                  :required="true"
                  className="w-full min-w-[160px] rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="border-b border-slate-100 px-2 py-2">
                <BaseInput
                  type="number"
                  step="0.01"
                  v-model="row.opening_debit"
                  placeholder="0.00"
                  className="w-full min-w-[100px] rounded-md border border-gray-300 px-2 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="border-b border-slate-100 px-2 py-2">
                <BaseInput
                  type="number"
                  step="0.01"
                  v-model="row.opening_credit"
                  placeholder="0.00"
                  className="w-full min-w-[100px] rounded-md border border-gray-300 px-2 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseSelect
                  v-model="row.status"
                  :options="statusOptions"
                  :required="true"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  v-model="row.remarks"
                  placeholder="Optional"
                  className="w-full min-w-[120px] rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="whitespace-nowrap border-b border-slate-100 px-3 py-2">
                <div class="flex flex-nowrap items-center gap-1">
                  <button
                    type="button"
                    class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50"
                    @click="copyRow(index)"
                  >
                    {{ t('parties.copy') }}
                  </button>
                  <button
                    type="button"
                    class="rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                    :disabled="rows.length <= 1"
                    @click="deleteRow(index)"
                  >
                    {{ t('parties.delete') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-if="validationMessage" class="mt-3 text-sm text-red-600">{{ validationMessage }}</p>

      <div class="mt-4 flex justify-end gap-2">
        <BaseButton
          type="button"
          class="bg-yellow-600 hover:bg-yellow-700"
          @click="closeModal"
        >
          {{ t('shared.actions.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="isSubmitting">
          <span v-if="isSubmitting">{{ t('shared.actions.save') }}...</span>
          <span v-else>{{ t('parties.save_all') }}</span>
        </BaseButton>
      </div>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import BaseSearchSelect from '@/shared/components/base/BaseSearchSelect.vue'
import { toast } from '@/shared/config/toastConfig'
import { usePartySourceOptionsQuery } from '@/modules/parties/queries/usePartySourceOptionsQuery'
import { usePartyStore } from '@/modules/parties/store/partyStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import {
  buildBulkPartyPayload,
  createDefaultPartyRows,
  createEmptyPartyRow,
  validateBulkPartyRows,
} from '../../data/partyFormHelpers'
import { getPartySourceConfig, hasPartySource } from '../../data/partySourceConfig'
import { partyTypeOptions, statusOptions } from '../../data/partyOptions'

const { t } = useTranslate()
const store = usePartyStore()

const partyType = ref('')
const rows = ref(createDefaultPartyRows())
const validationMessage = ref('')
const isSubmitting = ref(false)
let nextRowId = rows.value.length + 1

const partyTypeRef = computed(() => partyType.value)
const { data: sourceData, isLoading: isSourceLoading } = usePartySourceOptionsQuery(partyTypeRef)

const showSourceSelect = computed(() => hasPartySource(partyType.value))

const sourceSelectLabel = computed(() => {
  const config = getPartySourceConfig(partyType.value)
  return config ? t(config.selectLabelKey) : ''
})

const sourceOptions = computed(() => {
  const items = sourceData.value ?? []
  return items.map((item) => ({
    id: item.id,
    name: `${item.code} – ${item.name}`,
    code: item.code,
    labelName: item.name,
  }))
})

const sourcePlaceholder = computed(() =>
  isSourceLoading.value ? t('parties.loading_source') : t('parties.search_source'),
)

const filterByCodeOrName = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  const code = String(option?.code ?? '').toLowerCase()
  return label.includes(query) || code.includes(query)
}

const getSourceOptionsForRow = (row) => {
  const usedIds = new Set(
    rows.value
      .filter((item) => item.id !== row.id && item.source_id)
      .map((item) => String(item.source_id)),
  )

  return sourceOptions.value.filter((option) => !usedIds.has(String(option.id)))
}

const fillRowFromSource = (row, value) => {
  row.source_id = value ?? ''

  if (!value) return

  const selected = (sourceData.value ?? []).find((item) => String(item.id) === String(value))
  if (!selected) return

  row.code = selected.code ?? ''
  row.name = selected.name ?? ''
}

const resetForm = () => {
  partyType.value = ''
  rows.value = createDefaultPartyRows()
  nextRowId = rows.value.length + 1
  validationMessage.value = ''
}

const addRow = () => {
  rows.value.push(createEmptyPartyRow(nextRowId++))
}

const copyRow = (index) => {
  const source = rows.value[index]
  if (!source) return

  const copied = {
    ...source,
    id: nextRowId++,
    source_id: '',
  }
  rows.value.splice(index + 1, 0, copied)
}

const deleteRow = (index) => {
  if (rows.value.length <= 1) return
  rows.value.splice(index, 1)
}

const closeModal = () => {
  store.closeBulkModal()
  resetForm()
}

watch(
  () => store.isBulkModal,
  (isOpen) => {
    if (isOpen) resetForm()
  },
)

watch(partyType, (newType, oldType) => {
  if (!oldType || newType === oldType) return

  rows.value = createDefaultPartyRows()
  nextRowId = rows.value.length + 1
  validationMessage.value = ''
})

const handleSubmit = async () => {
  validationMessage.value = ''

  const errors = validateBulkPartyRows(partyType.value, rows.value, {
    requireSource: showSourceSelect.value,
  })

  if (errors.length) {
    validationMessage.value = errors[0]
    toast.error(t('parties.bulk_validation_failed'))
    return
  }

  isSubmitting.value = true

  try {
    const payload = buildBulkPartyPayload(partyType.value, rows.value)

    // Frontend-only for now. Backend bulk endpoint will consume this payload later.
    console.info('[parties:bulk-add] payload ready for backend', payload)

    toast.success(t('parties.bulk_ready', { count: payload.length }))
    closeModal()
  } finally {
    isSubmitting.value = false
  }
}
</script>

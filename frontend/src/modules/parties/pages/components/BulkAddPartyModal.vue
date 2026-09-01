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

      <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <BaseLabel for="bulk_party_type">{{ t('parties.party_type') }}</BaseLabel>
          <BaseSelect
            id="bulk_party_type"
            v-model="partyType"
            :options="partyTypeOptions"
            placeholder="Select type"
          />
        </div>

        <div v-if="showJobFilter" class="space-y-2">
          <BaseLabel for="bulk_job_id">{{ t('shared.labels.job') }}</BaseLabel>
          <BaseSearchSelect
            id="bulk_job_id"
            v-model="jobId"
            :options="jobOptions"
            :placeholder="jobPlaceholder"
            :disabled="isJobLoading"
            :filter-fn="filterJobByNameOrCode"
            teleport-dropdown
            list-class-name="max-h-96"
          />
        </div>
      </div>

      <div class="mb-3">
        <p class="text-sm font-medium text-slate-700">
          {{ t('parties.total_rows') }}: {{ rows.length }}
        </p>
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
                  :disabled="(isSourceLoading || isSourceFetching) && !sourceOptions.length"
                  :filter-fn="filterByCodeOrName"
                  teleport-dropdown
                  list-class-name="max-h-96"
                  @update:modelValue="(value) => fillRowFromSource(row, value)"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  v-model="row.code"
                  placeholder="Eg: CL001"
                  className="w-full min-w-[110px] rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  v-model="row.name"
                  placeholder="Eg: Gulf Engineering Co."
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
                <button
                  type="button"
                  class="rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                  :disabled="rows.length <= 1"
                  @click="deleteRow(index)"
                >
                  {{ t('parties.delete') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-3 flex justify-start">
        <BaseButton
          type="button"
          className="bg-slate-800 text-white hover:bg-slate-900"
          @click="addRow"
        >
          + {{ t('parties.add_row') }}
        </BaseButton>
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
import { usePartyMutations } from '@/modules/parties/queries/usePartyMutations'
import { usePartySourceOptionsQuery } from '@/modules/parties/queries/usePartySourceOptionsQuery'
import { usePartyJobOptionsQuery } from '@/modules/parties/queries/usePartyJobOptionsQuery'
import { usePartyStore } from '@/modules/parties/store/partyStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import {
  buildBulkPartyPayload,
  createDefaultPartyRows,
  createEmptyPartyRow,
  validateBulkPartyRows,
} from '../../data/partyFormHelpers'
import { getPartySourceConfig, hasPartySource, requiresPartyJobFilter } from '../../data/partySourceConfig'
import { statusOptions } from '../../data/partyOptions'
import { usePartyTypeOptionsQuery } from '../../queries/usePartyTypeOptionsQuery'

const { t } = useTranslate()
const store = usePartyStore()

const partyType = ref('')
const jobId = ref('')
const rows = ref(createDefaultPartyRows())
const validationMessage = ref('')
let nextRowId = rows.value.length + 1

const { data: partyTypeOptionsData } = usePartyTypeOptionsQuery('active')
const partyTypeOptions = computed(() =>
  (partyTypeOptionsData.value ?? []).map((item) => ({
    id: item.id ?? item.code,
    name: item.name,
  })),
)

const { bulkSubmit, bulkSubmitLoading: isSubmitting } = usePartyMutations(store.moduleName, {
  onSuccess() {
    closeModal()
  },
})

const partyTypeRef = computed(() => partyType.value)
const sourceFiltersRef = computed(() =>
  requiresPartyJobFilter(partyType.value) ? { job_list_id: jobId.value || undefined } : {},
)
const showJobFilter = computed(() => requiresPartyJobFilter(partyType.value))
const isBulkModalOpen = computed(() => store.isBulkModal)
const { data: jobOptionsData, isLoading: isJobLoading } = usePartyJobOptionsQuery(showJobFilter)
const { data: sourceData, isLoading: isSourceLoading, isFetching: isSourceFetching } =
  usePartySourceOptionsQuery(partyTypeRef, sourceFiltersRef, isBulkModalOpen)

const jobOptions = computed(() => jobOptionsData.value ?? [])

const jobPlaceholder = computed(() =>
  isJobLoading.value ? t('parties.loading_source') : t('parties.search_job'),
)

const showSourceSelect = computed(() => {
  if (!hasPartySource(partyType.value)) return false
  if (requiresPartyJobFilter(partyType.value)) return Boolean(jobId.value)
  return true
})

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

const sourcePlaceholder = computed(() => {
  if (requiresPartyJobFilter(partyType.value) && !jobId.value) {
    return t('parties.select_job_to_load_candidates')
  }
  if (isSourceLoading.value || isSourceFetching.value) {
    return t('parties.loading_source')
  }
  if (showSourceSelect.value && !(sourceOptions.value?.length)) {
    return t('parties.no_source_options')
  }
  return t('parties.search_source')
})

const filterJobByNameOrCode = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  return label.includes(query)
}

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
  jobId.value = ''
  rows.value = createDefaultPartyRows()
  nextRowId = rows.value.length + 1
  validationMessage.value = ''
}

const resetRowSelections = () => {
  rows.value = rows.value.map((row) => ({
    ...row,
    source_id: '',
    code: '',
    name: '',
  }))
}

const addRow = () => {
  rows.value.push(createEmptyPartyRow(nextRowId++))
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

  jobId.value = ''
  rows.value = createDefaultPartyRows()
  nextRowId = rows.value.length + 1
  validationMessage.value = ''
})

watch(jobId, () => {
  if (partyType.value !== 'Candidate') return
  resetRowSelections()
})

const handleSubmit = async () => {
  validationMessage.value = ''

  if (requiresPartyJobFilter(partyType.value) && !jobId.value) {
    validationMessage.value = t('parties.select_job_to_load_candidates')
    toast.error(validationMessage.value)
    return
  }

  const errors = validateBulkPartyRows(partyType.value, rows.value, {
    requireSource: showSourceSelect.value,
  })

  if (errors.length) {
    const firstError = errors[0]
    const message =
      typeof firstError === 'string'
        ? firstError
        : t(firstError.key, firstError.params || {})

    validationMessage.value = message
    toast.error(message)
    return
  }

  const payload = buildBulkPartyPayload(partyType.value, rows.value)

  try {
    await bulkSubmit.mutateAsync(payload)
  } catch (error) {
    const message =
      error?.response?.data?.message ||
      error?.message ||
      t('parties.bulk_validation_failed')
    validationMessage.value = message
    toast.error(message)
  }
}
</script>

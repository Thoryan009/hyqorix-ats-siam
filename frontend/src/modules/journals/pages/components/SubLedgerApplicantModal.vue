<template>
  <BaseModal
    :isVisible="isVisible"
    :title="t('journals.sub_ledger_modal_title')"
    className="max-w-[95vw] xl:max-w-3xl max-h-[90vh]"
    @close="handleClose"
  >
    <div class="space-y-4">
      <p class="text-sm text-slate-600">{{ t('journals.sub_ledger_modal_hint') }}</p>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <BaseInput
          v-model="searchQuery"
          :placeholder="t('journals.sub_ledger_search_passport')"
          className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
        />
        <div class="flex shrink-0 gap-2">
          <BaseButton
            type="button"
            className="border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
            @click="selectAllVisible"
          >
            {{ t('journals.sub_ledger_select_all') }}
          </BaseButton>
          <BaseButton
            type="button"
            className="border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
            @click="clearSelection"
          >
            {{ t('journals.sub_ledger_clear_all') }}
          </BaseButton>
        </div>
      </div>

      <p class="text-xs font-medium text-slate-500">
        {{ t('journals.sub_ledger_selected_count', { count: selectedPassports.length }) }}
      </p>

      <div
        v-if="isLoading"
        class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500"
      >
        {{ t('journals.sub_ledger_loading') }}
      </div>

      <div
        v-else-if="!filteredApplicants.length"
        class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500"
      >
        {{ t('journals.sub_ledger_empty') }}
      </div>

      <div
        v-else
        class="max-h-[50vh] overflow-y-auto rounded-lg border border-slate-200 divide-y divide-slate-100"
      >
        <label
          v-for="applicant in filteredApplicants"
          :key="applicant.id"
          class="flex cursor-pointer items-start gap-3 px-4 py-3 transition hover:bg-slate-50"
          :class="isSelected(applicant.passport_no) ? 'bg-blue-50/60' : 'bg-white'"
        >
          <input
            type="checkbox"
            class="mt-1 h-4 w-4 shrink-0"
            :checked="isSelected(applicant.passport_no)"
            @change="togglePassport(applicant.passport_no)"
          />
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-slate-900">{{ applicant.passport_no }}</p>
            <p class="text-sm text-slate-600">{{ applicant.full_name || '—' }}</p>
          </div>
        </label>
      </div>

      <div class="flex justify-end gap-2 border-t border-slate-200 pt-4">
        <BaseButton
          type="button"
          class="bg-yellow-600 hover:bg-yellow-700"
          @click="handleClose"
        >
          {{ t('shared.actions.cancel') }}
        </BaseButton>
        <BaseButton type="button" @click="handleApply">
          {{ t('journals.sub_ledger_apply') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useSubLedgerApplicantsQuery } from '../../queries/useSubLedgerApplicantsQuery'
import { formatSubLedgerPassports, parseSubLedgerPassports } from '../../data/subLedgerHelpers'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  jobListId: {
    type: [String, Number],
    default: '',
  },
  modelValue: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue', 'close'])

const { t } = useTranslate()
const searchQuery = ref('')
const selectedPassports = ref([])

const jobListIdRef = computed(() => props.jobListId)
const modalOpen = computed(() => props.isVisible)

const { data: applicantsData, isLoading } = useSubLedgerApplicantsQuery(jobListIdRef, modalOpen)

const applicants = computed(() => applicantsData.value ?? [])

const filteredApplicants = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return applicants.value

  return applicants.value.filter((applicant) => {
    const passport = String(applicant.passport_no ?? '').toLowerCase()
    const name = String(applicant.full_name ?? '').toLowerCase()
    return passport.includes(query) || name.includes(query)
  })
})

const isSelected = (passportNo) =>
  selectedPassports.value.includes(String(passportNo ?? '').trim().toUpperCase())

const togglePassport = (passportNo) => {
  const normalized = String(passportNo ?? '').trim().toUpperCase()
  if (!normalized) return

  if (isSelected(normalized)) {
    selectedPassports.value = selectedPassports.value.filter((item) => item !== normalized)
    return
  }

  selectedPassports.value = [...selectedPassports.value, normalized]
}

const selectAllVisible = () => {
  const visible = filteredApplicants.value
    .map((item) => String(item.passport_no ?? '').trim().toUpperCase())
    .filter(Boolean)

  selectedPassports.value = [...new Set([...selectedPassports.value, ...visible])]
}

const clearSelection = () => {
  selectedPassports.value = []
}

const resetModalState = () => {
  searchQuery.value = ''
  selectedPassports.value = parseSubLedgerPassports(props.modelValue)
}

watch(
  () => [props.isVisible, props.modelValue],
  ([visible]) => {
    if (visible) {
      resetModalState()
    }
  },
  { immediate: true },
)

const handleClose = () => {
  emit('close')
}

const handleApply = () => {
  emit('update:modelValue', formatSubLedgerPassports(selectedPassports.value))
  emit('close')
}
</script>

<template>
  <div class="space-y-1.5">
    <p
      v-if="showDemandLetterWarning"
      class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800"
    >
      {{ t('journals.demand_letter_required') }}
    </p>

    <div class="flex flex-wrap items-center gap-2">
      <button
        v-for="source in visibleProjectSources"
        :key="source.id"
        type="button"
        class="rounded-md border px-3 py-1.5 text-xs font-semibold transition-all"
        :class="tabClass(source.id)"
        @click="setSource(source.id)"
      >
        {{ source.label }}
      </button>
    </div>

    <BaseSearchSelect
      :id="inputId"
      :model-value="modelValue"
      :options="currentOptions"
      :placeholder="currentPlaceholder"
      :disabled="isCurrentLoading"
      :filter-fn="currentFilterFn"
      teleport-dropdown
      list-class-name="max-h-72"
      @update:model-value="emit('update:modelValue', $event)"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import {
  filterDemandLetterOption,
  filterJobOption,
  parseProjectId,
  PROJECT_SOURCE_DEMAND_LETTER,
  PROJECT_SOURCE_JOB,
} from '../../data/postJournalStatic'
import {
  useDemandLetterSelectOptionsQuery,
  useJobSelectOptionsQuery,
} from '../../queries/useJobOptionsQuery'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  inputId: {
    type: String,
    default: 'project_id',
  },
  enabled: {
    type: Boolean,
    default: true,
  },
  demandLetterRequired: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useTranslate()
const activeSource = ref(PROJECT_SOURCE_JOB)

const hasDemandLetterSelected = computed(() => {
  const parsed = parseProjectId(props.modelValue)
  return parsed.type === PROJECT_SOURCE_DEMAND_LETTER && Boolean(parsed.id)
})

const showDemandLetterWarning = computed(
  () => props.demandLetterRequired && !hasDemandLetterSelected.value,
)

const visibleProjectSources = computed(() => {
  const sources = [{ id: PROJECT_SOURCE_JOB, label: t('journals.tab_jobs') }]

  if (props.demandLetterRequired) {
    sources.push({
      id: PROJECT_SOURCE_DEMAND_LETTER,
      label: t('journals.tab_demand_letters'),
    })
  }

  return sources
})

const enabledRef = computed(() => props.enabled)
const loadJobs = computed(
  () => enabledRef.value && activeSource.value === PROJECT_SOURCE_JOB,
)
const loadDemandLetters = computed(
  () => enabledRef.value && activeSource.value === PROJECT_SOURCE_DEMAND_LETTER,
)

const { jobSelectOptions, isLoading: isJobOptionsLoading } = useJobSelectOptionsQuery(loadJobs)
const { demandLetterSelectOptions, isLoading: isDemandLetterOptionsLoading } =
  useDemandLetterSelectOptionsQuery(loadDemandLetters)

const isJobsSource = computed(() => activeSource.value === PROJECT_SOURCE_JOB)

const currentOptions = computed(() =>
  isJobsSource.value ? jobSelectOptions.value : demandLetterSelectOptions.value,
)

const isCurrentLoading = computed(() =>
  isJobsSource.value ? isJobOptionsLoading.value : isDemandLetterOptionsLoading.value,
)

const currentFilterFn = computed(() =>
  isJobsSource.value ? filterJobOption : filterDemandLetterOption,
)

const currentPlaceholder = computed(() => t('journals.search'))

const setSource = (sourceId) => {
  if (activeSource.value === sourceId) return
  activeSource.value = sourceId
  emit('update:modelValue', '')
}

const tabClass = (sourceId) => {
  const isActive = activeSource.value === sourceId
  const isRequiredDemandLetterTab =
    props.demandLetterRequired && sourceId === PROJECT_SOURCE_DEMAND_LETTER

  if (isRequiredDemandLetterTab) {
    return isActive
      ? 'border-amber-500 bg-amber-50 text-amber-900 ring-1 ring-amber-400'
      : 'border-amber-400 bg-amber-50 text-amber-800 hover:border-amber-500'
  }

  if (isActive) {
    return 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
  }

  return 'border-gray-200 bg-white text-gray-600 hover:border-primary hover:bg-primary-light!'
}

watch(
  () => props.demandLetterRequired,
  (required) => {
    if (required) {
      activeSource.value = PROJECT_SOURCE_DEMAND_LETTER

      const parsed = parseProjectId(props.modelValue)
      if (parsed.type === PROJECT_SOURCE_JOB) {
        emit('update:modelValue', '')
      }
      return
    }

    activeSource.value = PROJECT_SOURCE_JOB

    const parsed = parseProjectId(props.modelValue)
    if (parsed.type === PROJECT_SOURCE_DEMAND_LETTER) {
      emit('update:modelValue', '')
    }
  },
  { immediate: true },
)

watch(
  () => props.modelValue,
  (value) => {
    if (!value) return
    activeSource.value = parseProjectId(value).type
  },
  { immediate: true },
)
</script>

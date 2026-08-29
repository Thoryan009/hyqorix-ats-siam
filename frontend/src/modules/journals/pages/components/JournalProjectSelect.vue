<template>
  <div class="space-y-1.5">
    <div class="flex flex-wrap items-center gap-2">
      <button
        v-for="source in projectSources"
        :key="source.id"
        type="button"
        class="rounded-md border px-3 py-1.5 text-xs font-semibold transition-all"
        :class="
          activeSource === source.id
            ? 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
            : 'border-gray-200 bg-white text-gray-600 hover:border-primary hover:bg-primary-light!'
        "
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
})

const emit = defineEmits(['update:modelValue'])

const { t } = useTranslate()
const activeSource = ref(PROJECT_SOURCE_JOB)

const projectSources = computed(() => [
  { id: PROJECT_SOURCE_JOB, label: t('journals.tab_jobs') },
  { id: PROJECT_SOURCE_DEMAND_LETTER, label: t('journals.tab_demand_letters') },
])

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

const currentPlaceholder = computed(() => {
  if (isCurrentLoading.value) {
    return isJobsSource.value
      ? t('journals.loading_jobs')
      : t('journals.loading_demand_letters')
  }

  if (!currentOptions.value.length) {
    return isJobsSource.value
      ? t('journals.no_jobs_found')
      : t('journals.no_demand_letters_found')
  }

  return isJobsSource.value
    ? t('journals.select_project')
    : t('journals.select_demand_letter')
})

const setSource = (sourceId) => {
  if (activeSource.value === sourceId) return
  activeSource.value = sourceId
  emit('update:modelValue', '')
}

watch(
  () => props.modelValue,
  (value) => {
    if (!value) return
    activeSource.value = parseProjectId(value).type
  },
  { immediate: true },
)
</script>

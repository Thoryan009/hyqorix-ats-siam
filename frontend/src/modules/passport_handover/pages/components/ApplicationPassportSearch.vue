<template>
  <div class="space-y-2">
    <BaseLabel>{{ label }}</BaseLabel>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
      <BaseInput
        :model-value="searchQuery"
        :placeholder="placeholder"
        :class-name="inputClass"
        @update:model-value="onSearchInput"
        @focus="showHints = true"
        @blur="handleBlur"
      />
      <BaseButton
        v-if="mode === 'multi'"
        class="shrink-0 bg-primary text-white hover:opacity-90"
        :disabled="!canAddFromInput"
        @click="addFromInput"
      >
        {{ $t('passport.add') }}
      </BaseButton>
    </div>

    <p v-if="searchQuery.length > 0 && searchQuery.length < 3" class="text-xs text-gray-500">
      {{ $t('passport.type_minimum_3_characters') }}
    </p>

    <div
      v-if="showHints && searchQuery.length >= 3"
      class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
    >
      <div
        v-if="isFetching"
        class="px-4 py-3 text-sm text-gray-500"
      >
        {{ $t('passport.searching_applications') }}
      </div>
      <div
        v-else-if="!filteredHints.length"
        class="px-4 py-3 text-sm text-gray-500"
      >
        {{ $t('passport.no_application_found') }}
      </div>
      <div v-else class="divide-y divide-gray-100">
        <button
          v-for="item in filteredHints"
          :key="item.id"
          type="button"
          class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-primary/5"
          @mousedown.prevent="selectApplication(item)"
        >
          <div class="flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ item.fullName }}</p>
            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
              <span>
                {{ $t('passport.passport') }}:
                <span class="font-medium text-gray-800">{{ item.passportNo }}</span>
              </span>
              <span v-if="item.applicationCode">
                {{ $t('passport.application_id') }}:
                <span class="font-medium text-gray-800">{{ item.applicationCode }}</span>
              </span>
              <span v-if="item.mobile">
                {{ $t('passport.mobile') }}:
                <span class="font-medium text-gray-800">{{ item.mobile }}</span>
              </span>
            </div>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import {
  mapApplicationSearchItem,
  usePassportHandoverApplicationSearch,
} from '../../queries/usePassportHandoverApplicationSearch'

const props = defineProps({
  label: { type: String, default: 'Search by Passport No' },
  placeholder: { type: String, default: 'Enter passport no to search' },
  mode: {
    type: String,
    default: 'single',
    validator: (value) => ['single', 'multi'].includes(value),
  },
  excludeApplicationIds: { type: Array, default: () => [] },
  inputClass: {
    type: String,
    default:
      'w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary',
  },
})

const emit = defineEmits(['select'])

const searchQuery = ref('')
const showHints = ref(false)

const { data, isFetching } = usePassportHandoverApplicationSearch(searchQuery)

const applicationHints = computed(() => {
  const rows = data.value?.data?.data ?? []

  return rows.map(mapApplicationSearchItem)
})

const excludedIdSet = computed(() => new Set(props.excludeApplicationIds.map(Number)))

const filteredHints = computed(() =>
  applicationHints.value.filter((item) => !excludedIdSet.value.has(Number(item.id)))
)

const canAddFromInput = computed(() => {
  if (props.mode !== 'multi' || searchQuery.value.trim().length < 3) return false
  return filteredHints.value.length === 1
})

watch(searchQuery, (value) => {
  if (!value?.trim()) {
    showHints.value = false
  }
})

function onSearchInput(value) {
  searchQuery.value = value
  if (value?.trim().length >= 3) {
    showHints.value = true
  }
}

function handleBlur() {
  window.setTimeout(() => {
    showHints.value = false
  }, 150)
}

function selectApplication(item) {
  emit('select', item)
  searchQuery.value = ''
  showHints.value = false
}

function addFromInput() {
  if (!canAddFromInput.value) return
  selectApplication(filteredHints.value[0])
}
</script>

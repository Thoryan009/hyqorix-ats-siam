<template>
  <div class="relative w-56">
    <input
      :value="searchQuery"
      type="text"
      class="w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm"
      placeholder="Search by passport no"
      @input="onSearchInput($event.target.value)"
      @focus="showHints = true"
      @blur="handleBlur"
      @keyup.enter="handleEnter"
    />

    <div
      v-if="showHints && searchQuery.trim().length >= 1"
      class="absolute left-0 right-0 top-full z-20 mt-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
    >
      <div v-if="isFetching" class="px-4 py-3 text-sm text-gray-500">
        Searching passport handovers...
      </div>
      <div v-else-if="!searchResults.length" class="px-4 py-3 text-sm text-gray-500">
        No passport handover found for this passport no.
      </div>
      <div v-else class="max-h-72 divide-y divide-gray-100 overflow-y-auto">
        <button
          v-for="item in searchResults"
          :key="item.id"
          type="button"
          class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-primary/5"
          @mousedown.prevent="selectItem(item)"
        >
          <div class="flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ item.candidateName }}</p>
            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
              <span>
                Passport:
                <span class="font-medium text-gray-800">{{ item.passportNo }}</span>
              </span>
              <span v-if="item.handoverNo">
                Handover:
                <span class="font-medium text-gray-800">{{ item.handoverNo }}</span>
              </span>
              <span v-if="item.takerName">
                Taker:
                <span class="font-medium text-gray-800">{{ item.takerName }}</span>
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
import {
  mapPassportHandoverSearchItem,
  usePassportHandoverPassportSearch,
} from '../../queries/usePassportHandoverPassportSearch'

const emit = defineEmits(['select'])

const searchQuery = ref('')
const showHints = ref(false)

const { data, isFetching } = usePassportHandoverPassportSearch(searchQuery)

const searchResults = computed(() => {
  const rows = data.value?.data?.data ?? []

  return rows.map(mapPassportHandoverSearchItem)
})

watch(searchQuery, (value) => {
  if (!value?.trim()) {
    showHints.value = false
  }
})

function onSearchInput(value) {
  searchQuery.value = value
  if (value?.trim().length >= 1) {
    showHints.value = true
  }
}

function handleBlur() {
  window.setTimeout(() => {
    showHints.value = false
  }, 150)
}

function selectItem(item) {
  emit('select', {
    handoverId: item.handoverId,
    passportNo: item.passportNo,
  })
  searchQuery.value = ''
  showHints.value = false
}

function handleEnter() {
  if (!searchResults.value.length) return
  selectItem(searchResults.value[0])
}
</script>

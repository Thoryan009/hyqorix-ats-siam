<template>
  <div ref="containerRef" class="relative z-[100] w-full max-w-xl">
    <div class="relative">
      <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
      <input
        v-model="searchQuery"
        type="text"
        autocomplete="off"
        :placeholder="t('application.search_candidate_by_name_or_passport_no')"
        class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20"
        @focus="isOpen = true"
        @keydown.escape="closeDropdown"
      />
    </div>

    <div
      v-if="isOpen"
      class="absolute left-0 right-0 top-full z-[100] mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
    >
      <p
        v-if="showHint"
        class="border-b border-gray-100 px-4 py-3 text-xs text-gray-500"
      >
        {{t('passport.type_minimum_3_characters')}}
      </p>

      <p v-else-if="isLoading" class="px-4 py-3 text-sm text-gray-500">{{ t('shared.placeholders.searching') }}</p>

      <p
        v-else-if="hasSearched && !results.length"
        class="px-4 py-3 text-sm text-gray-500"
      >
       {{ t('passport.no_application_found') }}
      </p>

      <ul v-else class="max-h-80 overflow-y-auto">
        <li
          v-for="result in results"
          :key="result.application_id"
          :class="[
            'border-b border-gray-100 px-4 py-3 last:border-b-0',
            result.can_open_ats
              ? 'cursor-pointer transition hover:bg-indigo-50'
              : 'cursor-not-allowed bg-gray-50/70',
          ]"
          @mousedown.prevent="result.can_open_ats && goToApplication(result)"
        >
          <div class="flex items-start gap-3">
            <CandidateAvatar
              :image-url="result.worker_image_url"
              :alt="result.full_name"
              size="sm"
            />

            <div class="min-w-0 flex-1">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-gray-900">{{ result.full_name }}</p>
                  <p class="mt-0.5 text-xs text-gray-500">
                    Passport:
                    <span class="font-medium text-gray-700">{{ result.passport_no }}</span>
                  </p>
                  <p class="mt-0.5 text-xs text-gray-500">
                    Sex:
                    <span class="font-medium text-gray-700">{{ result.sex || 'N/A' }}</span>
                  </p>
                </div>
                <span
                  class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                  :class="
                    result.can_open_ats
                      ? 'bg-primary/10 text-primary'
                      : 'bg-gray-200 text-gray-600'
                  "
                >
                  {{ result.application_status }}
                </span>
              </div>

              <div class="mt-2 grid grid-cols-1 gap-1 text-xs text-gray-600 sm:grid-cols-2">
                <p class="truncate">
                  <span class="text-gray-400">Job:</span>
                  {{ result.job_name_with_code }}
                </p>
                <p class="truncate">
                  <span class="text-gray-400">Client:</span>
                  {{ result.client_name }}
                </p>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { fetchAtsQuickSearch } from '@/modules/job/services/atsService'
import CandidateAvatar from '@/shared/components/ui/CandidateAvatar.vue'
import {useTranslate} from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const QUICK_SEARCH_CANDIDATE_KEY = 'ats_quick_search_candidate'

const router = useRouter()

const containerRef = ref(null)
const searchQuery = ref('')
const results = ref([])
const isOpen = ref(false)
const isLoading = ref(false)
const hasSearched = ref(false)

let debounceTimer = null

const showHint = computed(() => {
  return isOpen.value && searchQuery.value.trim().length < 2
})

const closeDropdown = () => {
  isOpen.value = false
}

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const runSearch = async (query) => {
  if (query.length < 2) {
    results.value = []
    hasSearched.value = false
    return
  }

  isLoading.value = true
  hasSearched.value = true

  try {
    const response = await fetchAtsQuickSearch(query)
    results.value = response?.data?.data ?? []
  } catch {
    results.value = []
  } finally {
    isLoading.value = false
  }
}

watch(searchQuery, (value) => {
  clearTimeout(debounceTimer)
  const query = value.trim()

  if (query.length < 2) {
    results.value = []
    hasSearched.value = false
    return
  }

  debounceTimer = setTimeout(() => runSearch(query), 300)
})

const goToApplication = (result) => {
  if (!result?.can_open_ats) return

  closeDropdown()
  searchQuery.value = ''

  sessionStorage.setItem(QUICK_SEARCH_CANDIDATE_KEY, JSON.stringify(result))

  router.push({
    name: 'Single ATS',
    params: { id: result.job_list_id },
    query: {
      application_id: result.application_id,
      process_id: result.process_id,
      from_quick_search: '1',
    },
  })
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  clearTimeout(debounceTimer)
})
</script>

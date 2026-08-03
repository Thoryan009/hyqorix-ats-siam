<template>
  <div class="bg-white rounded-lg shadow-md p-4">
    <div class="flex items-center justify-between mb-4 border-b pb-3">
      <BaseOverlayLoading :show="store.loading" />
      <!-- <pre>{{ applicants }}</pre> -->

      <div class="flex items-center gap-2">
        <div class="flex items-center justify-center">
          <input
            type="checkbox"
            class="w-5 h-5 cursor-pointer accent-[#588F36] rounded transition-transform hover:scale-110"
            :checked="isAllVisibleSelected"
            @change="handleToggleAll($event.target.checked)"
          />
        </div>
        <svg class="w-5 h-5 text-[#B400C9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
          />
        </svg>
        <h3 class="text-lg font-semibold text-gray-800"> {{ $t('shared.labels.applicants') }}</h3>
      </div>
      <div v-can="'ats.update'" class="flex items-center gap-2">
        <div>
          <BaseSelect
            @change="handleBulkProcess"
            v-model="targetProcessId"
            v-if="selectedIds.length > 1"
            :options="store.allProcesses.filter((process) => process.id > selectedProcessId)"
            :placeholder="'Select Process'"
            class="w-full"
          />
        </div>
        <span
          class="px-2 py-1 bg-primary/10 text-[#B400C9] text-xs font-semibold rounded-full"
        >{{ selectedIds?.length || 0 }}</span>
      </div>
    </div>

    <div class="mb-3">
      <BaseInput
        v-model="searchQuery"
        :placeholder="$t('ats.search_by_name_or_passport')"
        className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#B400C9]/30 focus:border-[#B400C9]"
      />
    </div>

    <div class="space-y-2 max-h-150 overflow-y-auto applicant-scroller pr-1">
      <p
        v-if="filteredApplicants.length === 0"
        class="text-center text-gray-500 py-6 text-sm"
      >
        {{ $t('ats.no_applicants_found') }}
      </p>

      <div
        v-for="(applicant, index) in filteredApplicants"
        :key="applicant.id"
        :class="[
          'p-3 rounded-lg cursor-pointer transition-all duration-200 border-l-4',
          selectedApplicant?.id === applicant.id
            ? 'bg-[#B400C9] text-white border-[#B400C9] shadow-lg ring-2 ring-[#B400C9]/30'
            : 'bg-gray-50 border-gray-300 hover:bg-gray-100 hover:border-[#B400C9]/50 hover:shadow-md',
        ]"
      >
        <div class="flex items-center gap-3">
          <div class="flex items-center justify-center">
            <input
              type="checkbox"
              :checked="selectedIds.includes(applicant.id)"
              @change="emits('toggleRow', applicant.id)"
              class="w-5 h-5 cursor-pointer accent-[#588F36] rounded transition-transform hover:scale-110"
            />
          </div>
          <div
            :class="[
              'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
              selectedApplicant?.id === applicant.id
                ? 'bg-white/20 text-white'
                : 'bg-[#B400C9]/10 text-[#B400C9]',
            ]"
          >{{ index + 1 }}</div>

          <div @click="select(applicant)" class="flex-1 flex items-center min-w-0">
            <div class="flex-1 min-w-0">
              <h4
                :class="[
                  'font-semibold text-sm truncate',
                  selectedApplicant?.id === applicant.id ? 'text-white' : 'text-gray-800',
                ]"
              >{{ applicant.full_name }}</h4>
              <div class="flex items-center gap-2 mt-0.5">
                <p
                  :class="[
                    'text-sm',
                    selectedApplicant?.id === applicant.id ? 'text-white' : 'text-gray-800',
                  ]"
                >{{ applicant.passport_no || 'N/A' }}</p>
              </div>
            </div>

            <div class="flex-shrink-0">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap',
                  selectedApplicant?.id === applicant.id
                    ? 'bg-white/20 text-white'
                    : getStatusColor(applicant.current_process),
                ]"
              >

              <!-- {{ $t(`ats.process.${applicant.current_process}`)}} -->
            {{  applicant.current_process === 'rejected'  || applicant.current_process === 'declined' || applicant.current_process === 'deployed' ? t(`shared.labels.${applicant.current_process}`) :  t(`dashboard.pipeline.${applicant.current_process}`)}}
            </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { getStatusColor } from '../../utils/statusColors'
import { useAtsStore } from '../../store/atsStore'
import { computed, ref } from 'vue'
import { useAtsMutations } from '../../queries/useAtsMutations'
import BaseOverlayLoading from '@/shared/components/base/BaseOverlayLoading.vue'
import { showConfirmDialog } from '@/shared/utils/sweetAlertUtils'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
const props = defineProps({
  applicants: Array,
  selectedApplicant: Object,
  selectedProcessId: Number,
  selectedIds: {
    type: Array,
    default: () => [],
  },
})

const targetProcessId = ref(null)
const searchQuery = ref('')

const store = useAtsStore()

const filteredApplicants = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  const list = props.applicants ?? []

  if (!query) return list

  return list.filter((applicant) => {
    const name = (applicant.full_name ?? '').toLowerCase()
    const passport = (applicant.passport_no ?? '').toLowerCase()

    return name.includes(query) || passport.includes(query)
  })
})

const isAllVisibleSelected = computed(() => {
  const list = filteredApplicants.value

  return list.length > 0 && list.every((applicant) => props.selectedIds.includes(applicant.id))
})

const handleToggleAll = (checked) => {
  emits('toggleAll', checked, filteredApplicants.value)
}

const emits = defineEmits([
  'update:selectedApplicant',
  'toggleApplicantSelection',
  'toggleAll',
  'toggleRow',
  'clearSelection',
])

const select = (applicant) => {
  emits('toggleApplicantSelection', applicant)
}

// Mutations
const { createBulkNextProcess } = useAtsMutations(store.moduleName, {
  onSuccess() {
    emits('clearSelection')
    targetProcessId.value = null
  },
  onError(error) {
    console.log('Custom error handling', error)
    store.loading = false
  },
})

const handleBulkProcess = async () => {
  const result = await showConfirmDialog({
    title: 'Confirm Process Move',
    text: `Process ${props.selectedIds.length} selected application(s)?`,
    icon: 'question',
    confirmButtonText: 'Yes, process now',
    cancelButtonText: 'Cancel',
  })

  if (!result.isConfirmed) return

  try {
    store.loading = true

    await createBulkNextProcess.mutateAsync({
      next_process_id: targetProcessId.value,
      application_ids: props.selectedIds,
    })
  } finally {
    store.loading = false
  }
}
</script>

<style scoped>
.applicant-scroller::-webkit-scrollbar {
  width: 5px;
}

.applicant-scroller::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 10px;
}

.applicant-scroller::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #e2e8f0, #cbd5e1);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.applicant-scroller::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #cbd5e1, #94a3b8);
  box-shadow: 0 0 6px rgba(148, 163, 184, 0.4);
}

.applicant-scroller {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}
</style>

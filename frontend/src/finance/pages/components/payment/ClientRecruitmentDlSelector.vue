<template>
  <div class="space-y-4 rounded-lg border border-violet-100 bg-violet-50/40 p-4">
    <div>
      <h4 class="text-sm font-semibold text-gray-900">Select Demand Letter (DL)</h4>
      <p class="mt-1 text-xs text-gray-500">
        Client Recruitment Expense bills must be linked to a demand letter number
      </p>
    </div>

    <div class="space-y-2">
      <BaseLabel for="client_recruitment_dl_id">Demand Letter</BaseLabel>
      <BaseSearchSelect
        id="client_recruitment_dl_id"
        :model-value="modelValue"
        :options="demandLetterOptions"
        :placeholder="demandLetterStore.isLoadingDemandLetters ? 'Loading demand letters...' : 'Search and select demand letter'"
        :required="true"
        :disabled="demandLetterStore.isLoadingDemandLetters || !demandLetterOptions.length"
        @update:model-value="$emit('update:modelValue', $event ? String($event) : '')"
      />
      <p v-if="demandLetterStore.isLoadingDemandLetters" class="text-xs text-gray-500">
        Loading demand letters...
      </p>
    </div>

    <div
      v-if="!demandLetterStore.isLoadingDemandLetters && !demandLetterOptions.length"
      class="rounded-lg border border-dashed border-violet-200 bg-white px-4 py-5 text-center text-sm text-gray-500"
    >
      No demand letters with applications found.
    </div>

    <div
      v-else-if="selectedDemandLetter"
      class="rounded-lg border border-violet-100 bg-white px-4 py-3 text-sm"
    >
      <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
          <p class="font-semibold text-gray-900">{{ selectedDemandLetter.dl_no }}</p>
          <p class="mt-1 text-gray-600">{{ selectedDemandLetter.client_name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <span
            v-if="selectedDemandLetter.applications_count"
            class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700"
          >
            {{ selectedDemandLetter.applications_count }}
            {{ selectedDemandLetter.applications_count === 1 ? 'Application' : 'Applications' }}
          </span>
          <span
            v-if="selectedDemandLetter.country"
            class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700"
          >
            {{ selectedDemandLetter.country }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useDemandLetterStore } from '@/finance/store/demandLetterStore'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
})

defineEmits(['update:modelValue'])

const demandLetterStore = useDemandLetterStore()

onMounted(() => {
  demandLetterStore.fetchDemandLetterList()
})

const demandLetterOptions = computed(() => demandLetterStore.getDemandLetterSelectOptions())

const selectedDemandLetter = computed(() => demandLetterStore.getDemandLetter(props.modelValue))
</script>

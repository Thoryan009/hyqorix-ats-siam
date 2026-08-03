<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="'Client Send Bill Modal'"
    @close="store.handleToggleModal"
    :className="'xl:max-w-7xl'"
  >
    <div class="h-150 space-y-4">
      <SelectedCandidatesCard :selectedRows="localSelectedRows" />
      <SelectedCandidatesTable
        :selectedRows="localSelectedRows"
        :groupedSelectedRows="groupedSelectedRows"
        v-if="showCandidates"
      />

      <!-- Load More / Show Less -->
      <div class="flex justify-start my-4">
        <span
          class="text-blue-600 hover:text-blue-800 cursor-pointer underline font-bold"
          @click="toggleShowCandidates"
        >
          <template v-if="!showCandidates">Show {{ localSelectedRows.length }} Candidates</template>
          <template v-else>Show Less</template>
        </span>
      </div>

      <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <div class="w-full lg:w-1/3 flex-shrink-0">
          <DemandLetterInfoCard :selectedRows="localSelectedRows" :selectedIds="selectedIds" :filters="filters"
          @reset="$emit('reset')"
          />
        </div>
        <div class="w-full lg:w-2/3">
          <EmailFormCard :filters="filters" @reset="emit('reset')" />
        </div>
      </div>
      <div class="flex justify-end gap-2 py-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700"
          @click="store.handleToggleModal"
        >Cancel</BaseButton>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useClientBillStore } from '../../store/clientBillStore'
import SelectedCandidatesCard from './SelectedCandidatesCard.vue'
import SelectedCandidatesTable from './SelectedCandidatesTable.vue'
import DemandLetterInfoCard from './DemandLetterInfoCard.vue'
import EmailFormCard from './EmailFormCard.vue'

const props = defineProps({
  selectedIds: { type: Array, required: true },
  selectedRows: { type: Array, required: true },
  filters: { type: Object, required: true },

})

// declare the event you will emit
const emit = defineEmits(['reset'])

const store = useClientBillStore()
const showCandidates = ref(false)
const toggleShowCandidates = () => {
  showCandidates.value = !showCandidates.value
}

// ✅ Keep a local copy of selectedRows to make it stable
const localSelectedRows = ref([...props.selectedRows])
watch(
  () => props.selectedRows,
  (newRows) => {
    // update local copy only if needed
    if (newRows?.length) localSelectedRows.value = [...newRows]
  },
  { deep: true, immediate: true }
)

// Group selectedRows by job
const groupedSelectedRows = computed(() => {
  const map = {}
  localSelectedRows.value.forEach((row) => {
    if (!map[row.job_id]) {
      map[row.job_id] = {
        job: row.job,
        job_id: row.job_id,
        applications: [],
      }
    }
    map[row.job_id].applications.push(row)
  })
  return Object.values(map)
})
</script>

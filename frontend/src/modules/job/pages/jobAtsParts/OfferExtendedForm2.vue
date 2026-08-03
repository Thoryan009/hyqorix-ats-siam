<template>
  <div class="space-y-2" v-if="selectedApplicant.processes.some((p) => p.type === 'offer_extended')">
    <PageTitle>Process 1: Offer Extended Form</PageTitle>

    <div>
      <BaseLabel>Offer Status</BaseLabel>
      <BaseSelect v-model="offerExtended.data.offer_status" :options="[
        { name: 'Select', id: '' },
        { name: 'Accepted', id: 'accepted' },
        { name: 'Rejected', id: 'rejected' },
      ]" class="w-full" />
    </div>

    <div>
      <BaseLabel>Process Status</BaseLabel>
      <BaseSelect v-model="offerExtended.status" :options="[
        { name: 'Pending', id: 'pending' },
        { name: 'Draft', id: 'draft' },
        { name: 'Completed', id: 'completed' },
        { name: 'Rejected', id: 'rejected' },
      ]" class="w-full" />
    </div>

    <!-- Remarks -->
    <div>
      <BaseLabel>Remarks</BaseLabel>
      <BaseTextArea v-model="offerExtended.remarks" rows="2" placeholder="Enter your remarks here..." />
    </div>

    <!-- Started At -->
    <div>
      <BaseLabel>Started At</BaseLabel>
      <p class="text-black text-[15px]">{{ offerExtended.started_at || 'Not started yet' }}</p>
    </div>

    <!-- Completed At -->
    <div>
      <BaseLabel>Completed At</BaseLabel>
      <p class="text-black text-[15px]">{{ offerExtended.completed_at || 'Not completed yet' }}</p>
    </div>

    <!-- Form Actions -->
    <div class="grid grid-cols-3 gap-3 pt-4">
      <BaseButton @click="emit('updateProcess', offerExtended)">Submit</BaseButton>
      <BaseSelect v-model="selectedNextProcess" :options="nextProcessOptions.filter(
        (option) =>
          !selectedApplicant.active_processes.some(
            (p) => p === option.id
          )
      )" class="w-full" />
      <BaseButton @click="emit('nextProcess', selectedNextProcess)" class="bg-green-600 text-white hover:bg-green-700">
        Next Process</BaseButton>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { nextProcessOptions } from '@/modules/job/data/nextProcessOptions'
import PageTitle from '@/shared/components/ui/PageTitle.vue'

const props = defineProps({
  selectedApplicant: {
    type: Object,
    required: true,
  },
})

const selectedNextProcess = ref('')

const emit = defineEmits(['updateProcess', 'nextProcess'])

const offerExtended = computed(() => {
  const applicant = props.selectedApplicant
  if (!applicant) return null

  const p = applicant.processes?.find((x) => x.type === 'offer_extended')

  if (!p) return null

  return {
    process_id: p.id,
    type: p.type,
    status: p.status || 'pending',
    remarks: p.remarks || '',
    started_at: p.started_at || '',
    completed_at: p.completed_at || '',
    data: {
      offer_status: p.data?.offer_status || '',
    },
  }
})


</script>

<style></style>

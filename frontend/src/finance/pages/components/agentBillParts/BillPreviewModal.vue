<template>
  <BaseModal
    :isVisible="isVisible"
    title="Bill Preview"
    className="xl:max-w-[95vw] max-h-[90vh]"
    @close="emit('close')"
  >
    <BillPreview
      :agent="agent"
      :job="job"
      :selected-candidates="selectedCandidates"
      :total-amount="totalAmount"
      :bill-no="billNo"
      :bill-date="billDate"
    />

    <div class="mt-4 flex justify-end gap-3 print:hidden">
      <BaseButton class="bg-gray-100 text-gray-800 hover:bg-gray-200" @click="emit('close')">
        Close
      </BaseButton>
      <BaseButton class="bg-green-600 text-white hover:bg-green-700" @click="printPreview">
        <i class="fa fa-print mr-1"></i> Print / PDF
      </BaseButton>
    </div>
  </BaseModal>
</template>

<script setup>
import BillPreview from './BillPreview.vue'

defineProps({
  isVisible: { type: Boolean, default: false },
  agent: { type: Object, default: null },
  job: { type: Object, default: null },
  selectedCandidates: { type: Array, default: () => [] },
  totalAmount: { type: Number, default: 0 },
  billNo: { type: String, default: '' },
  billDate: { type: String, default: '' },
})

const emit = defineEmits(['close'])

function printPreview() {
  window.print()
}
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }

  #bill-preview-print,
  #bill-preview-print * {
    visibility: visible;
  }

  #bill-preview-print {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 1rem;
  }
}
</style>

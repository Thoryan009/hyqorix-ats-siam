<template>
  <BaseModal
    :isVisible="isVisible"
    title="Merged Document Preview"
    @close="$emit('close')"
    :className="'xl:max-w-[90vw] max-h-[95vh]'"
  >
    <!-- Loading State -->
    <div v-if="mergeDocumentsLoading" class="flex flex-col items-center justify-center py-20 gap-4">
      <i class="fa fa-spinner fa-spin text-4xl text-blue-500"></i>
      <p class="text-gray-500 text-sm">Merging documents, please wait...</p>
    </div>

    <div v-else class="flex flex-col gap-4">
      <!-- Toolbar -->
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-500 truncate max-w-[60%]">{{ fileUrl }}</span>
        <a
          :href="fileUrl"
          :download="fileName"
          class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
        >
          <i class="fa fa-download"></i>
          Download PDF
        </a>
      </div>

      <!-- PDF Preview -->
      <div class="w-full border border-gray-200 rounded-lg overflow-hidden" style="height: 75vh">
        <iframe :src="iframeSrc" class="w-full h-full" type="application/pdf" title="PDF Preview" />
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  fileUrl: {
    type: String,
    default: '',
  },
  mergeDocumentsLoading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])

const fileName = computed(() => {
  if (!props.fileUrl) return 'document.pdf'
  return props.fileUrl.split('/').pop() || 'document.pdf'
})

// Append #toolbar=1 so browser PDF viewer shows its own toolbar as well
const iframeSrc = computed(() => (props.fileUrl ? `${props.fileUrl}#toolbar=1` : ''))
</script>

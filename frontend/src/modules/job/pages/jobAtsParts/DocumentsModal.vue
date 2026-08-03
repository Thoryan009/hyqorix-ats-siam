<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex justify-end">
      <!-- Background overlay (NO blur) -->
      <div class="fixed inset-0 bg-black/20" @click="$emit('update:modelValue', false)"></div>

      <!-- Slide-over panel -->
      <div
        class="relative w-80 max-w-full h-full overflow-y-auto transform transition-transform duration-300 shadow-xl rounded-l-xl"
        :class="modelValue ? 'translate-x-0' : 'translate-x-full'"
        style="background: #f0fdf4"
      >
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-green-200">
          <div class="flex items-center gap-2">
            <i class="fa fa-file-text text-green-600"></i>
            <h2 class="text-lg font-semibold text-green-800">
              {{ title }}
            </h2>
          </div>

          <button
            class="text-green-600 hover:text-red-500 text-lg transition"
            @click="$emit('update:modelValue', false)"
          >
            <i class="fa fa-times"></i>
          </button>
        </div>


        <!-- Documents List -->
        <div class="p-4 space-y-2">
          <div v-if="documents.length > 0">
            <div
              v-for="doc in documents"
              :key="doc.label"
              class="flex my-1 items-center justify-between p-3 rounded-lg bg-white border border-green-100 hover:border-green-300 hover:shadow-md transition-all"
            >
              <!-- Left -->
              <div class="flex items-center gap-2">
                <i class="fa fa-file text-green-500"></i>
                <span class="text-green-800 font-medium text-sm">
                  {{ doc.label }}
                </span>
              </div>

              <!-- ✅ Right (GROUPED BUTTONS) -->
              <div class="flex items-center gap-2">
                <!-- View Button -->
                <a
                  :href="doc.link"
                  target="_blank"
                  class="flex items-center gap-1 text-xs bg-green-50 text-green-400 px-3 py-1 rounded-full transition-all"
                >
                  <i class="fa fa-eye"></i>
                </a>

                <!-- Download Button -->
                <!-- <button
                  @click="handleDownload(doc)"
                  class="flex items-center gap-1 text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded-full transition-all"
                >
                  <i class="fa fa-download"></i>
                </button> -->
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-green-700 text-center mt-10">
            <i class="fa fa-folder-open text-2xl mb-2"></i>
            <p class="italic text-sm">{{ t('ats.no_documents_available')}}</p>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { downloadFile } from '@/shared/utils/download'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
// const handleDownload = (doc) => {
//   if (!doc?.link) return

//   // you can customize filename
//   const fileName = doc.label || 'document'
//   downloadFile(doc.link, fileName)
// }

defineProps({
  modelValue: { type: Boolean, default: false },
  documents: { type: Array, default: () => [] },
  title: { type: String, default: 'Applicant Documents' },
})

defineEmits(['update:modelValue'])
</script>

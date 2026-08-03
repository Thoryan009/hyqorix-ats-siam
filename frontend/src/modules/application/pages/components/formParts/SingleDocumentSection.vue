  <template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <!-- Header -->
      <div class="flex items-center gap-3 pb-3 mb-2 border-b-2 border-orange-500">
        <span
          class="flex items-center justify-center w-8 h-8 rounded-full bg-orange-500 text-white text-sm font-bold shrink-0"
        >4</span>
        <i class="fa fa-file-pdf-o text-orange-500 text-xl"></i>
        <span class="font-semibold text-lg text-gray-800">{{ t('application.single_combined_document') }}</span>
      </div>

      <!-- Hint -->
      <p class="text-sm text-gray-500 mt-2">
        {{ t('application.combine_documents_hint') }}
        <br />
        <span class="text-gray-400">
          {{ t('application.suggested_order') }}: {{ t('application.resume') }} &bull; {{ t('application.experience_certificate') }} &bull; {{ t('application.education_certificate') }} &bull;
          {{ t('application.training_certificate') }} &bull; {{ t('application.passport') }} &bull; {{ t('application.driving_license') }}
        </span>
      </p>



      <!-- PDF Preview -->

            <div class="overflow-hidden" v-if="store.formData?.single_document_preview">
              <div
                class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative"
              >
                <iframe :src="store.formData?.single_document_preview" class="w-full h-full"></iframe>
                <button
                  class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
                  @click="store.cancelImage('single_document')"
                  type="button"
                >
                  <i class="fa fa-times"></i>
                </button>
              </div>
            </div>
            <template v-else-if="store.item?.single_document_url">
              <div
                v-if="store.item.single_document_url.split('?')[0].toLowerCase().endsWith('.pdf')"
                class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50"
              >
                <iframe :src="store.item.single_document_url" class="w-full h-full"></iframe>

              </div>

            </template>

      <!-- Upload Field -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
        <div>
          <BaseLabel for="single_document_path">{{ t('application.upload_single_pdf') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf"
            @change="
              store.handleFileChange($event, 'single_document_path', 'single_document_preview', 400 * 1024)
            "
            :fileName="store.fileName.single_document_path"
          />
          <p class="text-xs text-gray-400 mt-1">{{ t('application.pdf_only_max_400kb') }}</p>
          <div
            v-if="store.fileError.single_document_path"
            class="text-red-600 text-sm mt-1"
          >{{ store.fileError.single_document_path }}</div>
        </div>
      </div>
    </div>
  </template>

<script setup>
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useApplicationStore()
</script>

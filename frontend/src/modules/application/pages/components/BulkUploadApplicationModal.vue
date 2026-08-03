<template>
  <BaseModal
    :isVisible="store.openBulkUploadModal"
    :title="`${t('application.bulk_upload')} ${t('application.applicant')}`"
    @close="store.handleToggleModalBulkUpload"
  >
    <div class="space-y-4">
      <!-- Info / Rules -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
        <div class="text-center">
          <i class="fa fa-info-circle text-blue-600 text-xl mb-2"></i>
          <h3 class="text-sm font-semibold text-blue-800 mb-1">{{ t('application.csv_upload_guidelines') }}</h3>
        </div>

        <ul class="list-decimal list-inside text-xs text-blue-800 space-y-1">
          <li>
            {{ t('application.every_csv_record_must_include_given_name_and_sur_name') }}
          </li>
          <li>
            {{ t('application.user_must_select_a_job_before_uploading_the_csv') }}
          </li>
          <li>{{ t('application.all_workers_will_be_associated_with_selected_job') }}</li>
          <li>
            {{ t('application.duplicate_passport_number_record_will_be_skipped') }}
          </li>
        </ul>

        <BaseButton
          @click="downloadFile('/worker_sample.csv', 'sample_worker.csv')"
          class="mx-auto block mt-2 text-xs px-3! py-1! rounded bg-blue-600 hover:bg-blue-700 text-white cursor-pointer"
        >
          <i class="fa fa-download mr-1"></i> {{ t('application.download_sample_csv') }}
        </BaseButton>
      </div>

      <!-- Job selection -->
      <div class="flex flex-col">
        <label class="text-gray-800 text-[15px]">{{ t('application.job_required') }}</label>
        <BaseSelect v-model="selectedJobId" :options="jobs" required :placeholder="t('shared.placeholders.select')" />
      </div>

      <!-- Agent selection -->
      <div class="flex flex-col">
        <label class="text-gray-800 text-[15px]">{{ t('application.agent_required') }}</label>
        <BaseSelect v-model="selectedAgentId" :options="agents" required :placeholder="t('shared.placeholders.select')" />
      </div>

      <!-- File upload area -->
      <div
        class="border-2 border-dashed rounded-lg p-6 transition flex flex-col items-center justify-center space-y-3"
        :class="[
          !selectedJobId
            ? 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed pointer-events-none'
            : 'border-gray-300 hover:border-blue-400 bg-white',
        ]"
      >
        <i class="fa fa-file-csv text-4xl"></i>
        <label
          for="csv-upload"
          class="font-medium"
          :class="
            !selectedJobId
              ? 'text-gray-400 cursor-not-allowed'
              : 'text-blue-600 hover:text-blue-700 cursor-pointer'
          "
          >{{ t('application.click_to_select_csv_file') }}</label
        >
        <input
          id="csv-upload"
          type="file"
          accept=".csv"
          @change="onCsvChange"
          class="hidden"
          :disabled="!selectedJobId"
        />
        <p class="text-xs" :class="!selectedJobId ? 'text-gray-400' : 'text-gray-500'">
          {{ t('application.only_csv_files_are_allowed') }}
        </p>
      </div>

      <!-- Selected file preview -->
      <div v-if="selectedFile" class="bg-gray-50 rounded-lg p-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <i class="fa fa-file-csv text-green-600 text-xl"></i>
          <div>
            <p class="text-sm font-medium text-gray-800">{{ selectedFile.name }}</p>
            <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
          </div>
        </div>
        <BaseButton @click="clearFile" class="text-red-600 hover:text-red-700 cursor-pointer">
          <i class="fa fa-times"></i>
        </BaseButton>
      </div>

      <!-- Action buttons -->
      <div class="flex justify-end space-x-3 pt-4">
        <BaseButton @click="store.handleToggleModalBulkUpload" class="bg-gray-500 hover:bg-gray-600"
          >{{ t('shared.actions.cancel') }}</BaseButton
        >
        <BaseButton
          @click="handleUpload"
          :disabled="!selectedFile || isLoading"
          class="bg-blue-600 hover:bg-blue-700"
        >
          <span v-if="isLoading"> <i class="fa fa-spinner fa-spin mr-2"></i>{{ t('application.uploading') }} </span>
          <span v-else> <i class="fa fa-upload mr-2"></i>{{ t('application.upload') }} </span>
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import { useApplicationStore } from '../../store/applicationStore'
import { useApplicationMutations } from '../../queries/useApplicationMutations'
import formatFileSize from '@/shared/helpers/formatFileSize'
import { clearFileInput, handleCsvFileSelect } from '@/shared/helpers/file'
import { useCSVParser } from '@/shared/composables/useCSVParser'
import { downloadFile } from '@/shared/utils/download'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
defineProps({
  jobs: {
    type: Array,
    required: true,
  },
  agents: {
    type: Array,
    required: true,
  },
})

const store = useApplicationStore()
const selectedFile = ref(null)
const selectedJobId = ref(null)
const selectedAgentId = ref(null)
const onCsvChange = handleCsvFileSelect(selectedFile)

const clearFile = () => {
  clearFileInput(selectedFile, 'csv-upload')
}

const { jsonData, error, parseCSV } = useCSVParser()

const { bulkApplicationUpload, isLoading } = useApplicationMutations(store.moduleName, {
  onSuccess() {
    clearFile()
    store.handleToggleModalBulkUpload()
  },
  onError(error) {
    console.error('Upload error:', error)
  },
})

const handleUpload = async () => {
  if (!selectedFile.value) return

  const text = await selectedFile.value.text()

  parseCSV(text)

  if (error.value) {
    alert(`Failed to parse CSV: ${error.value}`)
    return
  }

  const formData = {
    job_id: selectedJobId.value,
    agent_id: selectedAgentId.value,
    applications: jsonData.value,
  }

  await bulkApplicationUpload.mutateAsync(formData)
}
</script>

<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Client Dropdown -->
    <div>
      <BaseLabel for="client_id">{{ t('shared.labels.client') }}</BaseLabel>
      <BaseSelect
        id="client_id"
        v-model="store.formData.client_id"
        :options="clients"
        placeholder="Select Client"
        :required="true"
      />
    </div>

    <!-- Candidates -->
    <div>
      <BaseLabel for="candidates">{{ t('shared.labels.candidates') }}</BaseLabel>
      <BaseInput
        id="candidates"
        type="number"
        v-model="store.formData.candidates"
        placeholder="Eg: 10"
      />
    </div>

    <!-- Assign To -->
    <div>
      <BaseLabel for="employee_id">{{ t('demand_letter.assign_to') }}</BaseLabel>
      <BaseSelect
        id="employee_id"
        v-model="store.formData.employee_id"
        :options="employees"
        placeholder="Select"
        :required="true"
      />
    </div>

    <!-- End Date -->
    <div>
      <BaseLabel for="end_date">{{ t('demand_letter.end_date') }}</BaseLabel>
      <BaseInput id="end_date" type="date" v-model="store.formData.end_date" />
    </div>

    <!-- Visa Issue Number -->
    <div>
      <BaseLabel for="visa_issue_number">{{ t('demand_letter.visa_issue_number') }}</BaseLabel>
      <BaseInput
        id="visa_issue_number"
        type="text"
        v-model="store.formData.visa_issue_number"
        placeholder="Enter visa issue number"
      />
    </div>

    <!-- Sponsor ID -->
    <div>
      <BaseLabel for="sponsor_id">{{ t('demand_letter.sponsor_id') }}</BaseLabel>
      <BaseInput
        id="sponsor_id"
        type="text"
        v-model="store.formData.sponsor_id"
        placeholder="Enter sponsor ID"
      />
    </div>
    <!-- Work Order File Upload Section -->
    <div class="col-span-full">
      <!-- IMAGE/PDF PREVIEW -->
      <div class="mt-2">
        <!-- New uploaded file preview -->
        <div class="overflow-hidden" v-if="store.formData?.work_order_preview">
          <BaseImagePreview
            v-if="store.fileType?.work_order_preview === 'image'"
            :src="store.formData?.work_order_preview"
            width="250px"
            height="200px"
            @cancelImage="store.cancelImage('work_order')"
            cancelKey="work_order"
          />
          <div
            v-else
            class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative"
          >
            <iframe :src="store.formData?.work_order_preview" class="w-full h-full"></iframe>
            <button
              class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
              @click="store.cancelImage('work_order')"
              type="button"
            >
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>

        <!-- Existing file from database -->
        <template v-else-if="store.item?.work_order_url">
          <div
            v-if="store.item.work_order_url.split('?')[0].toLowerCase().endsWith('.pdf')"
            class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50"
          >
            <iframe :src="store.item.work_order_url" class="w-full h-full"></iframe>
          </div>
          <BaseImagePreview
            v-else
            :src="store.item.work_order_url"
            width="320px"
            height="200px"
            cancelKey="work_order"
            @cancelImage="store.cancelImage('work_order')"
          />
        </template>
      </div>

      <!-- UPLOAD INPUT -->
      <div class="mt-4">
        <BaseLabel for="work_order_path">{{ t('demand_letter.upload_image') }}</BaseLabel>
        <BaseFileInput
          accept=".pdf, .jpg, .jpeg, .png"
          @change="store.handleFileChange($event, 'work_order_path', 'work_order_preview')"
          :fileName="store.fileName.work_order_path"
        />
        <div v-if="store.fileError.work_order_path" class="text-red-600 text-sm mt-1">
          {{ store.fileError.work_order_path }}
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="col-span-full flex flex-col sm:flex-row justify-end gap-2 py-4">
      <BaseButton
        class="bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto"
        type="button"
        @click="$emit('cancel')"
        >{{ t('shared.actions.cancel') }}</BaseButton
      >

      <BaseButton class="w-full sm:w-auto" type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.messages.saving') }}</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { useWorkOrderStore } from '@/modules/work_order/store/workOrderStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('work_order')
const store = useWorkOrderStore()

const props = defineProps({
  clients: { type: Array, required: true },
  employees: { type: Array, required: true },
  onSubmit: { type: Function, required: true },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['cancel'])

// Since we're using store.formData directly, we don't need to watch or emit updates
</script>

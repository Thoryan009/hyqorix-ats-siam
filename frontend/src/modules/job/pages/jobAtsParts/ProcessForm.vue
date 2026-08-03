<template>
  <div class="space-y-2 p-1" v-if="processData">
    <div class="flex items-center justify-between">
      <PageTitle>{{ title }}</PageTitle>
      <BaseButton
        v-if="!editMode"
        v-can="'ats.update'"
        class="bg-yellow-500 text-white hover:bg-yellow-600 text-xs px-3 py-1"
        @click="editMode = true"
      >
        <i class="fa fa-pencil mr-1"></i> {{t('shared.actions.edit')}}
      </BaseButton>
      <BaseButton
        v-else
        class="bg-gray-400 text-white hover:bg-gray-500 text-xs px-3 py-1"
        @click="cancelEdit"
      >{{t('shared.actions.cancel')}}</BaseButton>
    </div>

    <!-- Custom field slot -->
    <slot :processData="processData" :editMode="editMode"></slot>

    <!-- Process Status -->
    <div>
      <BaseLabel>{{t('ats.process_status')}}</BaseLabel>
      <BaseSelect
        v-if="editMode"
        v-model="processData.status"
        :options="statusOptions"
        class="w-full"
      />
      <p v-else class="text-black text-[15px] capitalize">{{ processData.status || 'pending' }}</p>
    </div>

    <!-- Remarks -->
    <div>
      <BaseLabel>{{t('ats.remarks')}}</BaseLabel>
      <BaseTextArea
        v-if="editMode"
        v-model="processData.remarks"
        :rows="2"
        :placeholder="t('ats.enter_remarks_here')"
      />
      <p v-else class="text-black text-[15px]">{{ processData.remarks || '—' }}</p>
    </div>

    <!-- created By -->
    <div>
      <BaseLabel>{{t('shared.labels.created_by')}}</BaseLabel>
      <p class="text-black text-[15px]">{{ processData.created_by || t('ats.not_started_yet') }}</p>
    </div>

    <!-- Updated By -->
    <div>
      <BaseLabel>{{t('shared.labels.updated_by')}}</BaseLabel>
      <p class="text-black text-[15px]">{{ processData.updated_by || t('ats.not_updated_yet') }}</p>
    </div>
    <div>
      <BaseLabel>{{t('shared.labels.completed_at')}}</BaseLabel>
      <p class="text-black text-[15px]">{{ processData.completed_at || t('ats.not_completed_yet') }}</p>
    </div>

    <!-- Actions (only in edit mode) -->
    <div v-if="editMode" class="grid grid-cols-2 md:grid-cols-3 gap-3 pt-4">
      <BaseButton @click="emit('updateProcess', processData)" v-can="'ats.update'">{{t('shared.actions.submit')}}</BaseButton>
      <BaseSelect
        v-can="'ats.update'"
        :disabled="selectedApplicant.current_process == 'rejected'"
        v-model="selectedNextProcessId"
        :options=" store.nextProcesses.filter(
            (process) => !selectedApplicant.active_process_ids.includes(process.id),
          )"
        :placeholder="t('ats.select_next_process')"
        class="w-full"
      />
      <BaseButton
        v-can="'ats.update'"
        v-if="selectedNextProcessId"
        @click="emit('nextProcess', { selectedNextProcessId, processData })"
        class="bg-green-600 text-white hover:bg-green-700"
      >{{t('ats.next_process')}}</BaseButton>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import { useAtsStore } from '../../store/atsStore'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
const props = defineProps({
  selectedApplicant: { type: Object, required: true },
  processId: { type: Number, required: true },
  title: { type: String, default: 'Process Form' },
})

const emit = defineEmits(['updateProcess', 'nextProcess'])

const store = useAtsStore()

const editMode = ref(false)
const selectedNextProcessId = ref('')

// snapshot to restore on cancel
let snapshot = {}

const cancelEdit = () => {
  processData.status = snapshot.status
  processData.remarks = snapshot.remarks
  processData.data = { ...snapshot.data }
  editMode.value = false
}

const statusOptions = [
  { name: 'Pending', id: 'pending' },
  { name: 'Draft', id: 'draft' },
  { name: 'Completed', id: 'completed' },
  { name: 'Rejected', id: 'rejected' },
  { name: 'Declined', id: 'declined' },
]

const processData = reactive({
  application_process_id: null,
  type: '',
  status: 'pending',
  remarks: '',
  created_by: '',
  updated_by: '',
  started_at: '',
  completed_at: '',
  data: {},
})

watch(
  () => props.selectedApplicant,
  () => {
    const p = props.selectedApplicant.processes?.find((x) => x.process_id == props.processId)

    if (!p) {
      return
    }

    processData.application_process_id = p.id
    processData.type = p.type
    processData.status = p.status || 'pending'
    processData.remarks = p.remarks || ''
    processData.created_by = p.created_by || ''
    processData.updated_by = p.updated_by || ''
    processData.started_at = p.started_at || ''
    processData.completed_at = p.completed_at || ''

    // 🔥 IMPORTANT: make data reactive
    processData.data = { ...p.data }

    // capture snapshot for cancel
    snapshot = { status: processData.status, remarks: processData.remarks, data: { ...p.data } }

    // reset edit mode on applicant change
    editMode.value = false
  },
  { immediate: true }
)
</script>

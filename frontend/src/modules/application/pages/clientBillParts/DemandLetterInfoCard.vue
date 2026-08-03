<template>
  <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
    <!-- Header -->
    <div class="flex items-center gap-3 border-b border-gray-300 pb-3 mb-4">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
        <i class="fa fa-user text-lg"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-800">Demand Letter Information</h3>
    </div>

  

    <!-- Info List -->
    <div class="space-y-3 text-base">
      <div class="flex items-start gap-3" v-for="(field, key) in workOrderFields" :key="key">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50">
          <i class="fa" :class="field.icon + ' text-gray-500 text-sm'"></i>
        </div>
        <div class="flex items-center">
          <span class="text-base font-medium text-gray-500 mr-1">{{ field.label }}:</span>
          <span class="font-medium text-gray-900">{{ workOrderInformation?.[key] || '' }}</span>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 mt-4">
      <BaseButton
       v-if="filters.transaction_status != 'invoice-generated'"
        class="bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 px-4 py-2 rounded-lg"
        @click="generateInvoice"
      >
        <i class="fa fa-file-text"></i>
        {{ isLoading ? 'Generating...' : 'Generate Invoice' }}
      </BaseButton>

      <BaseButton v-if="showCreateMailButton" class="bg-violet-600 text-white hover:bg-violet-700" @click="prepareMail">
        <i class="fa fa-paper-plane"></i>
        Create Email
      </BaseButton>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useClientBillStore } from '../../store/clientBillStore'
import { useRouter } from 'vue-router'
import { useAuthQuery } from '@/modules/auth/queries/useAuthQuery'
import { useSettingsQuery } from '@/modules/setting/queries/useSettingsQuery'
import { useClientBillMutations } from '../../queries/useClientBillMutation'

const props = defineProps({
  selectedRows: { type: Array, required: true },
  selectedIds: { type: Array, required: true },
  filters: { type: Object, required: true },
})

const emit = defineEmits(['reset'])

const handleReset = () => {
  emit('reset')
}

const store = useClientBillStore()
const router = useRouter()
const billNo = ref('')
const showCreateMailButton = ref(false)

const { data } = useAuthQuery()

const { data: settingApiData } = useSettingsQuery(1)

const { generateClientInvoice, isLoading } = useClientBillMutations(store.moduleName, {
  onSuccess(data) {
    const job_id = props.selectedRows?.[0]?.job_id
    const billNoValue = data?.data?.bill_no
    billNo.value = billNoValue

    const routeData = router.resolve({
      name: 'Print Client Invoice',
      params: { billNo: billNoValue, job_id: job_id },
    })
    showCreateMailButton.value = true
    window.open(routeData.href, '_blank')
    handleReset()
  },
  onError(error) {
    console.error('Error generating invoice:', error)
    alert('Failed to generate invoice. Please try again.')
  },
})

const settingsData = computed(() => settingApiData?.value?.data?.data || {})
const user = computed(() => data.value?.data ?? {})

// Combine labels and icons into one object
const workOrderFields = {
  client_name: { label: 'Client Name', icon: 'fa-id-card' },
  work_order_id: { label: 'Demand Letter', icon: 'fa-briefcase' },
  work_order_candidates: { label: 'Total Candidates', icon: 'fa-users' },
  work_order_end_date_formatted: { label: 'End Date', icon: 'fa-calendar' },
  work_order_price_usd: { label: 'Per Candidate Price USD', icon: 'fa-dollar' },
}

const selectedWorkOrder = ref(props.selectedRows?.[0] || null)
const selectedRows = computed(() => props.selectedRows || [])
const selectedIds = computed(() => props.selectedIds || [])

// Computed for selected work order information
const workOrderInformation = computed(() => {
  const row = selectedWorkOrder.value
  if (!row) return {}

  // Pick only the fields that exist in workOrderFields
  const info = {}
  for (const key in workOrderFields) {
    info[key] = row[key] ?? ''
  }

  // Add any extra fields if needed
  info.client_id = row.client_id
  info.client_email = row.client_email
  info.client_phone = row.client_phone
  info.client_country = row.client_country
  info.selectedCandidates = props.selectedRows.length

  return info
})

function prepareMail() {
  if (!workOrderInformation.value) return

  store.handleToggleEmailForm()
  const mailSubject = generateMailSubject()
  const mailBody = generateMailBody()
  store.formData.bill_no = billNo.value || props.filters.bill_no || ''
  store.formData.client_id = workOrderInformation.value.client_id
  store.formData.to_mail = workOrderInformation.value.client_email || ''
  store.formData.subject = mailSubject
  store.formData.body = mailBody
}

const generateMailSubject = () => {
  if (!workOrderInformation.value) return 'Invoice Details'

  const { work_order_id, client_name, selectedCandidates, work_order_price_usd } =
    workOrderInformation.value

  const total = work_order_price_usd * selectedCandidates

  // add bill no in in the beginning of the subject if available
  return `${billNo.value ? `Invoice ${billNo.value} | ` : ''}Demand Letter ${work_order_id} | ${client_name} | ${selectedCandidates} Candidates | USD $${total}`
}
const generateMailBody = () => {
  if (!workOrderInformation.value) return 'Please find the invoice details below.'
  const { client_name, work_order_id, selectedCandidates, work_order_price_usd } =
    workOrderInformation.value
  return `Dear ${client_name},

Please find the details of your invoice below:

- Bill No: ${billNo.value || props.filters?.bill_no || 'N/A'}
- Demand Letter: ${work_order_id}
- Total Candidates: ${selectedCandidates}
- Per Candidate Price: $${work_order_price_usd}
- Total Amount: $${selectedCandidates * work_order_price_usd}

Thank you for your business. Please let us know if you have any questions.

Best regards,
${user.value.name || 'Your Recruitment Manager'}
${user.value.type ? `${user.value.type}` : ''}
${user.value.email ? `${user.value.email}` : ''}
${settingsData.value.company_name ? `${settingsData.value.company_name}` : ''}
`
}

const generateInvoice = async () => {
  if (!selectedRows.value.length) return
  selectedWorkOrder.value = selectedRows.value[0]

  const payload = {
    ids: selectedIds.value,
    client_id: workOrderInformation.value.client_id,
  }

  await generateClientInvoice.mutateAsync(payload)
}

watch(
  () => props.filters,
  (newFilters) => {
    if (newFilters?.transaction_status === 'invoice-generated') {
      prepareMail()
    }
  },
  { deep: true, immediate: true }
)
</script>

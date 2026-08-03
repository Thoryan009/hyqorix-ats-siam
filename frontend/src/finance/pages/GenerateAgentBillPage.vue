<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <div class="mb-1 flex items-center gap-2 text-sm text-gray-500">
          <router-link to="/finance/agent-bills" class="hover:text-green-700">Bill List</router-link>
          <span>/</span>
          <router-link
            v-if="route.query.from === 'agent-accounts'"
            to="/finance/accounts?tab=agent-accounts"
            class="hover:text-green-700"
          >Agent Accounts</router-link>
          <template v-if="route.query.from === 'agent-accounts'">
            <span>/</span>
          </template>
          <span class="text-gray-700">Generate Bill (Job Wise)</span>
        </div>
        <PageTitle>Generate Bill (Job Wise)</PageTitle>
      </div>
    </PageHeader>

    <div class="space-y-4">
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <BaseLabel for="agent">Agent</BaseLabel>
            <BaseSelect
              id="agent"
              v-model="form.agent_id"
              :options="agentOptions"
              placeholder="Select Agent"
            />
          </div>

          <div class="space-y-2">
            <BaseLabel for="job">Job</BaseLabel>
            <BaseSelect
              id="job"
              v-model="form.job_id"
              :options="jobOptions"
              placeholder="Select Job"
              :disabled="!form.agent_id"
            />
          </div>

          <div class="space-y-2">
            <BaseLabel for="bill_date">Bill Date</BaseLabel>
            <BaseInput id="bill_date" v-model="form.bill_date" type="date" />
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <BaseButton
            class="bg-green-600 text-white hover:bg-green-700"
            :disabled="!form.agent_id || !form.job_id"
            @click="loadCandidates"
          >
            <i class="fa fa-users mr-1"></i> Load Job Candidates
          </BaseButton>
        </div>
      </div>

      <JobInfoCard v-if="loadedJob" :job="loadedJob" />

      <CandidateBillingTable
        v-if="loadedCandidates.length"
        :candidates="loadedCandidates"
        :selected-ids="selectedIds"
        :total-amount="selectedTotal"
        @toggle-all="toggleAll"
        @toggle-row="toggleRow"
        @update-cost="updateCandidateCost"
        @update-sale-price="updateCandidateSalePrice"
      />

      <div
        v-if="loadedCandidates.length"
        class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800"
      >
        <i class="fa fa-info-circle mr-1"></i>
        Note: The amount will be deducted from the agent's wallet when the bill is generated.
      </div>

      <div v-if="loadedCandidates.length" class="flex flex-wrap gap-3">
        <BaseButton
          class="bg-blue-600 text-white hover:bg-blue-700"
          :disabled="!selectedIds.length"
          @click="showBillPreviewModal = true"
        >
          <i class="fa fa-eye mr-1"></i> Bill Preview
        </BaseButton>
        <BaseButton
          class="bg-green-600 text-white hover:bg-green-700"
          :disabled="!selectedIds.length"
          @click="generateBill"
        >
          <i class="fa fa-file-text-o mr-1"></i> Generate Bill
        </BaseButton>
        <BaseButton class="bg-gray-100 text-gray-800 hover:bg-gray-200" @click="resetForm">
          <i class="fa fa-refresh mr-1"></i> Reset
        </BaseButton>
        <router-link to="/finance/agent-bills">
          <BaseButton
            :className="'bg-white text-gray-800 ring-1 ring-gray-300 hover:bg-gray-50 cursor-pointer'"
          >
            <i class="fa fa-arrow-left mr-1"></i> Back to List
          </BaseButton>
        </router-link>
      </div>
    </div>

    <BillPreviewModal
      :is-visible="showBillPreviewModal"
      :agent="selectedAgent"
      :job="loadedJob"
      :selected-candidates="selectedCandidates"
      :total-amount="selectedTotal"
      :bill-no="previewBillNo"
      :bill-date="form.bill_date"
      @close="showBillPreviewModal = false"
    />
  </SectionHeader>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import JobInfoCard from './components/agentBillParts/JobInfoCard.vue'
import CandidateBillingTable from './components/agentBillParts/CandidateBillingTable.vue'
import BillPreviewModal from './components/agentBillParts/BillPreviewModal.vue'
import {
  formatJobOption,
  getJobById,
  getJobsByAgent,
} from '@/finance/data/agentBillData'
import { getBillableAgents } from '@/finance/data/agentAccountData'
import { useAgentBillStore } from '@/finance/store/agentBillStore'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import {
  generateBillNo,
  getCandidateSalePrice,
  syncCandidateTotals,
} from '@/finance/utils/billUtils'
import Swal from 'sweetalert2'

const router = useRouter()
const route = useRoute()
const billStore = useAgentBillStore()
const accountStore = useAgentAccountStore()

const today = new Date().toISOString().slice(0, 10)

const form = ref({
  agent_id: '',
  job_id: '',
  currency: 'BDT',
  bill_date: today,
})

const loadedJob = ref(null)
const loadedCandidates = ref([])
const selectedIds = ref([])
const previewBillNo = ref('')
const showBillPreviewModal = ref(false)

const agentOptions = computed(() =>
  getBillableAgents(accountStore.accounts).map((agent) => ({
    id: agent.id,
    name: `${agent.agent_code} - ${agent.agent_name}`,
  }))
)

const selectedAgent = computed(() => {
  if (!form.value.agent_id) return null

  const account = accountStore.accounts.find(
    (item) => item.bill_agent_id === Number(form.value.agent_id)
  )

  if (!account) return null

  return {
    id: account.bill_agent_id,
    agent_code: account.agent_code,
    agent_name: account.agent_name,
    phone: account.phone,
  }
})

const jobOptions = computed(() => {
  if (!form.value.agent_id) return []
  return getJobsByAgent(form.value.agent_id).map(formatJobOption)
})

const selectedCandidates = computed(() =>
  loadedCandidates.value.filter((candidate) => selectedIds.value.includes(candidate.id))
)

const selectedTotal = computed(() =>
  selectedCandidates.value.reduce((sum, candidate) => sum + getCandidateSalePrice(candidate), 0)
)

watch(
  () => form.value.agent_id,
  () => {
    form.value.job_id = ''
    clearLoadedData()
  }
)

watch(
  () => form.value.job_id,
  () => {
    clearLoadedData()
  }
)

watch(
  () => form.value.bill_date,
  () => {
    previewBillNo.value = generateBillNo(
      form.value.bill_date,
      billStore.bills.map((bill) => bill.bill_no)
    )
  },
  { immediate: true }
)

onMounted(() => {
  const agentId = route.query.agent_id
  if (agentId && accountStore.accounts.some((item) => item.bill_agent_id === Number(agentId))) {
    form.value.agent_id = String(agentId)
  }
})

function clearLoadedData() {
  loadedJob.value = null
  loadedCandidates.value = []
  selectedIds.value = []
  showBillPreviewModal.value = false
}

function loadCandidates() {
  const job = getJobById(form.value.job_id)
  if (!job) return

  loadedJob.value = job
  loadedCandidates.value = job.candidates.map((candidate) => ({
    ...candidate,
    costs: { ...candidate.costs },
  }))
  selectedIds.value = job.candidates.map((candidate) => candidate.id)
}

function updateCandidateCost({ candidateId, fieldKey, value }) {
  const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
  if (!candidate) return

  if (!candidate.costs) candidate.costs = {}
  candidate.costs[fieldKey] = value
  syncCandidateTotals(candidate)
}

function updateCandidateSalePrice({ candidateId, value }) {
  const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
  if (!candidate) return

  candidate.sale_price = value
  candidate.charge = value
  syncCandidateTotals(candidate)
}

function toggleRow(id) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((item) => item !== id)
  } else {
    selectedIds.value.push(id)
  }
}

function toggleAll() {
  if (selectedIds.value.length === loadedCandidates.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = loadedCandidates.value.map((candidate) => candidate.id)
  }
}

function resetForm() {
  form.value = {
    agent_id: '',
    job_id: '',
    currency: 'BDT',
    bill_date: today,
  }
  clearLoadedData()
}

async function generateBill() {
  if (!selectedAgent.value || !loadedJob.value || !selectedCandidates.value.length) return

  const linkedAccount = accountStore.getAccountByBillAgentId(selectedAgent.value.id)
  if (linkedAccount && linkedAccount.balance < selectedTotal.value) {
    await Swal.fire({
      title: 'Insufficient Balance',
      text: 'Agent wallet balance is not enough to generate this bill.',
      icon: 'error',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  const billNo = generateBillNo(
    form.value.bill_date,
    billStore.bills.map((bill) => bill.bill_no)
  )

  billStore.addBill({
    bill_no: billNo,
    bill_date: form.value.bill_date,
    agent_code: selectedAgent.value.agent_code,
    agent_name: selectedAgent.value.agent_name,
    job_code: loadedJob.value.job_code,
    job_title: loadedJob.value.job_title,
    candidate_count: selectedCandidates.value.length,
    total_amount: selectedTotal.value,
    currency: form.value.currency,
    status: 'Generated',
    candidates: selectedCandidates.value.map((candidate) => ({
      ...candidate,
      costs: { ...candidate.costs },
    })),
  })

  accountStore.recordBillGeneration(selectedAgent.value.id, {
    billNo,
    amount: selectedTotal.value,
    job: loadedJob.value.job_title,
    clientName: loadedJob.value.client,
    demandLetter: loadedJob.value.demand_letter,
    candidateCount: selectedCandidates.value.length,
    date: form.value.bill_date,
  })

  await Swal.fire({
    title: 'Bill Generated',
    text: `Bill ${billNo} has been generated successfully.`,
    icon: 'success',
    confirmButtonColor: '#22C55E',
  })

  router.push('/finance/agent-bills')
}
</script>

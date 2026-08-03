<template>
  <BaseModal
    :isVisible="isVisible"
    title="Account Deposit / Withdraw"
    className="xl:max-w-3xl"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="mb-6 flex gap-2 rounded-xl bg-gray-50 p-1">
        <button
          v-for="option in typeOptions"
          :key="option.id"
          type="button"
          class="flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition"
          :class="
            form.type === option.id
              ? option.id === 'deposit'
                ? 'bg-green-600 text-white shadow-sm'
                : 'bg-amber-600 text-white shadow-sm'
              : 'text-gray-600 hover:bg-white hover:text-gray-900'
          "
          @click="form.type = option.id"
        >
          <i :class="option.icon"></i>
          {{ option.label }}
        </button>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <BaseLabel for="dw_from_account_id">From Account</BaseLabel>
          <BaseSelect
            id="dw_from_account_id"
            v-model="form.fromAccountId"
            :options="fromAccountOptions"
            :placeholder="form.type === 'deposit' ? 'Select cash account' : 'Select bank account'"
            :required="true"
          />
        </div>

        <div class="space-y-2">
          <BaseLabel for="dw_to_account_id">To Account</BaseLabel>
          <BaseSelect
            id="dw_to_account_id"
            v-model="form.toAccountId"
            :options="toAccountOptions"
            :placeholder="form.type === 'deposit' ? 'Select bank account' : 'Select cash account'"
            :required="true"
          />
        </div>

        <div class="space-y-2">
          <BaseLabel for="dw_date">Date</BaseLabel>
          <BaseInput id="dw_date" v-model="form.date" type="date" :required="true" />
        </div>

        <div class="space-y-2">
          <BaseLabel for="dw_amount">Amount (৳)</BaseLabel>
          <BaseInput
            id="dw_amount"
            v-model="form.amount"
            type="number"
            min="1"
            step="1"
            placeholder="Enter amount"
            :required="true"
          />
        </div>

        <div class="space-y-2">
          <BaseLabel for="dw_particular">Particular</BaseLabel>
          <BaseInput
            id="dw_particular"
            v-model="form.particular"
            :placeholder="
              form.type === 'deposit' ? 'Eg: Cash deposit to bank' : 'Eg: Bank withdrawal to cash'
            "
          />
        </div>

        <div class="space-y-2">
          <BaseLabel for="dw_reference">Reference No (Optional)</BaseLabel>
          <BaseInput id="dw_reference" v-model="form.referenceNo" placeholder="Eg: RC-001/26" />
        </div>

        <div class="space-y-2 md:col-span-2">
          <BaseLabel for="dw_remarks">Remarks (Optional)</BaseLabel>
          <textarea
            id="dw_remarks"
            v-model="form.remarks"
            rows="3"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
            placeholder="Additional notes"
          ></textarea>
        </div>

        <div
          v-if="form.type === 'withdraw'"
          class="md:col-span-2 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800"
        >
          <i class="fa fa-info-circle mr-1.5"></i>
          Withdraw will reduce the selected bank account balance and add to the cash account.
        </div>

        <div
          v-else
          class="md:col-span-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800"
        >
          <i class="fa fa-info-circle mr-1.5"></i>
          Deposit will reduce the cash account balance and add to the selected bank account.
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 md:col-span-2">
          <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="closeModal">
            Cancel
          </BaseButton>
          <BaseButton type="submit" :disabled="loading || !isFormValid">
            <span v-if="loading">Processing...</span>
            <span v-else>{{ submitLabel }}</span>
          </BaseButton>
        </div>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { useAccountStore } from '@/finance/store/accountStore'
import { formatCurrency } from '@/finance/utils/billUtils'
import Swal from 'sweetalert2'

const props = defineProps({
  isVisible: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const store = useAccountStore()
const loading = ref(false)

const typeOptions = [
  { id: 'deposit', label: 'Deposit', icon: 'fa fa-arrow-down' },
  { id: 'withdraw', label: 'Withdraw', icon: 'fa fa-arrow-up' },
]

const defaultForm = () => ({
  type: 'deposit',
  fromAccountId: '',
  toAccountId: '',
  amount: '',
  particular: '',
  referenceNo: '',
  remarks: '',
  date: new Date().toISOString().slice(0, 10),
})

const form = ref(defaultForm())

const mapAccountOptions = (accounts) =>
  accounts.map((account) => ({
    id: account.id,
    name: `${account.account_name} - ${account.account_label} (${formatCurrency(account.current_balance)})`,
  }))

const cashAccountOptions = computed(() =>
  mapAccountOptions(store.getActiveAccounts().filter((account) => account.account_type === 'Cash'))
)

const bankAccountOptions = computed(() =>
  mapAccountOptions(store.getActiveAccounts().filter((account) => account.account_type === 'Bank'))
)

const fromAccountOptions = computed(() =>
  form.value.type === 'deposit' ? cashAccountOptions.value : bankAccountOptions.value
)

const toAccountOptions = computed(() => {
  const options =
    form.value.type === 'deposit' ? bankAccountOptions.value : cashAccountOptions.value

  return options.filter((option) => option.id !== Number(form.value.fromAccountId))
})

const submitLabel = computed(() => (form.value.type === 'deposit' ? 'Deposit' : 'Withdraw'))

const isFormValid = computed(() =>
  Boolean(
    form.value.fromAccountId &&
      form.value.toAccountId &&
      form.value.date &&
      Number(form.value.amount) > 0 &&
      Number(form.value.fromAccountId) !== Number(form.value.toAccountId)
  )
)

watch(
  () => props.isVisible,
  async (visible) => {
    if (visible) {
      form.value = defaultForm()
      await store.fetchActiveAccounts()
    }
  }
)

watch(
  () => form.value.type,
  () => {
    form.value.fromAccountId = ''
    form.value.toAccountId = ''
  }
)

watch(
  () => form.value.fromAccountId,
  (fromAccountId) => {
    if (fromAccountId && Number(form.value.toAccountId) === Number(fromAccountId)) {
      form.value.toAccountId = ''
    }
  }
)

const closeModal = () => {
  emit('close')
  form.value = defaultForm()
}

const handleSubmit = async () => {
  loading.value = true

  const result =
    form.value.type === 'deposit'
      ? await store.depositToAccount({ ...form.value })
      : await store.withdrawFromAccount({ ...form.value })

  loading.value = false

  if (!result.ok) {
    await Swal.fire({
      icon: 'error',
      title: 'Transaction Failed',
      text: result.message,
      confirmButtonColor: '#22C55E',
    })
    return
  }

  await Swal.fire({
    icon: 'success',
    title: 'Success',
    text:
      form.value.type === 'deposit'
        ? 'Deposit completed successfully.'
        : 'Withdraw completed successfully.',
    confirmButtonColor: '#22C55E',
  })

  closeModal()
}
</script>

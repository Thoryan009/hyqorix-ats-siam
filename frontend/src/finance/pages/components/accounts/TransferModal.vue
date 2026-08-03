<template>
  <BaseModal
    :isVisible="isVisible"
    title="Account Transfer"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel for="from_account_id">From Account</BaseLabel>
        <BaseSelect
          id="from_account_id"
          v-model="form.fromAccountId"
          :options="accountOptions"
          placeholder="Select source bank account"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="to_account_id">To Account</BaseLabel>
        <BaseSelect
          id="to_account_id"
          v-model="form.toAccountId"
          :options="destinationOptions"
          placeholder="Select destination bank account"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="transfer_date">Date</BaseLabel>
        <BaseInput id="transfer_date" v-model="form.date" type="date" :required="true" />
      </div>

      <div class="space-y-2">
        <BaseLabel for="transfer_amount">Amount (৳)</BaseLabel>
        <BaseInput
          id="transfer_amount"
          v-model="form.amount"
          type="number"
          min="1"
          step="1"
          placeholder="Enter transfer amount"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="transfer_particular">Particular</BaseLabel>
        <BaseInput
          id="transfer_particular"
          v-model="form.particular"
          placeholder="Eg: Bank to bank transfer"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="transfer_reference">Reference No (Optional)</BaseLabel>
        <BaseInput
          id="transfer_reference"
          v-model="form.referenceNo"
          placeholder="Eg: TR-001/26"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="transfer_remarks">Remarks (Optional)</BaseLabel>
        <textarea
          id="transfer_remarks"
          v-model="form.remarks"
          rows="3"
          class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          placeholder="Additional notes"
        ></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="closeModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading || !isFormValid">
          <span v-if="loading">Transferring...</span>
          <span v-else>Transfer</span>
        </BaseButton>
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

const defaultForm = () => ({
  fromAccountId: '',
  toAccountId: '',
  amount: '',
  particular: '',
  referenceNo: '',
  remarks: '',
  date: new Date().toISOString().slice(0, 10),
})

const form = ref(defaultForm())

const accountOptions = computed(() =>
  store
    .getActiveAccounts()
    .filter((account) => account.account_type === 'Bank')
    .map((account) => ({
      id: account.id,
      name: `${account.account_name} - ${account.account_label} (${formatCurrency(account.current_balance)})`,
    }))
)

const destinationOptions = computed(() =>
  accountOptions.value.filter((option) => option.id !== Number(form.value.fromAccountId))
)

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

  const result = await store.transferBetweenAccounts({ ...form.value })

  loading.value = false

  if (!result.ok) {
    await Swal.fire({
      icon: 'error',
      title: 'Transfer Failed',
      text: result.message,
      confirmButtonColor: '#22C55E',
    })
    return
  }

  await Swal.fire({
    icon: 'success',
    title: 'Transfer Completed',
    text: 'Amount has been transferred successfully.',
    confirmButtonColor: '#22C55E',
  })

  closeModal()
}
</script>

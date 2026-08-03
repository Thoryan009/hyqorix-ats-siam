<template>
  <div class="space-y-3">
    <div class="mb-2 border-t border-gray-200"></div>

    <!-- Application Price -->
    <div
      class="rounded-lg border border-gray-200 bg-linear-to-r from-blue-50 to-indigo-50 p-2 shadow-sm"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="rounded-full bg-blue-600 p-2">
            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"
              />
            </svg>
          </div>
          <span class="text-sm font-semibold text-gray-700">Application Price</span>
        </div>
        <span class="text-2xl font-bold text-blue-700"> ৳{{ Math.floor(applicationPrice) }} </span>
      </div>
    </div>

    <!-- Discount Amount -->
    <div
      class="rounded-lg border border-gray-200 bg-linear-to-r from-green-50 to-green-100 p-2 shadow-sm"
      v-if="selectedItem?.discount_amount"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="rounded-full bg-green-600 p-2">
            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
              />
            </svg>
          </div>
          <span class="text-sm font-semibold text-gray-700">Discount Amount</span>
        </div>
        <span class="text-2xl font-bold text-green-700">
          ৳{{ selectedItem?.discount_amount }}
        </span>
      </div>
    </div>

    <!-- Current Due -->
    <div
      class="rounded-lg border border-gray-200 bg-linear-to-r from-amber-50 to-orange-50 p-2 shadow-sm"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="rounded-full bg-amber-600 p-2">
            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <span class="text-sm font-semibold text-gray-700">Pending Due</span>
        </div>
        <span class="text-2xl font-bold text-amber-700"> ৳{{ currentDue }} </span>
      </div>
    </div>

    <div v-if="currentDue > 0">
      <!-- Payment Method -->
      <PaymentMethod v-model="transaction.payment_method" :disabled="!selectedItem" />

      <!-- Date & Time -->
      <div class="grid grid-cols-2 gap-3 my-1">
        <div>
          <BaseLabel className="text-[13px] font-medium">Payment Date</BaseLabel>
          <BaseInput type="date" v-model="transaction.payment_date" :disabled="!selectedItem" />
        </div>
        <div>
          <BaseLabel className="text-[13px] font-medium">Payment Time</BaseLabel>
          <BaseInput type="time" v-model="transaction.payment_time" :disabled="!selectedItem" />
        </div>
      </div>

      <!-- Discount -->
      <div class="flex py-2 gap-2 items-center" v-if="!selectedItem?.discount_amount">
        <BaseLabel
          className="text-[14px] font-medium cursor-pointer mb-0!"
          :class="addDiscount ? 'text-red-500' : 'text-green-700'"
          @click="handleDiscountClick"
          v-if="selectedItem"
        >
          {{ addDiscount ? '-' : '+' }} {{ addDiscount ? 'Remove' : 'Add' }} Discount
        </BaseLabel>
      </div>

      <div v-if="addDiscount && !selectedItem?.discount_amount">
        <BaseLabel className="text-[14px] font-medium text-black">
          Discount Amount (in taka)
        </BaseLabel>
        <BaseInput
          type="number"
          v-model="transaction.discount_amount"
          :disabled="!selectedItem"
          step="1"
          placeholder="0"
        />
        <p v-if="isDiscountInvalid" class="text-sm text-red-600 mt-1">
          Discount cannot be greater than application price.
        </p>
      </div>

      <!-- Paid Amount -->
      <div>
        <BaseLabel className="text-[14px] font-medium"> Paid Amount (in taka) </BaseLabel>
        <BaseInput
          type="number"
          v-model="transaction.paid_amount"
          :disabled="!selectedItem"
          step="1"
          placeholder="0"
        />
        <p v-if="isPaidAmountInvalid" class="text-sm text-red-600 mt-1">
          Paid amount cannot be greater than total payable amount.
        </p>
      </div>

      <!-- Due -->
      <div class="flex items-center justify-between rounded-lg bg-gray-50 p-1 md:p-2 px-3 my-1">
        <span class="text-lg font-semibold text-gray-700">Due:</span>
        <span
          class="text-2xl font-bold"
          :class="dueAmount >= 0 ? 'text-red-600' : 'text-green-600'"
        >
          ৳{{ Math.abs(dueAmount) }}
          <span v-if="dueAmount < 0" class="text-sm">(Change)</span>
        </span>
      </div>

      <!-- Remarks -->
      <div>
        <BaseLabel className="text-[13px] font-medium">Remarks</BaseLabel>
        <BaseTextArea
          v-model="transaction.remarks"
          :disabled="!selectedItem"
          :rows="2"
          placeholder="Add any remarks here..."
        />
      </div>

      <!-- Buttons -->
      <div class="grid grid-cols-2 gap-3">
        <BaseButton
          @click="handleSubmit('draft')"
          :disabled="isActionDisabled"
          class="disabled:opacity-50 disabled:cursor-not-allowed"
          :className="'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'"
        >
          Draft
        </BaseButton>

        <BaseButton
          @click="handleSubmit"
          :disabled="isActionDisabled"
          class="disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Proceed
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import PaymentMethod from './PaymentMethod.vue'
import { useTransactionMutations } from '@/modules/application/queries/useTransactionMutation'
import { useTransactionStore } from '@/modules/application/store/transactionStore'

const props = defineProps({
  selectedItem: Object,
})

const emit = defineEmits(['clear'])

const store = useTransactionStore()

const addDiscount = ref(false)

const now = new Date()

const handleDiscountClick = () => {
  addDiscount.value = !addDiscount.value
  transaction.value.discount_amount = '' // example second logic
}

const transaction = ref({
  application_id: null,
  payment_method: 'cash',
  payment_date: now.toISOString().slice(0, 10), // YYYY-MM-DD
  payment_time: now.toTimeString().slice(0, 5), // HH:mm
  discount_amount: '',
  total_amount: '',
  paid_amount: '',
  status: '',
  remarks: 'Received payment for Recruitment Service.',
})

/* ---------------- COMPUTED VALUES ---------------- */

const applicationPrice = computed(() => props.selectedItem?.application_price || 0)

const currentDue = computed(() => props.selectedItem?.transaction_history?.[0]?.current_due || 0)

const discountAmount = computed(() => Number(transaction.value.discount_amount || 0))

const paidAmount = computed(() => Number(transaction.value.paid_amount || 0))

const totalPayable = computed(() => Math.max(applicationPrice.value - discountAmount.value, 0))

const dueAmount = computed(() => currentDue.value - discountAmount.value - paidAmount.value)

/* ---------------- VALIDATION ---------------- */

const isDiscountInvalid = computed(() => discountAmount.value > applicationPrice.value)

const isPaidAmountInvalid = computed(() => paidAmount.value > totalPayable.value)

console.log(isDiscountInvalid.value, isPaidAmountInvalid.value, !props.selectedItem)

const isActionDisabled = computed(
  () =>
    !props.selectedItem ||
    isDiscountInvalid.value ||
    isPaidAmountInvalid.value ||
    paidAmount.value <= 0 ||
    totalPayable.value <= 0,
)

/* ---------------- SUBMIT ---------------- */

const { submit } = useTransactionMutations(store.moduleName, {
  onSuccess(data) {
    transaction.value = {
      application_id: null,
      payment_method: 'bank',
      payment_date: now.toISOString().slice(0, 10), // YYYY-MM-DD
      payment_time: now.toTimeString().slice(0, 5), // HH:mm
      discount_amount: '',
      total_amount: '',
      paid_amount: '',
      status: '',
      remarks: 'Received payment for Recruitment Service.',
    }
    emit('clear')
    store.receiptData.value = data.data
    store.handleToggleModal('add')
    console.log('Transaction successful:', data)
  },
})

const handleSubmit = async (status = '') => {
  if (
    status === 'draft' &&
    isDiscountInvalid.value === false &&
    isPaidAmountInvalid.value === false
  ) {
    transaction.value.status = status
    await submit.mutateAsync(transaction.value)
  } else if (isActionDisabled.value) {
    return
  }
  transaction.value.paid_amount = Number(transaction.value.paid_amount || 0)
  transaction.value.discount_amount = Number(transaction.value.discount_amount || 0)
  await submit.mutateAsync(transaction.value)
}

/* ---------------- WATCH ---------------- */

watch(
  () => props.selectedItem,
  (newItem) => {
    transaction.value.application_id = newItem?.id ?? null
    transaction.value.total_amount = newItem?.application_price || 0
  },
  { immediate: true },
)
</script>

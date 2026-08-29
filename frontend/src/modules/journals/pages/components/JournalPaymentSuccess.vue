<template>
  <JournalFlowSuccess
    :kicker="t('journals.payment_success_kicker')"
    :title="t('journals.payment_success_title')"
    :message="messageLabel"
    :summary-items="summaryItems"
    :primary-label="t('journals.payment_success_view_journals')"
    :secondary-label="t('journals.payment_success_pay_another')"
    @primary="emit('view-journals')"
    @secondary="emit('pay-another')"
  >
    <template v-if="success.managerComment" #extra>
      <div class="rounded-xl border border-primary/10 bg-primary-light/40 px-4 py-3">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
          {{ t('journals.manager_comment') }}
        </p>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-700">
          {{ success.managerComment }}
        </p>
      </div>
    </template>
  </JournalFlowSuccess>
</template>

<script setup>
import { computed } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import JournalFlowSuccess from './JournalFlowSuccess.vue'

const props = defineProps({
  success: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['view-journals', 'pay-another'])

const { t } = useTranslate()

const messageLabel = computed(() =>
  t('journals.payment_success_message', {
    voucher: props.success?.voucherNo || '—',
  }),
)

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return '—'
  return Number(value).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const summaryItems = computed(() => [
  {
    label: t('journals.voucher_no'),
    value: props.success?.voucherNo || '—',
    className: '',
  },
  {
    label: t('journals.voucher_date'),
    value: props.success?.voucherDate || '—',
    className: '',
  },
  {
    label: t('journals.total_debit'),
    value: formatAmount(props.success?.totalDebit),
    className: 'tabular-nums',
  },
  {
    label: t('journals.status'),
    value: props.success?.status || '—',
    className: 'text-primary',
  },
])
</script>

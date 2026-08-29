<template>
  <JournalFlowSuccess
    :kicker="kickerLabel"
    :title="titleLabel"
    :message="messageLabel"
    :summary-items="summaryItems"
    :primary-label="primaryLabel"
    :secondary-label="t('journals.success_create_new')"
    @primary="emit('primary')"
    @secondary="emit('secondary')"
  />
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

const emit = defineEmits(['primary', 'secondary'])

const { t } = useTranslate()

const successType = computed(() => props.success?.type || 'submitted')

const showApprovalAction = computed(() =>
  ['submitted', 'resubmitted'].includes(successType.value),
)

const kickerLabel = computed(() => {
  if (successType.value === 'resubmitted') return t('journals.success_kicker_resubmitted')
  if (successType.value === 'posted') return t('journals.success_kicker_posted')
  return t('journals.success_kicker_submitted')
})

const titleLabel = computed(() => {
  if (successType.value === 'resubmitted') return t('journals.success_title_resubmitted')
  if (successType.value === 'posted') return t('journals.success_title_posted')
  return t('journals.success_title_submitted')
})

const messageLabel = computed(() => {
  const voucher = props.success?.voucherNo || '—'
  if (successType.value === 'resubmitted') {
    return t('journals.success_message_resubmitted', { voucher })
  }
  if (successType.value === 'posted') {
    return t('journals.success_message_posted', { voucher })
  }
  return t('journals.success_message_submitted', { voucher })
})

const primaryLabel = computed(() =>
  showApprovalAction.value
    ? t('journals.success_go_to_approval')
    : t('journals.success_view_journals'),
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

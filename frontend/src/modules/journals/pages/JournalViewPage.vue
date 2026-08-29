<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('journals.view_title') }}</PageTitle>
        <p v-if="journal?.voucher_no" class="mt-1 text-sm text-gray-500">
          {{ journal.voucher_no }}
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <BaseButton
          className="border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
          @click="goBack"
        >
          {{ t('journals.back_to_list') }}
        </BaseButton>
        <BaseButton
          v-if="journal?.can_reverse"
          className="bg-rose-600 text-white hover:bg-rose-700"
          :disabled="reverseLoading"
          @click="handleReverse"
        >
          {{ reverseLoading ? t('journals.reversing') : t('journals.reverse_journal') }}
        </BaseButton>
      </div>
    </PageHeader>

    <div v-if="isLoading" class="px-4 py-10 text-center text-sm text-slate-500">
      {{ t('journals.loading_journal') }}
    </div>

    <div v-else-if="isError" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-6 text-sm text-rose-700">
      {{ t('journals.error_loading_journal') }}
    </div>

    <JournalApprovalPreview v-else-if="journal" :journal="journal" />
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { showConfirmDialog } from '@/shared/utils/sweetAlertUtils'
import { toast } from '@/shared/config/toastConfig'
import JournalApprovalPreview from './components/JournalApprovalPreview.vue'
import { useJournalQuery } from '../queries/useJournalQuery'
import { useJournalMutations } from '../queries/useJournalMutations'

const route = useRoute()
const router = useRouter()
const { t } = useTranslate()

const journalId = computed(() => route.params.id)
const { data: journal, isLoading, isError, refetch } = useJournalQuery(journalId)

const { reverse, reverseLoading } = useJournalMutations({
  onReverseSuccess: (response) => {
    const voucher = response?.data?.voucher_no || response?.voucher_no || journal.value?.voucher_no
    toast.success(t('journals.reversed_success', { voucher }))
    refetch()
  },
})

const goBack = () => {
  router.push({ name: 'Journal Management' })
}

const handleReverse = async () => {
  const voucher = journal.value?.voucher_no || ''
  const result = await showConfirmDialog({
    title: t('journals.reverse_confirm_title'),
    text: t('journals.reverse_confirm_text', { voucher }),
    icon: 'warning',
    confirmButtonText: t('journals.reverse_journal'),
    cancelButtonText: t('journals.close'),
    confirmButtonColor: '#e11d48',
  })

  if (result.isConfirmed) {
    await reverse.mutateAsync(journalId.value)
  }
}
</script>

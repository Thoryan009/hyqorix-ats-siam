<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('journals.management') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('journals.static_note') }}</p>
      </div>
    </PageHeader>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
        <h2 class="text-base font-semibold text-slate-800 sm:text-lg">
          {{ t('journals.sheet_title') }}
        </h2>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-[#1e4b8c] text-left text-white">
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.je_no') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.date') }}
              </th>
              <th class="min-w-[260px] border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.transaction') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.account_code') }}
              </th>
              <th class="min-w-[220px] border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.account_name') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.party_ref') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 text-right font-semibold">
                {{ t('journals.debit') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 text-right font-semibold">
                {{ t('journals.credit') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.cost_class') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.project_client') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, index) in rows"
              :key="row.id"
              :class="rowClass(row, index)"
            >
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 font-medium text-slate-800">
                {{ row.je_no }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.date }}
              </td>
              <td class="border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.transaction }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-800">
                {{ row.account_code }}
              </td>
              <td class="border border-slate-200 px-3 py-2 text-slate-800">
                {{ row.account_name }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.party_ref || '' }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-right tabular-nums text-slate-800">
                {{ formatAmount(row.debit) }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-right tabular-nums text-slate-800">
                {{ formatAmount(row.credit) }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-500">
                {{ row.cost_class || '' }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.project_client || '' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { sampleJournalEntries } from '../data/sampleJournalEntries'

const { t } = useTranslate()

const rows = computed(() => sampleJournalEntries)

const isFirstLineOfJe = (index) => {
  if (index === 0) return true
  return rows.value[index].je_no !== rows.value[index - 1].je_no
}

const rowClass = (row, index) => {
  const groupIndex = [...new Set(rows.value.map((item) => item.je_no))].indexOf(row.je_no)
  const zebra = groupIndex % 2 === 0 ? 'bg-white' : 'bg-slate-50'
  const start = isFirstLineOfJe(index) ? 'border-t-2 border-t-[#1e4b8c]/30' : ''
  return `${zebra} ${start}`
}

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return '—'
  return Number(value).toLocaleString('en-US')
}
</script>

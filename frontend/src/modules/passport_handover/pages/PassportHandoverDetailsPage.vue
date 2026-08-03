<template>
  <SectionHeader>
   <PageHeader>
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <div class="flex flex-wrap items-center gap-3">
        <router-link
          to="/passport-handover"
          class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-primary"
        >
          <i class="fa fa-arrow-left"></i>
          {{ $t('passport.back_to_list') }}
        </router-link>
      </div>
      <PageTitle class="mt-2">{{ $t('passport.details') }}</PageTitle>
      <p v-if="handover" class="mt-1 text-sm text-gray-500">
        {{ handover.handover_no }} · {{ handover.type_label || formatTypeLabel(handover) }} {{ $t('passport.handover') }}
      </p>
    </div>

    <div v-if="handover" class="flex flex-wrap gap-2">
      <BaseButton
        v-if="handover.pending_items_count > 0 && !handover.is_permanent"
        v-can="'passport_handover.collect'"
        class="bg-emerald-600 text-white hover:opacity-90"
        @click="openCollectModal"
      >
        <i class="fa fa-check-circle mr-1"></i>  {{ $t('passport.collect_reject') }}
      </BaseButton>
    </div>
  </div>
</PageHeader>

<div
  v-if="isLoading"
  class="rounded-lg bg-white p-8 text-center text-sm text-gray-500 shadow-sm"
>
  {{ $t('passport.loading_handover_details') }}
</div>

<div
  v-else-if="!handover"
  class="rounded-lg bg-white p-8 text-center text-sm text-gray-500 shadow-sm"
>
  {{ $t('passport.handover_record_not_found') }}
</div>

<div v-else class="space-y-6">
  <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-100 bg-linear-to-r from-slate-50 to-white px-6 py-5">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            {{ $t('passport.handover_no') }}
          </p>
          <h2 class="mt-1 text-2xl font-bold text-gray-900">{{ handover.handover_no }}</h2>
        </div>
        <span :class="statusBadgeClass(handover.status, handover.is_permanent)">
          {{ handover.status_label }}
        </span>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
      <InfoCard
        :label="$t('passport.taker_name')"
        :value="handover.taker_name"
        icon="fa fa-user"
      />
      <InfoCard
        :label="$t('passport.taker_phone')"
        :value="handover.taker_phone"
        icon="fa fa-phone"
      />
      <InfoCard
        :label="$t('passport.taken_at')"
        :value="handover.taken_at"
        icon="fa fa-calendar"
      />
      <InfoCard
        v-if="!handover.is_permanent"
        :label="$t('passport.expected_return')"
        :value="handover.expected_return_date || '—'"
        icon="fa fa-calendar-check-o"
      />
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-1">
      <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
        {{ $t('passport.summary') }}
      </h3>

      <dl class="mt-4 space-y-4 text-sm">
        <div>
          <dt class="text-gray-500">{{ $t('passport.type') }}</dt>
          <dd class="mt-1 font-semibold text-gray-900">
            {{ handover.type_label || formatTypeLabel(handover) }}
          </dd>
        </div>

        <div>
          <dt class="text-gray-500">{{ $t('passport.mode') }}</dt>
          <dd class="mt-1 font-semibold text-gray-900">
            {{ handover.is_permanent ? 'Permanent' : 'Temporary' }}
          </dd>
        </div>

        <div>
          <dt class="text-gray-500">{{ $t('passport.taken_reason') }}</dt>
          <dd class="mt-1 font-medium text-gray-900">
            {{ handover.taken_reason || '—' }}
          </dd>
        </div>

        <div v-if="!handover.is_permanent">
          <dt class="text-gray-500">{{ $t('passport.return_date') }}</dt>
          <dd class="mt-1 font-medium text-gray-900">
            {{ handover.return_date || '—' }}
          </dd>
        </div>

        <div>
          <dt class="text-gray-500">{{ $t('passport.handed_over_by') }}</dt>
          <dd class="mt-1 font-semibold text-gray-900">
            {{ handover.handed_over_by_name || handover.created_by || '—' }}
          </dd>
        </div>

        <div>
          <dt class="text-gray-500">{{ $t('shared.labels.created_at') }}</dt>
          <dd class="mt-1 font-medium text-gray-900">{{ handover.created_at }}</dd>
        </div>
      </dl>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
      <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
        {{
          handover.is_permanent
            ? $t('passport.handover_status')
            : $t('passport.collection_progress')
        }}
      </h3>

      <div
        v-if="handover.is_permanent"
        class="mt-4 rounded-lg border border-violet-200 bg-violet-50 px-4 py-5 text-sm text-violet-800"
      >
        <p class="font-semibold">{{ $t('passport.permanent_handover') }}</p>
        <p class="mt-1 text-violet-700">
          {{ $t('passport.permanent_handover_description') }}
        </p>
      </div>

      <template v-else>
        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
          <StatCard
            :label="$t('passport.total_passports')"
            :value="handover.total_items_count"
            tone="slate"
          />
          <StatCard
            :label="$t('passport.collected')"
            :value="handover.collected_items_count"
            tone="emerald"
          />
          <StatCard
            :label="$t('passport.rejected')"
            :value="handover.rejected_items_count"
            tone="rose"
          />
          <StatCard
            :label="$t('passport.pending')"
            :value="handover.pending_items_count"
            tone="amber"
          />
        </div>

        <div class="mt-5">
          <div class="mb-2 flex items-center justify-between text-xs text-gray-500">
            <span>{{ $t('passport.progress') }}</span>
            <span>{{ progressPercent }}%</span>
          </div>

          <div class="h-2 overflow-hidden rounded-full bg-gray-100">
            <div
              class="h-full rounded-full bg-emerald-500 transition-all"
              :style="{ width: `${progressPercent}%` }"
            ></div>
          </div>
        </div>
      </template>
    </div>
  </div>

  <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div
      class="flex flex-col gap-3 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h3 class="text-base font-semibold text-gray-900">
          {{ $t('passport.passport_items') }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ $t('passport.all_passports_with_employee_tracking') }}
        </p>
      </div>

      <input
        v-model="passportSearch"
        type="text"
        class="w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm sm:w-64"
        :placeholder="$t('passport.search_by_passport_no')"
      />
    </div>

    <div v-if="!filteredItems.length" class="px-6 py-8 text-center text-sm text-gray-500">
      {{ $t('passport.no_passport_items_match') }}
    </div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">#</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">
              {{ $t('shared.labels.candidates') }}
            </th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">
              {{ $t('passport.passport_no') }}
            </th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">
              {{ $t('passport.taken_reason') }}
            </th>

            <th
              v-if="!handover.is_permanent"
              class="px-4 py-3 text-left font-semibold text-gray-600"
            >
              {{ $t('passport.expected_return') }}
            </th>

            <th class="px-4 py-3 text-left font-semibold text-gray-600">
              {{ $t('passport.handed_over_by') }}
            </th>

            <th
              v-if="!handover.is_permanent"
              class="px-4 py-3 text-left font-semibold text-gray-600"
            >
              {{ $t('passport.processed_by') }}
            </th>

            <th
              v-if="!handover.is_permanent"
              class="px-4 py-3 text-left font-semibold text-gray-600"
            >
              {{ $t('passport.return_reject_date') }}
            </th>

            <th class="px-4 py-3 text-left font-semibold text-gray-600">
              {{ $t('shared.labels.status') }}
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 bg-white">
          <tr
            v-for="(item, index) in filteredItems"
            :key="item.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 text-gray-500">{{ index + 1 }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">
              {{ item.candidate_name || '—' }}
            </td>
            <td class="px-4 py-3 font-semibold text-gray-900">
              {{ item.passport_no }}
            </td>
            <td class="px-4 py-3 text-gray-700">
              {{ item.taken_reason || '—' }}
            </td>

            <td v-if="!handover.is_permanent" class="px-4 py-3 text-gray-700">
              {{ item.expected_return_date || '—' }}
            </td>

            <td class="px-4 py-3 text-gray-700">
              {{ item.handed_over_by_name || '—' }}
            </td>

            <td v-if="!handover.is_permanent" class="px-4 py-3 text-gray-700">
              {{ item.collected_by_name || item.rejected_by_name || '—' }}
            </td>

            <td v-if="!handover.is_permanent" class="px-4 py-3 text-gray-700">
              {{ item.return_date || item.reject_date || '—' }}
            </td>

            <td class="px-4 py-3">
              <span :class="itemStatusBadgeClass(item.status, handover.is_permanent)">
                {{ itemStatusLabel(item.status, handover.is_permanent) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

    <PassportHandoverCollectModal
      :is-visible="isCollectModalOpen"
      :handover-id="handoverId"
      @close="closeCollectModal"
      @collected="handleCollected"
    />
  </SectionHeader>
</template>

<script setup>
import { computed, defineComponent, h, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import PassportHandoverCollectModal from './components/PassportHandoverCollectModal.vue'
import { usePassportHandoverQuery } from '../queries/usePassportHandoversQuery'

const route = useRoute()
const handoverId = computed(() => route.params.id)
const { data, isLoading, refetch } = usePassportHandoverQuery(handoverId)

const isCollectModalOpen = ref(false)
const passportSearch = ref('')

const passportNoFromQuery = computed(() => {
  const v = route.query.passport_no ?? route.query.passportNo
  if (Array.isArray(v)) return v[0] ?? ''
  return v ?? ''
})

watch(
  passportNoFromQuery,
  (val) => {
    passportSearch.value = val ?? ''
  },
  { immediate: true },
)

const handover = computed(() => data.value?.data?.data ?? null)

const filteredItems = computed(() => {
  const items = handover.value?.items ?? []
  const query = passportSearch.value.trim().toLowerCase()

  if (!query) {
    return items
  }

  return items.filter((item) => item.passport_no?.toLowerCase().includes(query))
})

const progressPercent = computed(() => {
  const total = handover.value?.total_items_count || 0
  const collected = handover.value?.collected_items_count || 0
  const rejected = handover.value?.rejected_items_count || 0

  if (!total) return 0

  return Math.round(((collected + rejected) / total) * 100)
})

const InfoCard = defineComponent({
  name: 'InfoCard',
  props: {
    label: { type: String, required: true },
    value: { type: String, default: '—' },
    icon: { type: String, default: 'fa fa-info-circle' },
  },
  setup(props) {
    return () =>
      h('div', { class: 'rounded-lg border border-gray-100 bg-gray-50 p-4' }, [
        h('div', { class: 'flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500' }, [
          h('i', { class: props.icon }),
          h('span', props.label),
        ]),
        h('p', { class: 'mt-2 text-base font-semibold text-gray-900' }, props.value || '—'),
      ])
  },
})

const StatCard = defineComponent({
  name: 'StatCard',
  props: {
    label: { type: String, required: true },
    value: { type: [String, Number], default: 0 },
    tone: { type: String, default: 'slate' },
  },
  setup(props) {
    const toneClasses = {
      slate: 'border-slate-200 bg-slate-50 text-slate-700',
      emerald: 'border-emerald-200 bg-emerald-50 text-emerald-700',
      amber: 'border-amber-200 bg-amber-50 text-amber-700',
      rose: 'border-rose-200 bg-rose-50 text-rose-700',
    }

    return () =>
      h(
        'div',
        {
          class: `rounded-lg border px-4 py-3 ${toneClasses[props.tone] || toneClasses.slate}`,
        },
        [
          h('p', { class: 'text-xs font-semibold uppercase tracking-wide opacity-80' }, props.label),
          h('p', { class: 'mt-1 text-2xl font-bold' }, String(props.value)),
        ]
      )
  },
})

function formatTypeLabel(handover) {
  const structure = handover.type === 'group' ? 'Group' : 'Single'
  return handover.is_permanent ? `Permanent ${structure}` : structure
}

function statusBadgeClass(status, isPermanent = false) {
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-semibold'

  if (isPermanent && status === 'handed_over') {
    return `${base} bg-violet-100 text-violet-800`
  }

  if (status === 'collected') {
    return `${base} bg-emerald-100 text-emerald-800`
  }

  if (status === 'rejected') {
    return `${base} bg-rose-100 text-rose-800`
  }

  if (status === 'partially_collected') {
    return `${base} bg-amber-100 text-amber-800`
  }

  return `${base} bg-blue-100 text-blue-800`
}

function itemStatusBadgeClass(status, isPermanent = false) {
  const base = 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold'

  if (isPermanent && status === 'handed_over') {
    return `${base} bg-violet-100 text-violet-800`
  }

  if (status === 'collected') {
    return `${base} bg-emerald-100 text-emerald-800`
  }

  if (status === 'rejected') {
    return `${base} bg-rose-100 text-rose-800`
  }

  return `${base} bg-blue-100 text-blue-800`
}

function itemStatusLabel(status, isPermanent = false) {
  if (isPermanent && status === 'handed_over') {
    return 'Permanent'
  }

  if (status === 'collected') {
    return 'Collected'
  }

  if (status === 'rejected') {
    return 'Rejected'
  }

  return 'Handed Over'
}

function openCollectModal() {
  isCollectModalOpen.value = true
}

function closeCollectModal() {
  isCollectModalOpen.value = false
}

function handleCollected() {
  closeCollectModal()
  refetch()
}
</script>

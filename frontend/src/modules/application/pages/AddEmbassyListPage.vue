<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <div class="mb-1 flex items-center gap-2 text-sm text-gray-500">
          <router-link :to="{ name: 'Embassy List' }" class="hover:text-indigo-600">
           {{t('embassy.embassy_list')}}
          </router-link>
          <span>/</span>
          <span class="text-gray-700">{{ t('embassyList.add') }}</span>
        </div>
        <PageTitle>{{ t('embassyList.add') }}</PageTitle>
      </div>
    </PageHeader>

    <div class="mt-4 rounded-lg bg-white p-6 shadow-sm lg:p-8">
      <div class="mx-auto max-w-5xl rounded-md border border-gray-200 bg-white p-4 sm:p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
          <label class="text-sm font-medium text-gray-700">{{ t('embassyList.submit_date') }}</label>
          <input
            type="date"
            v-model="submitDate"
            class="w-full max-w-xs rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700"
          />
        </div>

        <div class="mt-6 flex justify-center">
          <div class="inline-flex border-b border-gray-200 text-sm">
            <button
              class="px-5 py-2"
              :class="activeTab === 'restamping' ? 'border-b-2 border-indigo-500 font-semibold text-indigo-600' : 'text-gray-500'"
              @click="activeTab = 'restamping'"
            >{{ t('embassyList.re_stamping') }}</button>
            <button
              class="px-5 py-2"
              :class="activeTab === 'new_stamping' ? 'border-b-2 border-indigo-500 font-semibold text-indigo-600' : 'text-gray-500'"
              @click="activeTab = 'new_stamping'"
            >{{ t('embassyList.new_stamping') }}</button>
            <button
              class="px-5 py-2"
              :class="activeTab === 'cancellation' ? 'border-b-2 border-indigo-500 font-semibold text-indigo-600' : 'text-gray-500'"
              @click="activeTab = 'cancellation'"
            >{{ t('embassyList.cancellation') }}</button>
          </div>
        </div>

        <div class="mt-5 rounded-md border border-gray-200 bg-gray-50 p-4 sm:p-6">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <label class="text-sm font-medium text-gray-700">{{ t('shared.labels.passport_no') }}</label>
            <input
              type="text"
              :placeholder="t('application.enter_passport_no_or_search')"
              v-model="tabPassportInputs[activeTab]"
              @focus="showHints = true"
              @blur="handlePassportBlur"
              class="w-full max-w-xs rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700"
            />
            <BaseButton
              class="w-fit bg-emerald-600 text-white hover:bg-emerald-700"
              @click="addPassportToActiveTab"
            >{{ t('shared.actions.add') }}</BaseButton>
          </div>

          <div
            v-if="showHints && activeHints.length"
            class="mt-3 max-w-3xl overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
          >
            <div
              class="border-b border-gray-100 bg-gray-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500"
            >{{ t('embassyList.search_hints') }}</div>
            <div class="divide-y divide-gray-100">
              <button
                v-for="item in activeHints"
                :key="`${activeTab}-${item.applicationId ?? item.passport}`"
                type="button"
                class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-indigo-50"
                @mousedown.prevent="selectHint(item)"
              >
                <div
                  class="mt-0.5 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white"
                >{{ item.index }}</div>
                <div class="flex-1">
                  <div
                    class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-semibold text-gray-900"
                  >
                    <span>{{ item.name }}</span>
                    <span class="text-gray-400">/</span>
                    <span>{{ item.surname }}</span>
                  </div>
                  <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
                    <span>
                      {{ t('shared.labels.passport_no') }}:
                      <span class="font-medium text-gray-800">{{ item.passport }}</span>
                    </span>
                    <span>
                      {{ t('embassy.visa_no') }}:
                      <span class="font-medium text-gray-800">{{ item.visaNo }}</span>
                    </span>
                    <span>
                      {{ t('demand_letter.sponsor_id') }}:
                      <span class="font-medium text-gray-800">{{ item.sponsorId }}</span>
                    </span>
                    <span>
                           {{ t('embassy.profession') }}:
                      <span class="font-medium text-gray-800">{{ item.professionAr }}</span>
                    </span>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="mt-4 space-y-2">
            <div
              v-for="item in activeApplicants"
              :key="`${activeTab}-${item.passport}`"
              class="flex items-center gap-3 rounded border border-gray-300 bg-gray-200 px-3 py-3 text-sm"
            >
              <span class="text-base leading-none text-gray-700">+</span>
              <span
                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black text-xs font-semibold text-white"
              >{{ item.id }}</span>
              <span class="flex-1 text-gray-800">
                <span class="font-semibold">{{ item.name }}</span>
                <span class="font-semibold">- {{ item.code }}</span>
                <span>- {{ item.passport }}</span>
                <span>- {{ item.nid }}</span>
                <span>- {{ item.professionAr }}</span>
              </span>
              <button
                type="button"
                class="text-base font-bold leading-none text-gray-700 transition hover:text-red-600"
                title="Remove"
                @click="removeApplicantFromActiveTab(item.passport)"
              >x</button>
            </div>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap justify-center gap-3">
          <BaseButton
            class="bg-slate-800 px-6 text-white hover:bg-slate-900"
            :disabled="submitLoading || !hasApplicants"
            @click="handleCreateList"
          >
            <span v-if="submitLoading">{{ t('shared.messages.Creating') }}</span>
            <span v-else>{{ t('embassyList.create_list') }}</span>
          </BaseButton>
          <router-link :to="{ name: 'Embassy List' }">
            <BaseButton
              :className="'bg-white text-gray-800 ring-1 ring-gray-300 hover:bg-gray-50 cursor-pointer'"
            >
              <i class="fa fa-arrow-left mr-1"></i> {{ t('embassyList.back_to_embassy_list') }}
            </BaseButton>
          </router-link>
        </div>
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import { toast } from '@/shared/config/toastConfig'
import { useEmbassyListForm } from '../composables/useEmbassyListForm'
import { useEmbassyListMutations } from '../queries/useEmbassyListMutations'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const router = useRouter()

const {
  submitDate,
  activeTab,
  showHints,
  tabPassportInputs,
  activeApplicants,
  activeHints,
  hasApplicants,
  addPassportToActiveTab,
  removeApplicantFromActiveTab,
  selectHint,
  handlePassportBlur,
  buildPayload,
} = useEmbassyListForm()

const { submit, submitLoading } = useEmbassyListMutations('Embassy List', {
  onSuccess: () => {
    router.push({ name: 'Embassy List' })
  },
})

const handleCreateList = async () => {
  if (!submitDate.value) {
    toast.error('Please select a submit date.')
    return
  }

  if (!hasApplicants.value) {
    toast.error('Please add at least one candidate.')
    return
  }

  await submit.mutateAsync(buildPayload())
}
</script>

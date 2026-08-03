<template>
  <form @submit.prevent="onSubmit" class="h-150 grid grid-cols-1 md:grid-cols-2 gap-2 py-5!">
    <!-- Demand Letter -->
    <div>
      <BaseLabel for="work_order_id">{{ t('shared.labels.demand_letter') }}</BaseLabel>
      <BaseSearchSelect
        id="work_order_id"
        v-model="formData.work_order_id"
        :options="workOrders"
        placeholder="Search demand letter..."
        option-label="name"
        option-value="id"
        :filter-fn="filterWorkOrderOption"
        :required="true"
      />
    </div>

    <!-- Principal -->
    <div v-can="'job.select_principal'">
      <BaseLabel for="principal_id">{{ t('shared.labels.principal') }}</BaseLabel>
      <BaseSearchSelect
        id="principal_id"
        v-model="formData.principal_id"
        :options="principals"
        placeholder="Search principal..."
        option-label="name"
        option-value="id"
        :filter-fn="filterPrincipalOption"
        :required="true"
      />
    </div>

    <!-- Job Name -->
    <div>
      <BaseLabel for="name">{{ t('shared.labels.job_name') }}</BaseLabel>
      <BaseInput
        id="name"
        type="text"
        v-model="formData.name"
        placeholder="Factory Worker"
        :required="true"
      />
    </div>

    <!-- Vacancy -->
    <div>
      <BaseLabel for="vacancy">{{ t('shared.labels.vacancy') }}</BaseLabel>
      <BaseInput
        id="vacancy"
        type="number"
        v-model="formData.vacancy"
        placeholder="Eg: 10"
        :required="true"
      />
    </div>

    <!-- Experience -->
    <div>
      <BaseLabel for="experience">{{ t('shared.labels.experience') }}</BaseLabel>
      <BaseInput
        id="experience"
        type="text"
        v-model="formData.experience"
        placeholder="No experience required"
      />
    </div>

    <div>
      <BaseLabel for="price">{{ t('job.price_per_candidate_taka') }}</BaseLabel>
      <BaseInput id="price" type="text" v-model="formData.price" placeholder="Enter price" />
    </div>
    <div>
      <BaseLabel for="client_commission_per_candidate">{{
        t('job.client_commission_per_candidate')
      }}</BaseLabel>
      <BaseInput
        id="client_commission_per_candidate"
        type="text"
        v-model="formData.client_commission_per_candidate"
        placeholder="Enter client commission per candidate"
      />
    </div>

    <!-- Min Age -->
    <div>
      <BaseLabel for="min_age">{{ t('job.min_age') }}</BaseLabel>
      <BaseInput id="min_age" type="number" v-model="formData.min_age" placeholder="18" />
    </div>

    <!-- Max Age -->
    <div>
      <BaseLabel for="max_age">{{ t('job.max_age') }}</BaseLabel>
      <BaseInput id="max_age" type="number" v-model="formData.max_age" placeholder="40" />
    </div>

    <!-- Contract Length -->
    <div>
      <BaseLabel for="contract_length">{{ t('job.contract_length') }}</BaseLabel>
      <BaseInput
        id="contract_length"
        type="text"
        v-model="formData.contract_length"
        placeholder="2 Years"
      />
    </div>

    <!-- Qualification -->
    <div>
      <BaseLabel for="qualification">{{ t('job.qualification') }}</BaseLabel>
      <BaseInput
        id="qualification"
        type="text"
        v-model="formData.qualification"
        placeholder="SSC Pass"
      />
    </div>

    <!-- Language -->
    <div>
      <BaseLabel for="language">{{ t('job.language') }}</BaseLabel>
      <BaseInput
        id="language"
        type="text"
        v-model="formData.language"
        placeholder="Basic English"
      />
    </div>

    <!-- Salary -->
    <div>
      <BaseLabel for="salary">{{ t('shared.labels.salary') }}</BaseLabel>
      <BaseInput id="salary" type="text" v-model="formData.salary" placeholder="1200 AED" />
    </div>

    <!-- Deadline -->
    <div>
      <BaseLabel for="deadline">{{ t('job.application_deadline') }}</BaseLabel>
      <BaseInput id="deadline" type="date" v-model="formData.deadline" />
    </div>

    <!-- Interview Date -->
    <div>
      <BaseLabel for="interview_date">{{ t('shared.labels.interview_date') }}</BaseLabel>
      <BaseInput id="interview_date" type="date" v-model="formData.interview_date" />
    </div>

    <!-- Status -->
    <div>
      <BaseLabel for="status">{{ t('shared.labels.status') }}</BaseLabel>
      <BaseSelect
        id="status"
        v-model="formData.status"
        :options="options"
        placeholder="Select Status"
      />
    </div>

    <!-- Description -->
    <div class="col-span-1 md:col-span-2">
      <BaseLabel for="description">{{ t('job.description') }}</BaseLabel>
      <textarea
        id="description"
        rows="2"
        class="w-full border rounded-lg p-2"
        v-model="formData.description"
        placeholder="Job description..."
      ></textarea>
    </div>

    <!-- Actions -->
    <div class="col-span-1 md:col-span-2 flex justify-end gap-2 pt-4 pb-6">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
        {{ t('shared.actions.cancel') }}
      </BaseButton>

      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.messages.saving') }}...</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </form>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')

const formData = defineModel('formData', { type: Object, required: true })

const props = defineProps({
  principals: { type: Array, default: () => [] },
  workOrders: { type: Array, default: () => [] },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
})

const options = [
  { name: 'Open', id: 'open' },
  { name: 'Closed', id: 'closed' },
  { name: 'Hold', id: 'hold' },
]

function filterWorkOrderOption(option, query) {
  const haystack = [option?.name, option?.search_name, option?.id]
    .filter(Boolean)
    .join(' ')
    .toLowerCase()

  return haystack.includes(query)
}

function filterPrincipalOption(option, query) {
  const haystack = [option?.name, option?.search_name, option?.id]
    .filter(Boolean)
    .join(' ')
    .toLowerCase()

  return haystack.includes(query)
}

const onSubmit = () => {
  props.onSubmit()
}

const onCancel = () => {
  props.onCancel()
}
</script>

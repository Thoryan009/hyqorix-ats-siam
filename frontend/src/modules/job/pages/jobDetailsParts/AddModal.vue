<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('job_price.add')"
    @close="store.handleToggleModal"
  >
    <BaseForm :onSubmit="onSubmit">
      <div v-for="(item, index) in fromData" :key="index" class="space-y-2">
        <div class="flex flex-col lg:flex-row gap-2 items-start lg:items-end">
          <!-- Fee Name -->
          <div class="space-y-2 flex-1 w-full">
            <BaseLabel :for="`fee_name_${index}`">{{ t('job_price.fee_name') }}</BaseLabel>
            <BaseSelect
              :id="`fee_name_${index}`"
              v-model="item.job_list_details_head_id"
              :options="fees"
              placeholder="Select"
              :required="true"
            />
          </div>
          <!-- Amount -->
          <div class="space-y-2 flex-1 w-full">
            <BaseLabel :for="`amount_${index}`">{{ t('job_price.amount') }}</BaseLabel>
            <BaseInput
              :id="`amount_${index}`"
              type="number"
              v-model="item.amount"
              :required="true"
              placeholder="Eg: 1500"
            />
          </div>
          <!-- Amount USD -->
          <div class="space-y-2 flex-1 w-full">
            <BaseLabel :for="`amount_usd_${index}`">{{ t('job_price.amount_usd') }}</BaseLabel>
            <BaseInput
              :id="`amount_usd_${index}`"
              type="number"
              v-model="item.amount_usd"
              :required="true"
              placeholder="Eg: 30"
            />
          </div>
          <!-- Remove Button -->
          <div v-if="fromData.length > 1" class="w-full lg:w-auto">
            <BaseButton type="button" class="bg-red-600 hover:bg-red-700 w-full lg:w-auto" @click="removeRow(index)">
              {{ t('application.remove') }}
            </BaseButton>
          </div>
        </div>
      </div>

      <!-- Add More Button -->
      <div class="flex justify-start pt-2">
        <BaseButton type="button" class="bg-green-600 hover:bg-green-700 w-full sm:w-auto" @click="addRow">
          + {{ t('passport.add_more') }}
        </BaseButton>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4 border-t">
        <BaseButton class="bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto" type="button" @click="store.handleToggleModal"
          > {{ t('shared.actions.cancel') }}</BaseButton
        >
        <BaseButton class="w-full sm:w-auto" :disabled="!isFormValid || bulkSubmitLoading" type="submit">
          <span v-if="bulkSubmitLoading">{{ t('shared.messages.saving') }}</span>
          <span v-else>{{ t('shared.actions.save') }}</span>
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useJobDetailsStore } from '../../store/jobDetailsStore'
import { useJobDetailHeadsQuery } from '../../queries/useJobDetailsQuery'
import { useJobDetailsMutations } from '../../queries/useJobDetailsMutations'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const props = defineProps({
  jobId: {
    type: [String, Number],
    required: true,
  },
})

const store = useJobDetailsStore()

const { data } = useJobDetailHeadsQuery()
const fees = computed(() => data.value?.data?.data ?? [])

const initalForm = {
  job_list_details_head_id: null,
  amount: null,
  amount_usd: null,
}

const fromData = ref([{ ...initalForm }])

// Auto-fill amount and amount_usd when fee is selected
watch(
  () => fromData.value.map((item) => item.job_list_details_head_id),
  (newIds, oldIds) => {
    newIds.forEach((newId, index) => {
      // Only auto-fill if the ID changed
      if (newId && newId !== oldIds?.[index]) {
        const selectedFee = fees.value.find((fee) => fee.id == newId)

        if (selectedFee) {
          fromData.value[index].amount = selectedFee.amount ?? 0
          fromData.value[index].amount_usd = selectedFee.amount_usd ?? 0
        }
      }
    })
  },
  { deep: true },
)

const addRow = () => {
  fromData.value.push({
    job_list_details_head_id: null,
    amount: null,
    amount_usd: null,
  })
}

const removeRow = (index) => {
  if (fromData.value.length > 1) {
    fromData.value.splice(index, 1)
  }
}

const isFormValid = computed(() => {
  return fromData.value.every((item) => item.job_list_details_head_id)
})

const { bulkSubmit, bulkSubmitLoading } = useJobDetailsMutations(store.moduleName, {
  onSuccess() {
    fromData.value = [{ ...initalForm }] // reset form
    store.handleToggleModal() // ✅ close modal
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

const onSubmit = () => {
  const payload = fromData.value.map((item) => ({
    ...item,
    job_list_id: props.jobId,
  }))
  bulkSubmit.mutate(payload)
}
</script>

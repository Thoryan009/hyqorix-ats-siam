<template>
  <BaseModal
    :is-visible="isVisible"
    :title="$t('passport.collect_passports')"
    class-name="max-w-3xl xl:max-w-5xl max-h-[90vh]"
    @close="$emit('close')"
  >
    <div v-if="isLoading" class="py-8 text-center text-sm text-gray-500">
      {{ $t('passport.loading_details') }}
    </div>

    <div v-else-if="handover" class="space-y-5">
      <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
          <p>
            <span class="text-gray-500">{{ $t('passport.handover_no') }}:</span>
            <span class="font-semibold">{{ handover.handover_no }}</span>
          </p>
          <p>
            <span class="text-gray-500">{{ $t('passport.taker') }}:</span>
            <span class="font-semibold">{{ handover.taker_name }}</span>
          </p>
          <p>
            <span class="text-gray-500">{{ $t('passport.phone') }}:</span>
            <span class="font-semibold">{{ handover.taker_phone }}</span>
          </p>
          <p>
            <span class="text-gray-500">{{ $t('passport.taken_at') }}:</span>
            <span class="font-semibold">{{ handover.taken_at }}</span>
          </p>
        </div>
      </div>

      <div
        v-if="!pendingItems.length"
        class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800"
      >
        {{ $t('passport.all_passports_processed') }}
      </div>

      <template v-else>
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <BaseLabel>{{ $t('passport.process_pending_passports') }}</BaseLabel>
            <button
              type="button"
              class="text-sm font-medium text-primary hover:underline"
              @click="toggleAllIncluded"
            >
              {{ allIncluded ? $t('passport.clear_all') : $t('passport.select_all') }}
            </button>
          </div>

          <div class="max-h-[28rem] space-y-3 overflow-y-auto rounded-lg border border-gray-200 p-3">
            <div
              v-for="item in pendingItems"
              :key="item.id"
              class="rounded-md border border-gray-100 bg-white p-4"
            >
              <div class="flex items-start gap-3">
                <input
                  v-model="itemForms[item.id].included"
                  type="checkbox"
                  class="mt-1 h-4 w-4 accent-primary"
                />
                <div class="flex-1 space-y-3">
                  <div class="text-sm">
                    <p class="font-semibold text-gray-900">
                      {{ item.candidate_name || $t('shared.labels.candidates') }}
                    </p>
                    <p class="mt-1 text-gray-600">
                      {{ $t('passport.passport') }}:
                      <span class="font-medium">{{ item.passport_no }}</span>
                    </p>
                    <p v-if="item.taken_reason" class="mt-1 text-gray-500">
                      {{ $t('passport.reason') }}: {{ item.taken_reason }}
                    </p>
                  </div>

                  <div
                    v-if="itemForms[item.id].included"
                    class="grid grid-cols-1 gap-3 md:grid-cols-2"
                  >
                    <div class="space-y-2">
                      <BaseLabel :for="`action_${item.id}`">
                        {{ $t('passport.action') }}
                      </BaseLabel>
                      <select
                        :id="`action_${item.id}`"
                        v-model="itemForms[item.id].action"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                      >
                        <option value="collect">
                          {{ $t('passport.collect') }}
                        </option>
                        <option value="reject">
                          {{ $t('passport.reject') }}
                        </option>
                      </select>
                    </div>

                    <div class="space-y-2">
                      <BaseLabel :for="`date_${item.id}`">
                        {{
                          itemForms[item.id].action === 'reject'
                            ? $t('passport.reject_date')
                            : $t('passport.return_date')
                        }}
                      </BaseLabel>

                      <BaseInput
                        :id="`date_${item.id}`"
                        v-model="itemForms[item.id].date"
                        type="date"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-4">
        <BaseButton
          :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          @click="$emit('close')"
        >
          {{ $t('shared.actions.cancel') }}
        </BaseButton>

        <BaseButton
          v-if="pendingItems.length"
          class="bg-primary text-white hover:opacity-90"
          :disabled="collectLoading || !includedItems.length"
          @click="handleSubmit"
        >
          {{
            collectLoading
              ? $t('common.processing')
              : $t('passport.process_selected')
          }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import { usePassportHandoverQuery } from '../../queries/usePassportHandoversQuery'
import { usePassportHandoverMutations } from '../../queries/usePassportHandoverMutations'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  isVisible: { type: Boolean, default: false },
  handoverId: { type: [Number, String, null], default: null },
})

const emit = defineEmits(['close', 'collected'])

const itemForms = reactive({})

const handoverIdRef = computed(() => (props.isVisible ? props.handoverId : null))
const { data, isLoading } = usePassportHandoverQuery(handoverIdRef)

const { collect, collectLoading } = usePassportHandoverMutations('Passport Handover', {
  onCollectSuccess: () => {
    emit('collected')
    emit('close')
  },
})

const handover = computed(() => data.value?.data?.data ?? null)

const pendingItems = computed(() =>
  (handover.value?.items ?? []).filter((item) => item.status === 'handed_over')
)

const includedItems = computed(() =>
  pendingItems.value.filter((item) => itemForms[item.id]?.included)
)

const allIncluded = computed(
  () =>
    pendingItems.value.length > 0 &&
    includedItems.value.length === pendingItems.value.length
)

function today() {
  return new Date().toISOString().slice(0, 10)
}

function initializeItemForms(items) {
  Object.keys(itemForms).forEach((key) => {
    delete itemForms[key]
  })

  items.forEach((item) => {
    itemForms[item.id] = {
      included: true,
      action: 'collect',
      date: today(),
    }
  })
}

watch(
  () => props.isVisible,
  (visible) => {
    if (!visible) {
      Object.keys(itemForms).forEach((key) => {
        delete itemForms[key]
      })
      return
    }

    initializeItemForms(pendingItems.value)
  }
)

watch(pendingItems, (items) => {
  if (props.isVisible) {
    initializeItemForms(items)
  }
})

function toggleAllIncluded() {
  const nextValue = !allIncluded.value

  pendingItems.value.forEach((item) => {
    if (itemForms[item.id]) {
      itemForms[item.id].included = nextValue
    }
  })
}

async function handleSubmit() {
  if (!props.handoverId || !includedItems.value.length) {
    toast.error('Please select at least one passport to process.')
    return
  }

  const payloadItems = []

  for (const item of includedItems.value) {
    const form = itemForms[item.id]

    if (!form?.date) {
      toast.error(
        form?.action === 'reject'
          ? `Reject date is required for passport ${item.passport_no}.`
          : `Return date is required for passport ${item.passport_no}.`
      )
      return
    }

    payloadItems.push({
      id: item.id,
      action: form.action,
      return_date: form.action === 'collect' ? form.date : null,
      reject_date: form.action === 'reject' ? form.date : null,
    })
  }

  await collect.mutateAsync({
    id: props.handoverId,
    payload: { items: payloadItems },
  })
}
</script>

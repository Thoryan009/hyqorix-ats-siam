<template>
  <BaseModal
    :is-visible="isVisible"
    :title="$t('passport.create_handover')"
    class-name="max-w-4xl xl:max-w-5xl max-h-[90vh]"
    @close="$emit('close')"
  >
    <div class="space-y-6">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-3">
          <BaseLabel>{{ $t('passport.handover_type') }}</BaseLabel>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="type in handoverTypes"
              :key="type.id"
              type="button"
              class="rounded-lg px-4 py-2 text-sm font-medium transition"
              :class="
                sharedFields.type === type.id
                  ? 'bg-primary text-white'
                  : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
              "
              @click="setType(type.id)"
            >
              {{ type.label }}
            </button>
          </div>
        </div>

        <div class="space-y-3">
          <BaseLabel>{{ $t('passport.handover_mode') }}</BaseLabel>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="mode in handoverModes"
              :key="mode.id"
              type="button"
              class="rounded-lg px-4 py-2 text-sm font-medium transition"
              :class="
                sharedFields.is_permanent === mode.permanent
                  ? 'bg-primary text-white'
                  : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
              "
              @click="setPermanent(mode.permanent)"
            >
              {{ mode.label }}
            </button>
          </div>
          <p v-if="isPermanent" class="text-xs text-amber-700">
            {{ $t('passport.permanent_note') }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <BaseLabel for="taker_name">{{ $t('passport.taker_name') }}</BaseLabel>
          <BaseInput
            id="taker_name"
            v-model="sharedFields.taker_name"
            :placeholder="$t('passport.enter_taker_name')"
          />
        </div>

        <div class="space-y-2">
          <BaseLabel for="taker_phone">{{ $t('passport.taker_phone') }}</BaseLabel>
          <BaseInput
            id="taker_phone"
            v-model="sharedFields.taker_phone"
            :placeholder="$t('passport.enter_taker_phone')"
          />
        </div>

        <div class="space-y-2">
          <BaseLabel for="taken_at">{{ $t('passport.taken_at') }}</BaseLabel>
          <BaseInput id="taken_at" v-model="sharedFields.taken_at" type="date" />
        </div>

        <div v-if="isGroupType && !isPermanent" class="space-y-2">
          <BaseLabel for="expected_return_date">
            {{ $t('passport.expected_return_date') }}
          </BaseLabel>
          <BaseInput
            id="expected_return_date"
            v-model="sharedFields.expected_return_date"
            type="date"
          />
        </div>
      </div>

      <div v-if="isSingleType" class="space-y-4">
        <div
          v-for="(entry, index) in singleEntries"
          :key="entry.id"
          class="rounded-lg border border-gray-200 bg-gray-50 p-4"
        >
          <div class="mb-4 flex items-center justify-between gap-3">
            <h4 class="text-sm font-semibold text-gray-800">
              {{ $t('passport.single_entry') }} {{ index + 1 }}
            </h4>

            <button
              v-if="singleEntries.length > 1"
              type="button"
              class="text-sm font-medium text-red-600 hover:text-red-700"
              @click="removeSingleEntry(entry.id)"
            >
              {{ $t('passport.remove') }}
            </button>
          </div>

          <div class="space-y-4">
            <ApplicationPassportSearch
              mode="single"
              :exclude-application-ids="getExcludeIdsForEntry(entry)"
              @select="setSingleEntryApplication(entry.id, $event)"
            />

            <div
              v-if="entry.application"
              class="flex items-start justify-between gap-3 rounded-md border border-gray-200 bg-white px-3 py-3 text-sm"
            >
              <div>
                <p class="font-semibold text-gray-900">{{ entry.application.fullName }}</p>
                <p class="mt-1 text-gray-600">
                  {{ $t('passport.passport') }}:
                  <span class="font-medium text-gray-800">{{ entry.application.passportNo }}</span>
                </p>
              </div>

              <button
                type="button"
                class="text-gray-500 hover:text-red-600"
                @click="removeSingleEntryApplication(entry.id)"
              >
                &times;
              </button>
            </div>

            <div
              class="grid grid-cols-1 gap-4"
              :class="isPermanent ? 'md:grid-cols-1' : 'md:grid-cols-3'"
            >
              <div v-if="!isPermanent" class="space-y-2">
                <BaseLabel :for="`expected_return_date_${entry.id}`">
                  {{ $t('passport.expected_return_date') }}
                </BaseLabel>
                <BaseInput
                  :id="`expected_return_date_${entry.id}`"
                  v-model="entry.expected_return_date"
                  type="date"
                />
              </div>

              <div class="space-y-2">
                <BaseLabel :for="`taken_reason_${entry.id}`">
                  {{ $t('passport.taken_reason') }}
                </BaseLabel>
                <BaseInput
                  :id="`taken_reason_${entry.id}`"
                  v-model="entry.taken_reason"
                  :placeholder="$t('passport.enter_taken_reason')"
                />
              </div>

              <div v-if="!isPermanent" class="space-y-2">
                <BaseLabel :for="`return_date_${entry.id}`">
                  {{ $t('passport.return_date') }}
                </BaseLabel>
                <BaseInput
                  :id="`return_date_${entry.id}`"
                  v-model="entry.return_date"
                  type="date"
                />
              </div>
            </div>
          </div>
        </div>

        <BaseButton
          :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          @click="addSingleEntry"
        >
          <i class="fa fa-plus mr-1"></i> {{ $t('passport.add_more') }}
        </BaseButton>
      </div>

      <div v-else class="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <ApplicationPassportSearch
          mode="multi"
          :exclude-application-ids="excludedApplicationIds"
          @select="addGroupApplication"
        />

        <div v-if="groupFields.applications.length" class="space-y-2">
          <p class="text-sm font-medium text-gray-700">
            {{ $t('passport.selected_applications') }} ({{ groupFields.applications.length }})
          </p>

          <div
            v-for="application in groupFields.applications"
            :key="application.id"
            class="flex items-start justify-between gap-3 rounded-md border border-gray-200 bg-white px-3 py-3 text-sm"
          >
            <div>
              <p class="font-semibold text-gray-900">{{ application.fullName }}</p>
              <p class="mt-1 text-gray-600">
                {{ $t('passport.passport') }}:
                <span class="font-medium text-gray-800">{{ application.passportNo }}</span>
              </p>
            </div>

            <button
              type="button"
              class="text-gray-500 hover:text-red-600"
              @click="removeGroupApplication(application.id)"
            >
              &times;
            </button>
          </div>
        </div>

        <div
          class="grid grid-cols-1 gap-4"
          :class="isPermanent ? 'md:grid-cols-1' : 'md:grid-cols-2'"
        >
          <div class="space-y-2">
            <BaseLabel for="group_taken_reason">
              {{ $t('passport.taken_reason') }}
            </BaseLabel>
            <BaseInput
              id="group_taken_reason"
              v-model="groupFields.taken_reason"
              :placeholder="$t('passport.enter_taken_reason')"
            />
          </div>

          <div v-if="!isPermanent" class="space-y-2">
            <BaseLabel for="group_return_date">
              {{ $t('passport.return_date') }}
            </BaseLabel>
            <BaseInput id="group_return_date" v-model="groupFields.return_date" type="date" />
          </div>
        </div>
      </div>

      <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-4">
        <BaseButton
          :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          @click="$emit('close')"
        >
          {{ $t('passport.cancel') }}
        </BaseButton>

        <BaseButton
          class="bg-primary text-white hover:opacity-90"
          :disabled="submitLoading"
          @click="handleSubmit"
        >
          {{
            submitLoading
              ? $t('passport.saving')
              : $t('passport.create_handover')
          }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { watch } from 'vue'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import ApplicationPassportSearch from './ApplicationPassportSearch.vue'
import { usePassportHandoverForm } from '../../composables/usePassportHandoverForm'
import { usePassportHandoverMutations } from '../../queries/usePassportHandoverMutations'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  isVisible: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'created'])

const handoverTypes = [
  { id: 'single', label: 'Single' },
  { id: 'group', label: 'Group' },
]

const handoverModes = [
  { id: 'temporary', label: 'Temporary', permanent: false },
  { id: 'permanent', label: 'Permanent', permanent: true },
]

const {
  sharedFields,
  groupFields,
  singleEntries,
  isSingleType,
  isGroupType,
  isPermanent,
  excludedApplicationIds,
  setType,
  setPermanent,
  addSingleEntry,
  removeSingleEntry,
  setSingleEntryApplication,
  removeSingleEntryApplication,
  addGroupApplication,
  removeGroupApplication,
  resetForm,
  buildPayload,
  validateForm,
} = usePassportHandoverForm()

const { submit, submitLoading } = usePassportHandoverMutations('Passport Handover', {
  onSuccess: () => {
    emit('created')
    emit('close')
  },
})

watch(
  () => props.isVisible,
  (visible) => {
    if (!visible) {
      resetForm()
    }
  }
)

function getExcludeIdsForEntry(entry) {
  const currentId = entry.application?.id
  return excludedApplicationIds.value.filter((id) => Number(id) !== Number(currentId))
}

async function handleSubmit() {
  const validation = validateForm()
  if (!validation.ok) {
    toast.error(validation.message)
    return
  }

  await submit.mutateAsync(buildPayload())
}
</script>

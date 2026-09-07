  <template>
  <div class="space-y-2 md:col-span-2">
    <BaseLabel>{{ label }}</BaseLabel>
    <!-- <pre>{{ visibleOptions}}</pre> -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
      <button
        v-for="option in visibleOptions"
        :key="option.id"
        type="button"
        class="rounded-xl border p-4 text-left transition focus:outline-none"
        :class="
          isSelected(option.id)
            ? `${option.activeBorder} ${option.activeBg} ring-2 ${option.activeRing}`
            : 'border-gray-200 bg-white hover:border-gray-300'
        "
        @click="toggle(option.id)"
      >
        <div class="flex items-start gap-3">
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="option.iconBg"
          >
            <i :class="[option.icon, 'text-lg']"></i>
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <p class="font-semibold text-gray-900">{{ option.label }}</p>
              <span
                class="flex h-5 w-5 items-center justify-center rounded border"
                :class="
                  isSelected(option.id)
                    ? 'border-primary bg-primary text-white'
                    : 'border-gray-300 bg-white text-transparent'
                "
              >
                <i class="fa fa-check text-[10px]"></i>
              </span>
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ option.description }}</p>
          </div>
        </div>
      </button>
    </div>
    <p v-if="showError" class="text-xs text-red-500">Select at least one payer.</p>
    <p v-else-if="modelValue.length" class="text-xs text-gray-500">
      {{ t('shared.messages.selected') }}: {{ formatJobPayers(modelValue, t) }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {

  formatJobPayers,
  normalizeJobPayers,
} from '@/modules/job/utils/jobPayerUtils'
import getJobPayerOptions  from '@/modules/job/utils/jobPayerUtils'
import { useTranslate } from '@/shared/composables/useTranslate'


const { t } = useTranslate()

const JOB_PAYER_OPTIONS = getJobPayerOptions(t)

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: 'Payer',
  },
  required: {
    type: Boolean,
    default: true,
  },
  showValidation: {
    type: Boolean,
    default: false,
  },
  exclude: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

const selectedPayers = computed(() => normalizeJobPayers(props.modelValue))

const visibleOptions = computed(() => {
  const excluded = new Set(
    props.exclude.map((item) => String(item).trim().toLowerCase()).filter(Boolean)
  )
  return JOB_PAYER_OPTIONS.filter((option) => !excluded.has(option.id))
})

const showError = computed(() => props.showValidation && !selectedPayers.value.length)

const isSelected = (payerId) => selectedPayers.value.includes(payerId)

const toggle = (payerId) => {
  const next = isSelected(payerId)
    ? selectedPayers.value.filter((item) => item !== payerId)
    : [...selectedPayers.value, payerId]

  emit('update:modelValue', next)
}
</script>

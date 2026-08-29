<template>
  <div class="space-y-3">
    <div>
      <BaseLabel :for="inputId">{{ label }}</BaseLabel>
      <p v-if="hint" class="mt-0.5 text-xs text-slate-500">{{ hint }}</p>
    </div>

    <div
      v-if="existingUrls.length"
      class="flex flex-wrap gap-3"
    >
      <BaseImagePreview
        v-for="(url, index) in existingUrls"
        :key="`existing-${index}-${url}`"
        :src="url"
        :alt="`${t('journals.receipt')} ${index + 1}`"
        :show-cancel="false"
        width="100%"
        height="120px"
        class-name="rounded-md"
      />
    </div>

    <div
      v-if="receiptPreviews.length"
      class="flex flex-wrap gap-3"
    >
      <BaseImagePreview
        v-for="(preview, index) in receiptPreviews"
        :key="`new-${index}-${preview}`"
        :src="preview"
        :alt="`${t('journals.receipt')} ${existingUrls.length + index + 1}`"
        width="100%"
        height="120px"
        class-name="rounded-md"
        @cancelImage="removeReceiptAt(index)"
      />
    </div>

    <BaseFileInput
      :id="inputId"
      accept="image/jpeg,image/png,image/jpg"
      multiple
      :fileName="receiptFileLabel"
      :disabled="totalCount >= maxReceiptFiles"
      @change="handleReceiptFilesChange"
    />

    <div v-if="fileError" class="text-sm text-red-600">
      {{ fileError }}
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import BaseFileInput from '@/shared/components/base/BaseFileInput.vue'
import BaseImagePreview from '@/shared/components/base/BaseImagePreview.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'

const props = defineProps({
  inputId: {
    type: String,
    default: 'journal_receipt_path',
  },
  label: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  existingUrls: {
    type: Array,
    default: () => [],
  },
  maxReceiptFiles: {
    type: Number,
    default: 10,
  },
  receiptPath: {
    type: Array,
    default: () => [],
  },
  receiptPreview: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:receiptPath', 'update:receiptPreview'])

const { t } = useTranslate()

const maxReceiptBytes = 2 * 1024 * 1024
const fileError = ref('')

const receiptPreviews = computed(() =>
  Array.isArray(props.receiptPreview) ? props.receiptPreview : [],
)

const totalCount = computed(
  () => props.existingUrls.length + receiptPreviews.value.length,
)

const receiptFileLabel = computed(() => {
  const files = Array.isArray(props.receiptPath) ? props.receiptPath : []
  if (!files.length) return ''
  if (files.length === 1) return files[0].name
  return `${files.length} images selected`
})

function revokeBlobUrls(urls = []) {
  urls.forEach((url) => {
    if (typeof url === 'string' && url.startsWith('blob:')) {
      URL.revokeObjectURL(url)
    }
  })
}

function clearNewReceipts() {
  revokeBlobUrls(receiptPreviews.value)
  emit('update:receiptPath', [])
  emit('update:receiptPreview', [])
  fileError.value = ''
}

function removeReceiptAt(index) {
  const files = Array.isArray(props.receiptPath) ? [...props.receiptPath] : []
  const previews = Array.isArray(props.receiptPreview) ? [...props.receiptPreview] : []
  const [removedPreview] = previews.splice(index, 1)
  files.splice(index, 1)

  if (typeof removedPreview === 'string' && removedPreview.startsWith('blob:')) {
    URL.revokeObjectURL(removedPreview)
  }

  emit('update:receiptPath', files)
  emit('update:receiptPreview', previews)
  fileError.value = ''
}

function handleReceiptFilesChange(event) {
  const selected = Array.from(event.target.files || [])
  event.target.value = null

  if (!selected.length) return

  const current = Array.isArray(props.receiptPath) ? [...props.receiptPath] : []
  const previews = Array.isArray(props.receiptPreview) ? [...props.receiptPreview] : []
  const remaining = props.maxReceiptFiles - props.existingUrls.length - current.length

  if (remaining <= 0) {
    fileError.value = t('journals.receipt_max_files', { max: props.maxReceiptFiles })
    return
  }

  const accepted = []
  for (const file of selected.slice(0, remaining)) {
    if (!file.type.startsWith('image/')) {
      fileError.value = t('journals.receipt_invalid_type')
      return
    }
    if (file.size > maxReceiptBytes) {
      fileError.value = t('journals.receipt_file_too_large', { name: file.name })
      return
    }
    accepted.push(file)
  }

  accepted.forEach((file) => {
    current.push(file)
    previews.push(URL.createObjectURL(file))
  })

  emit('update:receiptPath', current)
  emit('update:receiptPreview', previews)
  fileError.value = ''

  if (selected.length > remaining) {
    fileError.value = t('journals.receipt_extra_skipped', { max: props.maxReceiptFiles })
  }
}

watch(
  () => [props.receiptPath, props.receiptPreview],
  ([paths, previews]) => {
    if (
      Array.isArray(paths) &&
      Array.isArray(previews) &&
      paths.length === 0 &&
      previews.length === 0
    ) {
      fileError.value = ''
    }
  },
)

onBeforeUnmount(() => {
  revokeBlobUrls(receiptPreviews.value)
})

defineExpose({ clearNewReceipts })
</script>

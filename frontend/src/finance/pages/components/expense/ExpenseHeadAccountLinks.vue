<template>
  <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
    <div class="flex items-center justify-between gap-3">
      <div class="min-w-0">
        <h4 class="text-sm font-semibold text-slate-900">Linked Account Types</h4>
        <p class="mt-0.5 text-xs text-slate-500">
          Select which account types are linked to this expense head.
        </p>
      </div>
      <BaseButton
        type="button"
        class="shrink-0 bg-primary text-white hover:bg-primary/90"
        :disabled="!canAddMore"
        @click="addLink"
      >
        <i class="fa fa-plus mr-1"></i> Add Account Type
      </BaseButton>
    </div>

    <div
      v-if="!localLinks.length"
      class="rounded-lg border border-dashed border-slate-300 bg-white px-4 py-3 text-center text-sm text-slate-500"
    >
      No account types linked yet. Click "Add Account Type" to link one.
    </div>

    <div v-if="localLinks.length" class="grid grid-cols-1 gap-3 md:grid-cols-2">
      <div
        v-for="(link, index) in localLinks"
        :key="link._key"
        class="flex items-end gap-3 rounded-lg border border-slate-200 bg-white p-3"
      >
        <div class="min-w-0 flex-1 space-y-1">
          <BaseLabel :for="`link_category_${index}`">Account Type</BaseLabel>
          <BaseSelect
            :id="`link_category_${index}`"
            :model-value="link.account_category"
            :options="getCategoryOptions(link.account_category)"
            placeholder="Select account type"
            @update:modelValue="(value) => setLinkCategory(index, value)"
          />
        </div>

        <button
          type="button"
          class="mb-2 shrink-0 rounded-lg px-3 py-2 text-sm text-red-600 transition-colors hover:bg-red-50"
          @click="removeLink(index)"
        >
          <i class="fa fa-trash"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import {
  createEmptyLinkedAccount,
  getAvailableAccountCategoryOptions,
} from '@/finance/data/expenseHeadAccountLinkData'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

function createKey() {
  return `link-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
}

function toLocalLinks(links) {
  const source = Array.isArray(links) ? links : []

  if (!source.length) {
    return []
  }

  return source.map((link, index) => ({
    account_category: link.account_category ?? '',
    _key: link._key || `saved-${link.account_category || index}-${createKey()}`,
  }))
}

const localLinks = ref(toLocalLinks(props.modelValue))

watch(
  () => props.modelValue,
  (value) => {
    const list = Array.isArray(value) ? value : []
    const saved = list.filter((link) => link.account_category)

    if (!saved.length && !list.length) {
      localLinks.value = []
      return
    }

    const draftRows = localLinks.value.filter((link) => !link.account_category)
    localLinks.value = [...toLocalLinks(saved), ...draftRows]
  },
  { deep: true }
)

const selectedCategories = computed(() =>
  localLinks.value.map((link) => link.account_category).filter(Boolean)
)

const canAddMore = computed(() => {
  const hasEmptyRow = localLinks.value.some((link) => !link.account_category)
  const availableCount = getAvailableAccountCategoryOptions(selectedCategories.value).length
  return !hasEmptyRow && availableCount > 0
})

function emitLinks() {
  emit(
    'update:modelValue',
    localLinks.value
      .filter((link) => link.account_category)
      .map(({ account_category }) => ({ account_category }))
  )
}

function getCategoryOptions(currentCategory) {
  return getAvailableAccountCategoryOptions(selectedCategories.value, currentCategory)
}

function addLink() {
  localLinks.value.push({
    ...createEmptyLinkedAccount(),
    _key: createKey(),
  })
}

function setLinkCategory(index, value) {
  localLinks.value[index].account_category = value
  emitLinks()
}

function removeLink(index) {
  localLinks.value.splice(index, 1)
  emitLinks()
}
</script>

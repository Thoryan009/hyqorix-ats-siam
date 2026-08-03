<template>
  <ScrollableLayout height="680px">
    <form @submit.prevent="onSubmit" class="space-y-5 p-1">
      <div class="rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div
          class="px-4 py-2.5 flex items-center gap-2"
          style="background: linear-gradient(90deg, #8b5cf6, #a855f7)"
        >
          <i class="fa fa-bolt text-white text-sm"></i>
          <span class="text-white text-sm font-semibold tracking-wide uppercase">
            Quick Module Permissions
          </span>
        </div>

        <div class="p-4 bg-white space-y-4">
          <div class="rounded-lg border border-violet-100 bg-violet-50 px-3 py-2 text-sm text-violet-800">
            Enter a module name (e.g. <strong>country</strong>) and we will auto-create
            <strong> create / edit / view / delete </strong> permissions.
          </div>

          <div class="space-y-1.5">
            <label
              for="module_name"
              class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
            >
              <i class="fa fa-cube text-violet-400"></i> Module Name
              <span class="text-red-400">*</span>
            </label>
            <BaseInput
              id="module_name"
              v-model="localForm.module"
              placeholder="Eg: country, agent, flight_summary"
              :required="true"
            />
          </div>

          <div class="space-y-1.5">
            <label
              for="module_label"
              class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
            >
              <i class="fa fa-tag text-violet-400"></i> Display Label (optional)
            </label>
            <BaseInput
              id="module_label"
              v-model="localForm.label"
              placeholder="Eg: Country"
            />
            <p class="text-xs text-gray-400">
              Used in permission names. Leave empty to use the module name.
            </p>
          </div>

          <div class="space-y-2">
            <p
              class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
            >
              <i class="fa fa-check-square-o text-violet-400"></i> Actions
            </p>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
              <label
                v-for="action in defaultActions"
                :key="action"
                class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50"
                :class="localForm.actions.includes(action) ? 'border-violet-300 bg-violet-50' : ''"
              >
                <input
                  type="checkbox"
                  class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                  :checked="localForm.actions.includes(action)"
                  @change="toggleAction(action, $event.target.checked)"
                />
                <span class="capitalize">{{ action }}</span>
              </label>
            </div>
          </div>

          <div class="space-y-1.5">
            <label
              for="extra_actions"
              class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
            >
              <i class="fa fa-plus-circle text-violet-400"></i> Extra Actions (optional)
            </label>
            <BaseInput
              id="extra_actions"
              v-model="localForm.extraActions"
              placeholder="Eg: export, view_summary, collect"
            />
            <p class="text-xs text-gray-400">Comma-separated. Example: export, view_summary</p>
          </div>

          <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
            <input
              v-model="localForm.assign_to_admin"
              type="checkbox"
              class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
            />
            Also assign these permissions to Admin role
          </label>
        </div>
      </div>

      <div
        v-if="previewItems.length"
        class="rounded-2xl border border-emerald-100 overflow-hidden shadow-sm"
      >
        <div
          class="px-4 py-2.5 flex items-center gap-2"
          style="background: linear-gradient(90deg, #059669, #10b981)"
        >
          <i class="fa fa-list text-white text-sm"></i>
          <span class="text-white text-sm font-semibold tracking-wide uppercase">
            Preview ({{ previewItems.length }})
          </span>
        </div>
        <div class="divide-y divide-gray-100 bg-white">
          <div
            v-for="item in previewItems"
            :key="item.slug"
            class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm"
          >
            <span class="font-medium text-gray-800">{{ item.name }}</span>
            <code class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{
              item.slug
            }}</code>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-2 pb-4">
        <BaseButton
          :className="'bg-yellow-700 hover:bg-yellow-800 text-white border border-gray-200 gap-2 cursor-pointer'"
          @click="onCancel"
        >
          <i class="fa fa-times"></i> Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading || !previewItems.length" class="gap-2">
          <span v-if="loading" class="flex items-center gap-2">
            <i class="fa fa-spinner fa-spin"></i> Creating...
          </span>
          <span v-else class="flex items-center gap-2">
            <i class="fa fa-magic"></i> Create {{ previewItems.length || '' }} Permissions
          </span>
        </BaseButton>
      </div>
    </form>
  </ScrollableLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const defaultActions = ['create', 'edit', 'view', 'delete']

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:formData'])

const localForm = ref({ ...props.formData })

watch(
  localForm,
  (newVal) => {
    emit('update:formData', newVal)
  },
  { deep: true }
)

watch(
  () => props.formData,
  (newVal) => {
    if (!newVal) return
    Object.assign(localForm.value, newVal)
  },
  { deep: true }
)

function toggleAction(action, checked) {
  const current = [...(localForm.value.actions || [])]
  if (checked) {
    if (!current.includes(action)) current.push(action)
  } else {
    const index = current.indexOf(action)
    if (index >= 0) current.splice(index, 1)
  }
  localForm.value.actions = current
}

function normalizeToken(value) {
  return String(value || '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9_]+/g, '_')
    .replace(/^_+|_+$/g, '')
}

const normalizedModule = computed(() => normalizeToken(localForm.value.module))

const displayLabel = computed(() => {
  const label = String(localForm.value.label || '').trim()
  if (label) return label
  return normalizedModule.value.replace(/_/g, ' ')
})

const resolvedActions = computed(() => {
  const selected = [...(localForm.value.actions || [])]
  const extras = String(localForm.value.extraActions || '')
    .split(',')
    .map((item) => normalizeToken(item))
    .filter(Boolean)

  return [...new Set([...selected, ...extras])]
})

const previewItems = computed(() => {
  if (!normalizedModule.value || !resolvedActions.value.length) return []

  const label = displayLabel.value
    .split(' ')
    .filter(Boolean)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')

  return resolvedActions.value.map((action) => {
    const actionLabel = action
      .split('_')
      .filter(Boolean)
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')

    return {
      name: `${actionLabel} ${label}`,
      slug: `${normalizedModule.value}.${action}`,
    }
  })
})
</script>

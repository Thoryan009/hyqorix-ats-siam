<template>
  <div ref="containerRef" class="relative md:mt-auto">
    <input
      :id="id"
      type="text"
      autocomplete="off"
      :value="inputValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="inputClass"
      @focus="handleFocus"
      @input="handleInput"
      @keydown.escape="closeDropdown"
      @keydown.enter.prevent="selectFirstMatch"
    />

    <input
      v-if="required"
      type="text"
      tabindex="-1"
      class="absolute opacity-0 h-0 w-0 pointer-events-none"
      :value="modelValue"
      required
      aria-hidden="true"
    />

    <ul
      v-if="!teleportDropdown && isOpen && filteredOptions.length"
      class="absolute z-50 mt-1 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-lg"
      :class="listClassName"
    >
      <li
        v-for="option in filteredOptions"
        :key="getValue(option)"
        class="cursor-pointer px-3 py-2 text-sm text-gray-800 hover:bg-indigo-50"
        @mousedown.prevent="selectOption(option)"
      >
        {{ getLabel(option) }}
      </li>
    </ul>

    <Teleport to="body" :disabled="!teleportDropdown">
      <ul
        v-if="teleportDropdown && isOpen && filteredOptions.length"
        ref="dropdownRef"
        class="fixed z-[9999] overflow-y-auto rounded-md border border-gray-300 bg-white shadow-lg"
        :class="listClassName"
        :style="dropdownStyle"
      >
        <li
          v-for="option in filteredOptions"
          :key="getValue(option)"
          class="cursor-pointer px-3 py-2 text-sm text-gray-800 hover:bg-indigo-50"
          @mousedown.prevent="selectOption(option)"
        >
          {{ getLabel(option) }}
        </li>
      </ul>
    </Teleport>

    <p
      v-if="!teleportDropdown && isOpen && searchQuery.trim() && !filteredOptions.length"
      class="absolute z-50 mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-500 shadow-lg"
    >
      No results found
    </p>

    <Teleport to="body" :disabled="!teleportDropdown">
      <p
        v-if="teleportDropdown && isOpen && searchQuery.trim() && !filteredOptions.length"
        ref="dropdownRef"
        class="fixed z-[9999] rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-500 shadow-lg"
        :style="dropdownStyle"
      >
        No results found
      </p>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: '',
  },
  optionLabel: {
    type: String,
    default: 'name',
  },
  optionValue: {
    type: String,
    default: 'id',
  },
  filterFn: {
    type: Function,
    default: null,
  },
  className: {
    type: String,
    default: '',
  },
  listClassName: {
    type: String,
    default: 'max-h-56',
  },
  teleportDropdown: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const containerRef = ref(null)
const dropdownRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const dropdownStyle = ref({})

const DROPDOWN_GAP = 4
const DROPDOWN_MAX_HEIGHT = 384

const inputClass = computed(() => [
  'w-full px-3 py-2 rounded-md border border-gray-300 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition',
  props.className,
  props.disabled && 'opacity-50 cursor-not-allowed bg-gray-100',
])

const getLabel = (option) => option?.[props.optionLabel] ?? option?.name ?? option?.label ?? ''
const getValue = (option) => option?.[props.optionValue] ?? option?.id ?? option?.value ?? ''

const normalizedOptions = computed(() => (Array.isArray(props.options) ? props.options : []))

const selectedOption = computed(() =>
  normalizedOptions.value.find((option) => String(getValue(option)) === String(props.modelValue)),
)

const defaultFilter = (option, query) => {
  const label = getLabel(option).toLowerCase()
  return label.includes(query)
}

const filteredOptions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  const filter = props.filterFn ?? defaultFilter

  if (!query) {
    return normalizedOptions.value
  }

  return normalizedOptions.value.filter((option) => filter(option, query))
})

const inputValue = computed(() => {
  if (isOpen.value) {
    return searchQuery.value
  }

  return selectedOption.value ? getLabel(selectedOption.value) : ''
})

const handleFocus = () => {
  if (props.disabled) return
  isOpen.value = true
  searchQuery.value = ''
  updateDropdownPosition()
}

const handleInput = (event) => {
  if (props.disabled) return
  isOpen.value = true
  searchQuery.value = event.target.value
  updateDropdownPosition()

  if (!searchQuery.value.trim()) {
    emit('update:modelValue', '')
  }
}

const selectOption = (option) => {
  emit('update:modelValue', getValue(option))
  searchQuery.value = ''
  isOpen.value = false
}

const selectFirstMatch = () => {
  if (filteredOptions.value.length) {
    selectOption(filteredOptions.value[0])
  }
}

const closeDropdown = () => {
  isOpen.value = false
  searchQuery.value = ''
}

const updateDropdownPosition = () => {
  if (!props.teleportDropdown || !containerRef.value) return

  const rect = containerRef.value.getBoundingClientRect()
  const spaceBelow = window.innerHeight - rect.bottom - DROPDOWN_GAP
  const spaceAbove = rect.top - DROPDOWN_GAP
  const openUpward = spaceBelow < 180 && spaceAbove > spaceBelow
  const availableSpace = openUpward ? spaceAbove : spaceBelow
  const maxHeight = Math.max(Math.min(DROPDOWN_MAX_HEIGHT, availableSpace), 160)

  dropdownStyle.value = {
    top: openUpward ? `${rect.top - maxHeight - DROPDOWN_GAP}px` : `${rect.bottom + DROPDOWN_GAP}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    maxHeight: `${maxHeight}px`,
  }
}

const handleClickOutside = (event) => {
  const inContainer = containerRef.value?.contains(event.target)
  const inDropdown = dropdownRef.value?.contains(event.target)
  if (!inContainer && !inDropdown) {
    closeDropdown()
  }
}

watch(
  () => props.modelValue,
  () => {
    if (!isOpen.value) {
      searchQuery.value = ''
    }
  },
)

watch(isOpen, (open) => {
  if (!props.teleportDropdown) return

  if (open) {
    updateDropdownPosition()
    window.addEventListener('scroll', updateDropdownPosition, true)
    window.addEventListener('resize', updateDropdownPosition)
    return
  }

  window.removeEventListener('scroll', updateDropdownPosition, true)
  window.removeEventListener('resize', updateDropdownPosition)
})

watch(filteredOptions, () => {
  if (isOpen.value && props.teleportDropdown) {
    updateDropdownPosition()
  }
})

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('scroll', updateDropdownPosition, true)
  window.removeEventListener('resize', updateDropdownPosition)
})
</script>

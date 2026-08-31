<template>
  <div>
    <button
      type="button"
      class="fixed bottom-6 right-6 z-40 inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-700 hover:shadow-xl"
      @click="open = true"
    >
      <i class="fa fa-comments"></i>
      <span>{{ t('journals.chat_open') }}</span>
    </button>

    <Teleport to="body">
      <Transition name="chat-backdrop">
        <div
          v-if="open"
          class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-[1px]"
          @click="open = false"
        />
      </Transition>

      <Transition name="chat-panel">
        <aside
          v-if="open"
          class="fixed inset-y-0 right-0 z-[60] flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl"
          role="dialog"
          aria-modal="true"
          :aria-label="t('journals.chat_title')"
        >
          <div class="flex items-start justify-between gap-3 border-b border-slate-200 bg-gradient-to-r from-indigo-50 to-white px-4 py-4">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">
                {{ t('journals.chat_kicker') }}
              </p>
              <h2 class="text-base font-semibold text-slate-900">{{ t('journals.chat_title') }}</h2>
              <p class="mt-0.5 text-xs text-slate-500">{{ t('journals.chat_subtitle') }}</p>
            </div>
            <button
              type="button"
              class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"
              :aria-label="t('journals.chat_close')"
              @click="open = false"
            >
              <i class="fa fa-times"></i>
            </button>
          </div>

          <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
              <div class="flex items-center justify-between gap-2">
                <div>
                  <p class="text-xs font-semibold text-slate-900">
                    {{ t('journals.chat_presets_title') }}
                  </p>
                  <p class="mt-0.5 text-[11px] text-slate-500">
                    {{ t('journals.chat_presets_count', { count: visiblePresetCount }) }}
                  </p>
                </div>
                <button
                  type="button"
                  class="rounded-md border border-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50"
                  @click="showPresets = !showPresets"
                >
                  {{ showPresets ? t('journals.chat_presets_hide') : t('journals.chat_presets_show') }}
                </button>
              </div>

              <div v-if="showPresets" class="mt-3 space-y-3">
                <div class="relative">
                  <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                  <input
                    v-model="presetSearch"
                    type="search"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-8 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                    :placeholder="t('journals.chat_presets_search')"
                  />
                  <button
                    v-if="presetSearch"
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    :aria-label="t('journals.chat_presets_clear_search')"
                    @click="presetSearch = ''"
                  >
                    <i class="fa fa-times text-xs"></i>
                  </button>
                </div>

                <p class="text-[11px] text-slate-500">
                  {{ t('journals.chat_presets_hint') }}
                </p>

                <div v-if="!filteredPresetGroups.length" class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-center">
                  <p class="text-xs font-medium text-slate-700">{{ t('journals.chat_presets_no_results') }}</p>
                  <p class="mt-1 text-[11px] text-slate-500">{{ t('journals.chat_presets_no_results_hint') }}</p>
                </div>

                <div v-else class="max-h-56 space-y-2.5 overflow-y-auto pr-1">
                  <section
                    v-for="group in filteredPresetGroups"
                    :key="group.id"
                    class="overflow-hidden rounded-lg border border-slate-200"
                    :class="categoryMeta[group.id]?.sectionClass"
                  >
                    <div
                      class="flex items-center gap-2 border-b px-3 py-2"
                      :class="categoryMeta[group.id]?.headerClass"
                    >
                      <span
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[11px]"
                        :class="categoryMeta[group.id]?.iconClass"
                      >
                        <i class="fa" :class="categoryMeta[group.id]?.icon"></i>
                      </span>
                      <p class="min-w-0 flex-1 text-xs font-semibold text-slate-800">
                        {{ t(group.categoryKey) }}
                      </p>
                      <span
                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="categoryMeta[group.id]?.badgeClass"
                      >
                        {{ group.items.length }}
                      </span>
                    </div>

                    <ul class="divide-y divide-slate-100 bg-white">
                      <li v-for="item in group.items" :key="item.id">
                        <button
                          type="button"
                          class="group flex w-full items-start gap-2 px-3 py-2.5 text-left transition hover:bg-indigo-50/70"
                          :title="t('journals.chat_preset_use')"
                          @click="usePreset(item.text)"
                        >
                          <span
                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[10px] text-slate-500 group-hover:border-indigo-200 group-hover:bg-indigo-100 group-hover:text-indigo-700"
                          >
                            <i class="fa fa-plus"></i>
                          </span>
                          <span class="min-w-0 flex-1 text-xs leading-relaxed text-slate-700 group-hover:text-indigo-950">
                            {{ item.text }}
                          </span>
                          <i class="fa fa-chevron-right mt-0.5 shrink-0 text-[10px] text-slate-300 group-hover:text-indigo-400"></i>
                        </button>
                      </li>
                    </ul>
                  </section>
                </div>
              </div>
            </div>
          </div>

          <div ref="messagesEl" class="flex-1 space-y-3 overflow-y-auto px-4 py-4">
            <div
              v-if="!messages.length"
              class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center"
            >
              <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                <i class="fa fa-lightbulb-o"></i>
              </div>
              <p class="text-sm font-medium text-slate-800">{{ t('journals.chat_empty_title') }}</p>
              <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ t('journals.chat_empty_hint') }}</p>
            </div>

            <div
              v-for="(message, index) in messages"
              :key="`${index}-${message.role}`"
              class="flex"
              :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
            >
              <div
                class="max-w-[92%] rounded-2xl px-3.5 py-2.5 text-sm leading-relaxed whitespace-pre-wrap"
                :class="
                  message.role === 'user'
                    ? 'bg-indigo-600 text-white'
                    : 'border border-slate-200 bg-white text-slate-800 shadow-sm'
                "
              >
                {{ message.content }}
              </div>
            </div>

            <div v-if="isPending" class="flex justify-start">
              <div class="rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-500 shadow-sm">
                <i class="fa fa-spinner fa-spin mr-1.5"></i>
                {{ t('journals.chat_thinking') }}
              </div>
            </div>

            <p v-if="errorMessage" class="text-xs text-red-600">{{ errorMessage }}</p>
          </div>

          <div class="border-t border-slate-200 bg-white p-4">
            <BaseTextArea
              v-model="draft"
              :rows="3"
              className="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm leading-relaxed text-slate-800 placeholder:text-slate-400 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
              :placeholder="t('journals.chat_input_placeholder')"
              @keydown.enter.exact.prevent="sendMessage"
            />
            <div class="mt-2 flex items-center justify-between gap-2">
              <button
                type="button"
                class="text-xs font-medium text-slate-500 hover:text-slate-700"
                :disabled="!messages.length || isPending"
                @click="clearChat"
              >
                {{ t('journals.chat_clear') }}
              </button>
              <BaseButton
                className="bg-indigo-600 text-white hover:bg-indigo-700"
                :disabled="!canSend"
                @click="sendMessage"
              >
                <i v-if="!isPending" class="fa fa-paper-plane mr-1.5"></i>
                <i v-else class="fa fa-spinner fa-spin mr-1.5"></i>
                {{ isPending ? t('journals.chat_sending') : t('journals.chat_send') }}
              </BaseButton>
            </div>
          </div>
        </aside>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { fetchEntryChat } from '../../services/journalService'
import {
  buildPresetChatPrompt,
  recruitmentJournalNarrationPresets,
} from '../../data/recruitmentJournalNarrationPresets'

const { t } = useTranslate()

const open = ref(false)
const showPresets = ref(true)
const presetSearch = ref('')
const draft = ref('')
const messages = ref([])
const errorMessage = ref('')
const isPending = ref(false)
const messagesEl = ref(null)

const presetGroups = recruitmentJournalNarrationPresets

const categoryMeta = {
  capital_assets: {
    icon: 'fa-bank',
    sectionClass: 'border-indigo-100',
    headerClass: 'border-indigo-100 bg-indigo-50/70',
    iconClass: 'bg-indigo-100 text-indigo-700',
    badgeClass: 'bg-indigo-100 text-indigo-700',
  },
  client_commission: {
    icon: 'fa-handshake-o',
    sectionClass: 'border-blue-100',
    headerClass: 'border-blue-100 bg-blue-50/70',
    iconClass: 'bg-blue-100 text-blue-700',
    badgeClass: 'bg-blue-100 text-blue-700',
  },
  medical_tickets: {
    icon: 'fa-medkit',
    sectionClass: 'border-teal-100',
    headerClass: 'border-teal-100 bg-teal-50/70',
    iconClass: 'bg-teal-100 text-teal-700',
    badgeClass: 'bg-teal-100 text-teal-700',
  },
  principal_visa: {
    icon: 'fa-plane',
    sectionClass: 'border-violet-100',
    headerClass: 'border-violet-100 bg-violet-50/70',
    iconClass: 'bg-violet-100 text-violet-700',
    badgeClass: 'bg-violet-100 text-violet-700',
  },
  staff: {
    icon: 'fa-users',
    sectionClass: 'border-amber-100',
    headerClass: 'border-amber-100 bg-amber-50/70',
    iconClass: 'bg-amber-100 text-amber-700',
    badgeClass: 'bg-amber-100 text-amber-700',
  },
  agent_candidate: {
    icon: 'fa-user-plus',
    sectionClass: 'border-emerald-100',
    headerClass: 'border-emerald-100 bg-emerald-50/70',
    iconClass: 'bg-emerald-100 text-emerald-700',
    badgeClass: 'bg-emerald-100 text-emerald-700',
  },
  consultancy_finance: {
    icon: 'fa-line-chart',
    sectionClass: 'border-cyan-100',
    headerClass: 'border-cyan-100 bg-cyan-50/70',
    iconClass: 'bg-cyan-100 text-cyan-700',
    badgeClass: 'bg-cyan-100 text-cyan-700',
  },
  expenses_accruals: {
    icon: 'fa-file-text-o',
    sectionClass: 'border-rose-100',
    headerClass: 'border-rose-100 bg-rose-50/70',
    iconClass: 'bg-rose-100 text-rose-700',
    badgeClass: 'bg-rose-100 text-rose-700',
  },
}

const filteredPresetGroups = computed(() => {
  const query = presetSearch.value.trim().toLowerCase()

  return presetGroups
    .map((group) => {
      const categoryLabel = t(group.categoryKey).toLowerCase()
      const items = group.items.filter((item) => {
        if (!query) return true
        return (
          item.text.toLowerCase().includes(query) ||
          categoryLabel.includes(query) ||
          item.id.toLowerCase().includes(query)
        )
      })

      return { ...group, items }
    })
    .filter((group) => group.items.length > 0)
})

const visiblePresetCount = computed(() =>
  filteredPresetGroups.value.reduce((sum, group) => sum + group.items.length, 0),
)

const canSend = computed(() => !isPending.value && String(draft.value || '').trim() !== '')

watch(open, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

onBeforeUnmount(() => {
  document.body.style.overflow = ''
})

watch(
  () => messages.value.length,
  async () => {
    await nextTick()
    if (messagesEl.value) {
      messagesEl.value.scrollTop = messagesEl.value.scrollHeight
    }
  },
)

function usePreset(narration) {
  draft.value = buildPresetChatPrompt(narration, t)
}

async function sendMessage() {
  const content = String(draft.value || '').trim()
  if (!content || isPending.value) return

  errorMessage.value = ''
  const nextMessages = [...messages.value, { role: 'user', content }]
  messages.value = nextMessages
  draft.value = ''
  isPending.value = true

  try {
    const reply = await fetchEntryChat({ messages: nextMessages })
    messages.value = [...nextMessages, { role: 'assistant', content: reply }]
  } catch (error) {
    errorMessage.value = error?.message || t('journals.chat_failed')
    messages.value = nextMessages
  } finally {
    isPending.value = false
  }
}

function clearChat() {
  messages.value = []
  errorMessage.value = ''
  draft.value = ''
}
</script>

<style scoped>
.chat-backdrop-enter-active,
.chat-backdrop-leave-active {
  transition: opacity 0.25s ease;
}

.chat-backdrop-enter-from,
.chat-backdrop-leave-to {
  opacity: 0;
}

.chat-panel-enter-active,
.chat-panel-leave-active {
  transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.chat-panel-enter-from,
.chat-panel-leave-to {
  transform: translateX(100%);
}
</style>

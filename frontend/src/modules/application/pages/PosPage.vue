<template>
  <SectionHeader>
    <div class="grid grid-cols-12 gap-4">
      <!-- LEFT SIDE -->
      <div class="col-span-12 md:col-span-9">
        <div class="rounded-lg px-3 py-3 md:py-6 shadow-sm">
          <!-- Search component -->

          <SearchBar v-model="searchQuery" />

          <div v-if="posTransactions.length === 0" class="py-4 md:py-12 text-center text-gray-500">
            <p class="text-lg">No results found. Start typing to search...</p>
          </div>

          <div class="space-y-3" v-if="!isLoading">
            <div
              v-for="app in posTransactions"
              :key="app.id"
              class="cursor-pointer rounded-lg transition-all"
              :class="
                selectedItem?.id === app.id
                  ? 'border-2 border-primary bg-primary-light'
                  : 'hover:shadow-md'
              "
              @click="selectItem(app)"
            >
              <!-- Candidate Details component - Full Row (top part of card) -->
              <CandidateDetails :app="app" />
              <!-- Transaction History component - Full Row (bottom part of card) -->
              <TransactionHistory :transactions="app.transaction_history" />
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT SIDE -->
      <div class="col-span-12 md:col-span-3">
        <div class="sticky top-0 rounded-lg bg-white p-6 shadow-sm">
          <!-- Pos Selected Item component -->
          <PosSelectedItem :selectedItem="selectedItem" @clear="handleReset" />

          <!-- Pos Terminal component -->
          <PosTerminal :selectedItem="selectedItem" @clear="handleReset" />
        </div>
      </div>

      <PaymentSucessModal />
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

import SearchBar from './posParts/SearchBar.vue'
import CandidateDetails from './posParts/CandidateDetails.vue'
import TransactionHistory from './posParts/TransactionHistory.vue'
import PosSelectedItem from './posParts/PosSelectedItem.vue'
import PosTerminal from './posParts/PosTerminal.vue'
import { usePosQuery } from '../queries/useTransactionsQuery'
import PaymentSucessModal from './posParts/PaymentReceiptModal.vue'
import { useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'

const route = useRoute()

const applicationId = route.params.applicationId || null

const searchQuery = ref('')
const selectedItem = ref(null)

const { data, isLoading } = usePosQuery(searchQuery)

const selectItem = (app) => {
  selectedItem.value = { ...app }
}

const handleReset = () => {
  selectedItem.value = null
  searchQuery.value = ''
}

const posTransactions = computed(() => data.value?.data?.data ?? [])

watch(
  () => applicationId,
  (newAppId) => {
    if (newAppId) {
      searchQuery.value = newAppId
    }
  },
  { immediate: true },
)
</script>

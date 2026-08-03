<template>
  <SectionHeader>
    <ExpensePaymentFormPanel
      :initial-category-id="routeCategoryId"
      :initial-head-id="routeHeadId"
    />
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import ExpensePaymentFormPanel from './components/payment/ExpensePaymentFormPanel.vue'

const route = useRoute()

const routeCategoryId = computed(() => route.query.category_id ?? '')
const routeHeadId = computed(() => route.query.head_id ?? '')
</script>

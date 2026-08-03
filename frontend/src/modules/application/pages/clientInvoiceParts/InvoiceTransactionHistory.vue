<template>
  <div
    class="my-2"
    v-if="transaction && transaction.transactions && transaction.transactions.length"
  >
    <table
      class="w-full text-sm border border-gray-300 rounded-lg overflow-hidden mb-2"
    >
      <!-- Table Head -->
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 text-left">SL</th>
          <th class="p-2 text-left">Applicant Name</th>
          <th class="p-2 text-left">Job Name</th>
          <th class="p-2 text-left">Job Code</th>
          <th class="p-2 text-left">Candidate Price</th>
          <th class="p-2 text-left">Due Amount</th>
        </tr>
      </thead>

      <!-- Table Body -->
      <tbody>
        <tr
          v-for="(app, index) in transaction.transactions"
          :key="app.application_id"
          class="border-t border-gray-200 hover:bg-gray-50"
        >
          <td class="p-2">{{ index + 1 }}</td>
          <td class="p-2">{{ app.candidate_name }}</td>
          <td class="p-2">{{ app.applied_position }}</td>
          <td class="p-2">{{ app.applied_position_code }}</td>
          <td class="p-2">{{ app.total_amount }}</td>
          <td class="p-2">{{ app.due_amount }}</td>
        </tr>
      </tbody>

      <!-- Table Footer -->
      <tfoot class="bg-gray-100 font-semibold border-t border-gray-300">
        <tr>
          <td colspan="6" class="p-2 text-right">
            Total Due Amount:
          </td>
          <td class="p-2 text-left">
            ${{ transaction.total_amount }}
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- Fallback -->
  <div v-else class="text-gray-500 text-sm">
    No transactions available.
  </div>
</template>

<script setup>
defineProps({
  transaction: {
    type: Object,
    required: true,
  },
})
</script>

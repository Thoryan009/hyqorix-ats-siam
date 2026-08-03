<template>
  <div v-if="selectedRows.length" class="overflow-x-auto">
    <div v-for="jobGroup in groupedSelectedRows" :key="jobGroup.job_id">
      <div class="flex items-center gap-x-2 my-2">
        <h4 class="text-base font-semibold text-gray-800">{{ jobGroup.job }}</h4>
        <span class="bg-blue-50 text-blue-600 text-sm font-medium px-3 py-1 rounded-full">
          {{ jobGroup.applications.length }}
        </span>
      </div>

      <table class="w-full text-sm border border-gray-300 rounded-lg overflow-hidden mb-4">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 text-left">Bill No</th>
            <th class="p-2 text-left">Application ID</th>
            <th class="p-2 text-left">Applicant Name</th>
            <th class="p-2 text-left">Total Amount</th>
            <th class="p-2 text-left">Due Amount</th>
            <th class="p-2 text-left">Payer</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="app in jobGroup.applications"
            :key="app.application_id"
            class="border-t border-gray-200 hover:bg-gray-50"
          >
            <td class="p-2">{{ app.bill_no ?? 'N/A' }}</td>
            <td class="p-2">{{ app.payer_application_id }}</td>
            <td class="p-2">{{ app.payer_name }}</td>
            <td class="p-2">{{ app.total_amount }}</td>
            <td class="p-2">{{ app.due_amount }}</td>
            <td class="p-2">{{ app.payer }}</td>
          </tr>
        </tbody>
      </table>
    </div>


  </div>
</template>

<script setup>

defineProps({
  selectedRows: { type: Array, required: true },
  groupedSelectedRows: { type: Array, required: true },
})

</script>

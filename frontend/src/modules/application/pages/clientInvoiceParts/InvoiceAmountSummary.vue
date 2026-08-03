<template>
  <div class="mb-2">
    <table class="w-full border-collapse text-sm">
      <tbody>
        <tr class="font-semibold border-b border-gray-200">
          <td class="py-2 px-3 text-left">Total Due Amount:</td>
          <td class="py-2 px-3 text-right">
            ${{ transaction.total_amount }}
          </td>
        </tr>
      </tbody>
    </table>
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

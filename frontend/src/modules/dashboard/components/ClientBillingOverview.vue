<template>
  <div
    class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 hover:shadow-2xl transition-all duration-300"
  >
    <!-- Header with gradient -->
    <div class="bg-primary px-4 py-2.5 flex items-center gap-2 cursor-pointer" @click="$router.push('/applications/client-bills')">
      <div
        class="w-7 h-7 rounded-md bg-white/20 backdrop-blur-sm flex items-center justify-center text-white"
      >
        <i class="fa fa-money text-sm"></i>
      </div>
      <h2 class="text-sm font-semibold text-white">Client Billing Overview</h2>
    </div>

    <!-- Content -->
    <div class="p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="(card, index) in billingCards"
          :key="card.title"
          class="group p-5 rounded-xl border-2 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer"
          :class="card.bgClass"
          :style="{ animationDelay: `${index * 50}ms` }"
          @click="$router.push('/applications/client-bills')"
        >
          <div class="flex items-center justify-between mb-2">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center shadow-md group-hover:scale-110 transition-transform"
              :class="card.iconBgClass"
            >
              <i :class="card.icon + ' text-white'"></i>
            </div>
            <span class="text-xs font-semibold text-black bg-white px-2 py-1 rounded-full">
              {{ card.badge }}
            </span>
          </div>
          <p class="text-sm font-semibold text-black mb-1">{{ card.title }}</p>
          <p class="text-3xl font-bold text-black">{{ card.value }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClientBillingOverview',
  props: {
    client_billing: {
      type: Object,
    },
  },
  computed: {
    billingCards() {
      return [
        {
          title: 'Invoice Generated',
          value: this.client_billing?.invoice_generated,
          icon: 'fa fa-file-text',
          bgClass: 'bg-gradient-to-br from-tertiary/5 to-tertiary/10 border-2 border-tertiary/30',
          iconBgClass: 'bg-gradient-to-br from-tertiary to-tertiary/70',
          badge: 'Active',
          badgeClass: 'text-tertiary',
          textClass: 'text-tertiary',
        },
        {
          title: 'Invoice Sent',
          value: this.client_billing?.invoice_sent,
          icon: 'fa fa-paper-plane',
          bgClass: 'bg-gradient-to-br from-primary/20 to-primary/10 border-primary/30',
          iconBgClass: 'bg-gradient-to-br from-primary to-primary',
          badge: 'Sent',
          badgeClass: 'text-primary',
          textClass: 'text-primary',
        },
        {
          title: 'Paid',
          value: this.client_billing?.paid,
          icon: 'fa fa-check-circle',
          bgClass: 'bg-gradient-to-br from-primary/20 to-primary/10 border-primary/30',
          iconBgClass: 'bg-gradient-to-br from-primary to-primary',
          badge: '✓ Done',
          badgeClass: 'text-primary',
          textClass: 'text-primary',
        },
        {
          title: 'Cancelled',
          value: this.client_billing?.cancelled,
          icon: 'fa fa-times-circle',
          bgClass: 'bg-gradient-to-br from-secondary/5 to-secondary/10 border-2 border-secondary/30',
          iconBgClass: 'bg-gradient-to-br from-secondary to-secondary/70',
          badge: '✗ Cancel',
          badgeClass: 'text-secondary',
          textClass: 'text-secondary',
        },
      ]
    },
  },
}
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.group {
  animation: slideIn 0.5s ease-out forwards;
}
</style>

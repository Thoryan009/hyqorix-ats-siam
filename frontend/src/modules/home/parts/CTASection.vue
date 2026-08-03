<template>
  <section class="bg-gradient-to-r from-[#10B981] to-[#059669] px-4 py-20 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl text-center">
      <h2 class="text-3xl font-bold text-white sm:text-4xl mb-6">
        Ready to Transform Your Recruitment Agency?
      </h2>
      <p class="text-xl text-green-100 mb-8">
        Join leading overseas employment agencies who trust MERCHANT OVERSEAS ATS to manage
        thousands of candidates across 13 process stages with complete automation
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button
          @click="emit('onLogin')"
          class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#10B981] shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-200 hover:bg-gray-50"
        >
          Get Started
          <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 7l5 5m0 0l-5 5m5-5H6"
            />
          </svg>
        </button>
        <button
          class="inline-flex items-center justify-center rounded-xl border-2 border-white bg-transparent px-8 py-4 text-base font-semibold text-white hover:bg-white hover:text-[#10B981] transition-all duration-200"
        >
          Schedule a Demo
        </button>
      </div>
      <p class="mt-6 text-sm text-green-100">
        One-time purchase • Lifetime license • 24/7 Support included
      </p>
    </div>
  </section>
</template>

<script setup>
const emit = defineEmits(['onLogin'])
</script>

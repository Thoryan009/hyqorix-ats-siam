<template>

 <footer class="flex h-16 justify-between items-center px-4  text-sm bg-white text-black">
   <div>

      <router-link :to="{ name: 'Software Info' }" class="flex items-center ">
        <img src="/hyqorix-logo.png" alt="Company Logo" class="h-6 w-6 inline-block mr-2" />
        <span>Hyqorix ATS</span>
      </router-link>
      <!-- <img :src="settingsData.company_logo_url || '/favicon.ico'" alt="Company Logo" class="h-6 w-6 inline-block mr-2" />
      <span>{{ settingsData.software_name || 'ATS' }}</span> -->
    <p></p>
  </div>
   <div><p> © {{ currentYear }} All rights reserved.</p></div>
   <div><p> Powered By <span class="font-bold text-lg text-[#1D70B8]"><a href="https://www.norqel.com/" target="_blank" rel="noopener noreferrer">Norqel Technologies</a></span></p></div>
</footer>
</template>
<script setup>
  // import { useSettingsQuery } from '@/modules/home/queries/useSettingsQuery';
  // import { computed } from 'vue';

  console.log('Hello from Footer.vue')

  // const { data, isLoading } = useSettingsQuery(1)

  const currentYear = new Date().getFullYear()
  // const settingsData = computed(() => data.value?.data || {})

// const companyName = computed(() => settingsData.value?.company_name || 'Norqel Technologies')
</script>

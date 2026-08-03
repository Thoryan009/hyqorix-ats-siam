<template>
  <div class="min-h-screen bg-gradient-to-br from-primary/10 via-white to-primary/10">
    <!-- Navigation Header -->
    <Navbar :settingsData="settingsData" />

    <!-- Hero Section -->
    <HeroSection @onLogin="handleLogin" />

    <!-- Features Section -->
    <FeaturesSection />

    <!-- Benefits Section -->
    <BenefitsSection />

    <!-- CTA Section -->
    <!-- <CTASection @onLogin="handleLogin" /> -->

    <!-- Footer -->
    <Footer :settingsData="settingsData" />
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

import HeroSection from '../parts/HeroSection.vue'
import FeaturesSection from '../parts/FeaturesSection.vue'
import BenefitsSection from '../parts/BenefitsSection.vue'
import Footer from '../parts/Footer.vue'
import Navbar from '../parts/Navbar.vue'
import { useSettingsQuery } from '../queries/useSettingsQuery'
import { computed } from 'vue'


const router = useRouter()


const { data: settingsDataResponse } = useSettingsQuery()
const settingsData = computed(() => settingsDataResponse?.value?.data || {})

const handleLogin = () => {
  router.push('/login')
}
</script>

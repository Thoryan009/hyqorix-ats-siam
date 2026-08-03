import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { registerGlobalComponents } from '@/registerGlobalComponents'
import queryClient from '@/config/queryClient'

import { VueQueryPlugin } from '@tanstack/vue-query'

// --- toast ---
import Toast from 'vue-toastification'
import { toastOptions } from '@/shared/config/toastConfig'

import 'vue-toastification/dist/index.css'
import { useAuthStore } from './modules/auth/store/authStore'
import i18n from './localization'
import canDirective from '@/shared/directives/can'

// --- / toast ---

const app = createApp(App)

registerGlobalComponents(app)

app.use(createPinia())
app.use(router).use(Toast, toastOptions)

app.use(VueQueryPlugin, {
  queryClient,
})

app.use(i18n)
app.directive('can', canDirective)
app.mount('#app')

const authStore = useAuthStore()
authStore.initAuth()

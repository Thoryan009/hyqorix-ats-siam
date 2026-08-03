
<template>
  <AuthLayout>
    <div class="text-center mb-8">
      <AuthTitle>{{ app.name }}</AuthTitle>
      <AuthSubTitle>Enter your email to reset password</AuthSubTitle>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-6">

      <!-- Email -->
      <div>
        <BaseLabel for="email">Email Address</BaseLabel>
        <BaseInput
          id="email"
          v-model="email"
          type="email"
          placeholder="admin@example.com"
        />
      </div>

      <!-- Submit -->
      <AuthButton :disabled="auth.loading">
        {{ auth.loading ? "Sending..." : "Send Reset Link" }}
      </AuthButton>

      <!-- Success / Error Messages -->
      <p v-if="success" class="text-center text-sm text-green-600">
        Password reset link sent successfully!
      </p>

      <p v-if="errorMsg" class="text-center text-sm text-red-600">
        {{ errorMsg }}
      </p>

      <!-- Back to Login -->
      <div class="text-center text-sm text-gray-600">
        Remembered your password?
        <router-link
          to="/login"
          class="text-blue-600 font-semibold hover:underline ml-1"
        >
          Login here
        </router-link>
      </div>

    </form>
  </AuthLayout>
</template>
<script setup>
import { ref } from "vue"
import { useAuthStore } from "../store/authStore"
import app from "../../../shared/config/appConfig"
import AuthTitle from "../shared/components/ui/AuthTitle.vue"
import AuthSubTitle from "../shared/components/ui/AuthSubTitle.vue"
import AuthButton from "../shared/components/ui/AuthButton.vue"
import AuthLayout from "../shared/components/layout/AuthLayout.vue"
import BaseLabel from "@/shared/components/base/BaseLabel.vue"
import BaseInput from "@/shared/components/base/BaseInput.vue"

const auth = useAuthStore()
const email = ref("admin@gmail.com")
const success = ref(false)
const errorMsg = ref(null)

const submit = async () => {
  errorMsg.value = null
  try {
    await auth.forgotPassword(email.value)
    success.value = true
    alert("Password reset link sent to your email!")
  } catch (error) {
    errorMsg.value = error.response?.data?.message || "Failed to send reset link"
    alert(errorMsg.value)
    console.error(error)
  }
}
</script>
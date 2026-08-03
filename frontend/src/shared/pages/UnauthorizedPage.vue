<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8 text-center">

      <!-- Icon -->
      <div class="flex justify-center mb-6">
        <div class="w-20 h-20 flex items-center justify-center rounded-full bg-red-100">
          <svg
            class="w-10 h-10 text-red-600"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"
            />
          </svg>
        </div>
      </div>

      <!-- Title -->
      <h1 class="text-2xl font-bold text-gray-800 mb-2">
        Access Denied
      </h1>

      <!-- Message -->
      <p class="text-gray-600 mb-6">
        You don’t have permission to view this page.
        Please contact your administrator if you think this is a mistake.
      </p>

      <!-- Buttons -->
      <div class="flex gap-3 justify-center">
        <button
          @click="$router.back()"
          class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
        >
          Go Back
        </button>

        <router-link
          to="/dashboard"
          class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
        >
          Dashboard
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// No logic needed — simple UI page
</script>

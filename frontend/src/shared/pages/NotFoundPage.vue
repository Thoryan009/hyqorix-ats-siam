<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8 text-center">

      <!-- Icon -->
      <div class="flex justify-center mb-6">
        <div class="w-24 h-24 flex items-center justify-center rounded-full bg-green-100">
          <svg
            class="w-12 h-12 text-green-600"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"
            />
          </svg>
        </div>
      </div>

      <!-- Title -->
      <h1 class="text-6xl font-bold text-gray-800 mb-2">
        404
      </h1>
      
      <h2 class="text-2xl font-semibold text-gray-700 mb-4">
        Page Not Found
      </h2>

      <!-- Message -->
      <p class="text-gray-600 mb-8">
        Oops! The page you're looking for doesn't exist. 
        It might have been moved or deleted.
      </p>

      <!-- Buttons -->
      <div class="flex gap-3 justify-center">
        <button
          @click="$router.back()"
          class="px-6 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors duration-200 font-medium"
        >
          Go Back
        </button>

        <router-link
          to="/"
          class="px-6 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors duration-200 font-medium"
        >
          Go Home
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// 404 Not Found Page
</script>

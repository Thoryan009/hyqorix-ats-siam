<template>

<section class="min-h-191 flex items-center justify-center p-6" >

  <div class="bg-white rounded-2xl shadow-xl flex overflow-hidden max-w-8xl w-full">

    <!-- Left: Image -->
    <div class="w-56 flex-shrink-0 flex-1 items-center justify-center p-6 bg-green-100  " >
      <img
        src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=300&q=80&auto=format&fit=crop"
        alt="ERP Dashboard"
        class="rounded-xl w-full object-cover shadow-sm"
        style="aspect-ratio: 4/3;"
      />
    </div>

    <!-- Right: Content --> 
    <div class="flex-1 p-7 relative">

      <!-- Close Button -->
      <!-- <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-lg leading-none">
        <i class="fa fa-times"></i>
      </button> -->

      <h2 class="text-2xl font-bold text-gray-800 mb-5">About This Software</h2>

      <div class="space-y-3">

        <!-- Software -->
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8edf5;">
            <i class="fa fa-desktop text-green-500 text-sm"></i>
          </span>
          <span class="text-gray-500 w-28 text-sm">Software:</span>
          <span class="text-gray-800 text-sm font-medium">Hyqorix ATS</span>
        </div>

        <!-- Version -->
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8edf5;">
            <i class="fa fa-code text-green-500 text-sm"></i>
          </span>
          <span class="text-gray-500 w-28 text-sm">Version:</span>
          <span class="text-gray-800 text-sm font-medium">26.1</span>
        </div>

        <!-- Developed by -->
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8edf5;">
            <i class="fa fa-building text-green-500 text-sm"></i>
          </span>
          <span class="text-gray-500 w-28 text-sm">Developed by:</span>
          <span class="text-gray-800 text-sm font-medium">Norqel Technologies.</span>
        </div>

        <!-- Website -->
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8edf5;">
            <i class="fa fa-globe text-green-500 text-sm"></i>
          </span>
          <span class="text-gray-500 w-28 text-sm">Website:</span>
          <a href="https://www.norqel.com/" class="text-green-500 text-sm hover:underline">
            https://www.norqel.com/
          </a>
        </div>

        <!-- Copyright -->
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8edf5;">
            <i class="fa fa-copyright text-green-500 text-sm"></i>
          </span>
          <span class="text-gray-500 w-28 text-sm">Copyright:</span>
          <span class="text-gray-800 text-sm font-medium">© 2026 Norqel Technologies.</span>
        </div>

      </div>

      <!-- Description -->
      <p class="mt-5 text-gray-500 text-sm leading-relaxed border-t border-gray-100 pt-4">
        ERP Management System is a powerful solution to manage your business operations efficiently.
      </p>

    </div>
  </div>

</section>

</template>

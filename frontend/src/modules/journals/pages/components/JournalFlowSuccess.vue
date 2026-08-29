<template>
  <div class="journal-flow-success relative mx-auto w-full max-w-2xl overflow-hidden py-6 sm:py-10">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
      <span
        v-for="particle in particles"
        :key="particle.id"
        class="success-particle absolute rounded-full bg-primary/30"
        :style="{
          left: particle.left,
          top: particle.top,
          width: particle.size,
          height: particle.size,
          animationDelay: particle.delay,
          animationDuration: particle.duration,
          opacity: particle.opacity,
        }"
      />
      <span class="success-ring absolute left-1/2 top-24 h-40 w-40 -translate-x-1/2 rounded-full border-2 border-primary/25" />
      <span class="success-ring success-ring-delay absolute left-1/2 top-24 h-40 w-40 -translate-x-1/2 rounded-full border-2 border-primary/15" />
    </div>

    <div
      class="success-card relative rounded-3xl border border-slate-200/80 bg-white px-6 py-10 text-center shadow-xl ring-1 ring-primary/10 sm:px-10"
    >
      <div
        class="success-icon mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-primary-light ring-4 ring-primary/15"
      >
        <svg
          class="success-check h-14 w-14 text-primary"
          viewBox="0 0 52 52"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
        >
          <circle class="success-check-circle" cx="26" cy="26" r="23" stroke-width="3" />
          <path class="success-check-mark" d="M14 27l7 7 16-16" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>

      <p class="success-kicker mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-primary">
        {{ kicker }}
      </p>
      <h2 class="success-title text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
        {{ title }}
      </h2>
      <p class="success-message mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-500 sm:text-base">
        {{ message }}
      </p>

      <div v-if="$slots.extra" class="success-extra mx-auto mt-6 max-w-lg text-left">
        <slot name="extra" />
      </div>

      <dl
        v-if="summaryItems.length"
        class="success-summary mx-auto mt-8 grid max-w-lg grid-cols-2 gap-3 text-left sm:grid-cols-4"
      >
        <div
          v-for="item in summaryItems"
          :key="item.label"
          class="rounded-xl border border-primary/10 bg-primary-light/40 px-3 py-3"
        >
          <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
            {{ item.label }}
          </dt>
          <dd class="mt-1 text-sm font-semibold text-slate-900" :class="item.className">
            {{ item.value }}
          </dd>
        </div>
      </dl>

      <div class="success-actions mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
        <BaseButton
          className="border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
          @click="emit('secondary')"
        >
          {{ secondaryLabel }}
        </BaseButton>
        <BaseButton
          className="bg-primary text-white hover:bg-primary-hover"
          @click="emit('primary')"
        >
          {{ primaryLabel }}
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  kicker: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    required: true,
  },
  summaryItems: {
    type: Array,
    default: () => [],
  },
  primaryLabel: {
    type: String,
    required: true,
  },
  secondaryLabel: {
    type: String,
    required: true,
  },
})

defineEmits(['primary', 'secondary'])

const particles = [
  { id: 1, left: '8%', top: '12%', size: '8px', delay: '0.1s', duration: '2.8s', opacity: '0.55' },
  { id: 2, left: '18%', top: '68%', size: '6px', delay: '0.4s', duration: '3.1s', opacity: '0.45' },
  { id: 3, left: '82%', top: '18%', size: '7px', delay: '0.2s', duration: '2.6s', opacity: '0.6' },
  { id: 4, left: '88%', top: '72%', size: '5px', delay: '0.55s', duration: '3.4s', opacity: '0.4' },
  { id: 5, left: '48%', top: '8%', size: '6px', delay: '0.75s', duration: '2.9s', opacity: '0.5' },
  { id: 6, left: '72%', top: '52%', size: '8px', delay: '0.35s', duration: '3.2s', opacity: '0.65' },
  { id: 7, left: '28%', top: '28%', size: '5px', delay: '0.65s', duration: '2.7s', opacity: '0.48' },
  { id: 8, left: '58%', top: '78%', size: '7px', delay: '0.85s', duration: '3s', opacity: '0.52' },
]
</script>

<style scoped>
.success-card {
  animation: success-card-in 0.65s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.success-kicker,
.success-title,
.success-message,
.success-extra,
.success-summary,
.success-actions {
  animation: success-fade-up 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.success-kicker {
  animation-delay: 0.15s;
}

.success-title {
  animation-delay: 0.25s;
}

.success-message {
  animation-delay: 0.35s;
}

.success-extra {
  animation-delay: 0.4s;
}

.success-summary {
  animation-delay: 0.45s;
}

.success-actions {
  animation-delay: 0.55s;
}

.success-icon {
  animation: success-pop 0.75s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both;
}

.success-check-circle,
.success-check-mark {
  stroke: currentColor;
}

.success-check-circle {
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  animation: success-stroke 0.65s cubic-bezier(0.65, 0, 0.45, 1) 0.35s forwards;
}

.success-check-mark {
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  animation: success-stroke 0.45s cubic-bezier(0.65, 0, 0.45, 1) 0.85s forwards;
}

.success-ring {
  animation: success-ring-pulse 2.4s ease-out 0.2s infinite;
}

.success-ring-delay {
  animation-delay: 0.55s;
}

.success-particle {
  animation-name: success-float;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
  animation-direction: alternate;
}

@keyframes success-card-in {
  from {
    opacity: 0;
    transform: translateY(24px) scale(0.96);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes success-fade-up {
  from {
    opacity: 0;
    transform: translateY(16px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes success-pop {
  0% {
    opacity: 0;
    transform: scale(0.4);
  }

  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes success-stroke {
  to {
    stroke-dashoffset: 0;
  }
}

@keyframes success-ring-pulse {
  0% {
    opacity: 0.7;
    transform: translateX(-50%) scale(0.85);
  }

  70% {
    opacity: 0;
    transform: translateX(-50%) scale(1.45);
  }

  100% {
    opacity: 0;
    transform: translateX(-50%) scale(1.45);
  }
}

@keyframes success-float {
  from {
    transform: translateY(0) scale(1);
    opacity: 0.45;
  }

  to {
    transform: translateY(-14px) scale(1.08);
    opacity: 0.95;
  }
}
</style>

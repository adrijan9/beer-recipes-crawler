<template>
  <header class="border-b border-border bg-card backdrop-blur-sm shadow-soft">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-6">
      <div class="flex items-center gap-3">
        <img :src="logo" alt="Beer Smith Crawler logo" class="h-12 w-12 rounded-2xl shadow-strong" />
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-ink-soft">{{ appName }}</p>
          <h1 class="text-2xl font-display font-semibold text-ink">Recipe Archive Preview</h1>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-3 text-sm text-ink-soft">
        <span class="rounded-full border border-border bg-paper px-3 py-1">Saved files: {{ filesCount }}</span>
        <span class="rounded-full border border-border bg-paper px-3 py-1">Output: {{ outputDir }}</span>
        <div class="flex items-center gap-1 rounded-full border border-border bg-paper p-1 text-ink-soft shadow-soft">
          <button
            type="button"
            aria-label="Light theme"
            :aria-pressed="theme === 'light'"
            :class="[
              'rounded-full px-3 py-1 transition',
              theme === 'light' ? 'bg-ink text-paper shadow-soft' : 'text-ink-soft hover:text-ink',
            ]"
            @click="emit('update:theme', 'light')"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8" />
              <path
                d="M12 3v2.2M12 18.8V21M3 12h2.2M18.8 12H21M5.4 5.4l1.6 1.6M17 17l1.6 1.6M18.6 5.4 17 7M7 17l-1.6 1.6"
                stroke="currentColor"
                stroke-width="1.6"
                stroke-linecap="round"
              />
            </svg>
          </button>
          <button
            type="button"
            aria-label="System theme"
            :aria-pressed="theme === 'system'"
            :class="[
              'rounded-full px-3 py-1 transition',
              theme === 'system' ? 'bg-ink text-paper shadow-soft' : 'text-ink-soft hover:text-ink',
            ]"
            @click="emit('update:theme', 'system')"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="3" y="5" width="18" height="12" rx="2.2" stroke="currentColor" stroke-width="1.6" />
              <path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
          </button>
          <button
            type="button"
            aria-label="Dark theme"
            :aria-pressed="theme === 'dark'"
            :class="[
              'rounded-full px-3 py-1 transition',
              theme === 'dark' ? 'bg-ink text-paper shadow-soft' : 'text-ink-soft hover:text-ink',
            ]"
            @click="emit('update:theme', 'dark')"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path
                d="M19 14.5c-1.1.5-2.3.7-3.5.5-3.3-.5-5.6-3.6-5.1-6.9.2-1.2.7-2.2 1.4-3.1-3.9.4-7 3.8-7 7.9 0 4.4 3.6 8 8 8 2.9 0 5.5-1.5 6.8-3.9-.3.2-.4.3-.6.5z"
                stroke="currentColor"
                stroke-width="1.6"
                stroke-linejoin="round"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import logo from '../assets/logo.svg';

defineProps({
  filesCount: {
    type: Number,
    default: 0,
  },
  appName: {
    type: String,
    default: '',
  },
  outputDir: {
    type: String,
    default: '',
  },
  theme: {
    type: String,
    default: 'system',
  },
});

const emit = defineEmits(['update:theme']);
</script>

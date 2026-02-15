<template>
  <section class="rounded-2xl border border-border bg-card p-6 shadow-soft">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-display font-semibold text-ink">Recipe Files</h2>
        <p class="text-sm text-ink-soft">Select one or more saved JSON files to preview.</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button
          v-if="!showClearAllConfirm"
          class="rounded-full border border-border bg-card px-4 py-2 text-sm font-semibold text-ink-soft shadow-soft hover:border-copper hover:shadow-strong"
          type="button"
          @click="emit('refresh-files')"
          :disabled="loadingFiles"
        >
          <span v-if="loadingFiles" class="inline-flex items-center gap-2">
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-ink/20 border-t-ink"></span>
            Refreshing...
          </span>
          <span v-else>Refresh list</span>
        </button>
        <button
          v-if="!showClearAllConfirm"
          class="rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 shadow-soft hover:shadow-strong"
          type="button"
          @click="emit('request-clear-all')"
          :disabled="files.length === 0"
        >
          Clear all
        </button>
        <button
          v-if="!showClearAllConfirm"
          class="rounded-full bg-gradient-to-r from-amber to-copper px-4 py-2 text-sm font-semibold text-[#1b1208] shadow-strong"
          type="button"
          @click="emit('open-crawl-modal')"
        >
          New crawl
        </button>
        <div v-else class="flex flex-wrap items-center gap-2">
          <button
            class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white shadow-soft hover:shadow-strong"
            type="button"
            @click="emit('confirm-clear-all')"
          >
            Confirm clear
          </button>
          <button
            class="rounded-full border border-border bg-card px-3 py-2 text-xs font-semibold text-ink-soft shadow-soft hover:border-copper"
            type="button"
            @click="emit('cancel-clear-all')"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_auto]">
      <div class="rounded-xl border border-border bg-paper p-3">
        <div v-if="loadingFiles" class="rounded-lg border border-border bg-card px-3 py-4 text-sm text-ink-soft">
          <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em]">
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-ink/20 border-t-ink"></span>
            Refreshing files...
          </span>
        </div>
        <div v-else-if="files.length === 0" class="rounded-lg border border-border bg-card px-3 py-4 text-sm text-ink-soft">
          No saved recipe files yet.
        </div>
        <div v-else class="flex flex-col gap-2">
          <SelectFileComponent
            v-for="row in selectRows"
            :key="row.index"
            :value="row.value"
            :files="files"
            :selected-values="selectedValues"
            @update="handleUpdate(row.index, $event)"
            @clear="handleClear(row.index)"
          />
        </div>
      </div>
      <div class="flex flex-col justify-between gap-3">
        <div class="rounded-xl border border-border bg-paper px-4 py-3 text-sm text-ink-soft">
          <p class="font-semibold text-ink">Loaded recipes</p>
          <p class="text-2xl font-semibold text-ink">{{ recipesCount }}</p>
          <p class="text-xs uppercase tracking-[0.2em] text-ink-soft">Current selection</p>
        </div>
        <button
          class="rounded-full bg-ink px-4 py-2 text-sm font-semibold text-paper shadow-soft hover:shadow-strong"
          type="button"
          @click="emit('clear-selection')"
          :disabled="!hasSelections"
        >
          Clear selection
        </button>
        <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {{ errorMessage }}
        </div>
        <div v-if="loadingRecipes" class="rounded-xl border border-border bg-card px-4 py-3 text-sm text-ink-soft">
          Loading recipes...
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import SelectFileComponent from './SelectFileComponent.vue';

const props = defineProps({
  files: {
    type: Array,
    default: () => [],
  },
  selectRows: {
    type: Array,
    default: () => [],
  },
  loadingFiles: {
    type: Boolean,
    default: false,
  },
  showClearAllConfirm: {
    type: Boolean,
    default: false,
  },
  recipesCount: {
    type: Number,
    default: 0,
  },
  hasSelections: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  loadingRecipes: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  'refresh-files',
  'request-clear-all',
  'confirm-clear-all',
  'cancel-clear-all',
  'open-crawl-modal',
  'update-selection',
  'remove-file-at',
  'clear-selection',
]);

const selectedValues = computed(() => props.selectRows.map((row) => row.value).filter(Boolean));

function handleUpdate(index, event) {
  emit('update-selection', index, event);
}

function handleClear(index) {
  emit('remove-file-at', index);
}
</script>

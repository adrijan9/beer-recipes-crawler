<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8">
    <div class="absolute inset-0 bg-black/40" @click.self="handleBackdropClick"></div>
    <div class="relative w-full max-w-3xl rounded-2xl border border-border bg-card p-6 shadow-strong backdrop-blur-sm">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-ink-soft">{{ appName }}</p>
        </div>
        <button
          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-card text-ink-soft shadow-soft hover:border-copper hover:shadow-strong"
          type="button"
          @click="handleClose"
          :disabled="isCrawling"
          :class="isCrawling ? 'cursor-not-allowed opacity-60' : ''"
          aria-label="Close"
        >
          <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5">
            <path
              d="M6 6l12 12M18 6l-12 12"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-width="2"
            />
          </svg>
        </button>
      </div>

      <div class="my-6 border-t border-border"></div>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div class="flex flex-col gap-2 md:col-span-2">
          <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">File name (optional)</label>
          <input
            v-model="crawlForm.name"
            type="text"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
            placeholder="my_recipes_2024"
          />
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Search term</label>
          <input
            v-model="crawlForm.term"
            type="text"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
            :placeholder="crawlDefaults.term || ''"
          />
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Depth</label>
          <input
            v-model.number="crawlForm.depth"
            type="number"
            min="1"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          />
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Sort by</label>
          <select
            v-model="crawlForm.sort"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          >
            <option v-for="option in crawlSortOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Rating</label>
          <select
            v-model="crawlForm.rated"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          >
            <option v-for="option in crawlRatingOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
        <div class="flex flex-col gap-2 md:col-span-2 lg:col-span-3">
          <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Recipe types</label>
          <div class="flex flex-wrap gap-2 text-sm text-ink-soft">
            <label
              v-for="type in crawlTypeOptions"
              :key="type.key"
              class="flex items-center gap-2 rounded-lg border border-border bg-paper px-3 py-2"
            >
              <input v-model="crawlForm.types[type.key]" type="checkbox" class="accent-amber-500" />
              <span>{{ type.label }}</span>
            </label>
          </div>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap items-center gap-3">
        <button
          class="inline-flex min-w-[8.5rem] items-center justify-center rounded-full bg-gradient-to-r from-amber to-copper px-4 py-2 text-sm font-semibold text-[#1b1208] shadow-strong"
          type="button"
          @click="startCrawl"
          :disabled="isCrawling"
          :aria-busy="isCrawling ? 'true' : 'false'"
          :aria-label="isCrawling ? 'Crawling' : 'Start crawl'"
        >
          <span v-if="!isCrawling">Start crawl</span>
          <span v-else class="inline-flex items-center justify-center">
            <span class="sr-only">Crawling</span>
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-[#1b1208] border-t-transparent"></span>
          </span>
        </button>
        <span v-if="crawlStatus" class="rounded-full border border-border bg-paper px-3 py-1 text-sm text-ink-soft">
          {{ crawlStatus }}
        </span>
        <span v-if="crawlError" class="text-sm text-rose-600">{{ crawlError }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  appName: {
    type: String,
    default: '',
  },
  crawlConfig: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['close', 'completed']);

const crawlDefaults = computed(() => props.crawlConfig?.defaults || {});
const crawlSortOptions = computed(() => props.crawlConfig?.sortOptions || []);
const crawlTypeOptions = computed(() => props.crawlConfig?.typeOptions || []);
const crawlRatingOptions = computed(() => props.crawlConfig?.ratingOptions || []);

const crawlForm = ref(buildCrawlForm(crawlDefaults.value, crawlTypeOptions.value));
const crawlStatus = ref('');
const crawlError = ref('');
const isCrawling = ref(false);

watch(
  [crawlDefaults, crawlTypeOptions],
  ([defaults, typeOptions]) => {
    crawlForm.value = buildCrawlForm(defaults, typeOptions);
  },
  { immediate: true }
);

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      crawlStatus.value = '';
      crawlError.value = '';
    }
  }
);

async function startCrawl() {
  if (isCrawling.value) return;
  crawlError.value = '';
  crawlStatus.value = '';
  isCrawling.value = true;

  try {
    const params = new URLSearchParams();
    if (crawlForm.value.term) params.set('term', crawlForm.value.term.trim());
    if (crawlForm.value.name) params.set('name', crawlForm.value.name.trim());
    if (crawlForm.value.depth) params.set('depth', String(crawlForm.value.depth));
    if (crawlForm.value.sort) params.set('sort', crawlForm.value.sort);
    if (crawlForm.value.rated !== null && crawlForm.value.rated !== undefined) {
      params.set('rated', String(crawlForm.value.rated));
    }

    Object.entries(crawlForm.value.types).forEach(([key, value]) => {
      if (value) params.set(key, '1');
    });

    const queryString = params.toString();
    const url = queryString ? `/api/crawl.php?${queryString}` : '/api/crawl.php';
    const response = await fetch(url, { method: 'POST' });

    if (!response.ok) {
      let data = null;
      try {
        data = await response.json();
      } catch (error) {
        data = null;
      }
      const message = data && data.error ? data.error : 'Failed to crawl recipes.';
      throw new Error(message);
    }

    crawlStatus.value = 'Crawl completed';
    emit('completed');
  } catch (error) {
    crawlError.value = error?.message || 'Failed to crawl recipes.';
  } finally {
    isCrawling.value = false;
  }
}

function handleClose() {
  if (isCrawling.value) return;
  emit('close');
}

function handleBackdropClick() {
  if (isCrawling.value) return;
  emit('close');
}

function buildCrawlForm(defaults, typeOptions) {
  const term = typeof defaults?.term === 'string' ? defaults.term : '';
  const depthValue = Number(defaults?.depth);
  const depth = Number.isFinite(depthValue) && depthValue > 0 ? depthValue : 1;
  const sort = typeof defaults?.sort === 'string' ? defaults.sort : '';
  const rated = defaults?.rated !== null && defaults?.rated !== undefined ? String(defaults.rated) : '0';
  const types = {};

  if (defaults?.types && typeof defaults.types === 'object') {
    Object.entries(defaults.types).forEach(([key, value]) => {
      types[key] = Boolean(value);
    });
  } else if (Array.isArray(typeOptions)) {
    typeOptions.forEach((option) => {
      if (option?.key) {
        types[option.key] = false;
      }
    });
  }

  return {
    term,
    name: '',
    depth,
    sort,
    rated,
    types,
  };
}
</script>

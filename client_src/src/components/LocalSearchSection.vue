<template>
  <section class="rounded-2xl border border-border bg-card p-6 shadow-soft">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-display font-semibold text-ink">Local Search</h2>
        <p class="text-sm text-ink-soft">Filter the currently loaded recipes without starting a crawl.</p>
      </div>
      <button
        class="rounded-full border border-border bg-card px-4 py-2 text-sm font-semibold text-ink-soft shadow-soft hover:border-copper hover:shadow-strong"
        type="button"
        @click="emit('reset')"
        :disabled="disabled"
      >
        Clear filters
      </button>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Search</label>
        <input
          v-model="searchTerm"
          type="text"
          class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          placeholder="Search recipes"
          :disabled="disabled"
        />
      </div>
      <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Rating</label>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <select
            v-model="ratingComparator"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
            :disabled="disabled"
          >
            <option v-for="option in ratingComparatorOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <select
            v-model="ratingFilter"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
            :disabled="disabled"
          >
            <option v-for="option in ratingOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>
      <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Malt</label>
        <select
          :value="ingredientFilters.malt"
          class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          :disabled="disabled"
          @change="updateIngredientFilter('malt', $event)"
        >
          <option value="any">Any</option>
          <option v-for="option in ingredientOptions.malt" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Hops</label>
        <select
          :value="ingredientFilters.hops"
          class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          :disabled="disabled"
          @change="updateIngredientFilter('hops', $event)"
        >
          <option value="any">Any</option>
          <option v-for="option in ingredientOptions.hops" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Yeast</label>
        <select
          :value="ingredientFilters.yeast"
          class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          :disabled="disabled"
          @change="updateIngredientFilter('yeast', $event)"
        >
          <option value="any">Any</option>
          <option v-for="option in ingredientOptions.yeast" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Misc</label>
        <select
          :value="ingredientFilters.misc"
          class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          :disabled="disabled"
          @change="updateIngredientFilter('misc', $event)"
        >
          <option value="any">Any</option>
          <option v-for="option in ingredientOptions.misc" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  ingredientOptions: {
    type: Object,
    default: () => ({ malt: [], hops: [], yeast: [], misc: [] }),
  },
  ratingOptions: {
    type: Array,
    default: () => [],
  },
  ratingComparatorOptions: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['reset']);

const searchTerm = defineModel('searchTerm', { type: String, default: '' });
const ratingFilter = defineModel('ratingFilter', { type: String, default: 'any' });
const ratingComparator = defineModel('ratingComparator', { type: String, default: '=' });
const ingredientFilters = defineModel('ingredientFilters', {
  type: Object,
  default: () => ({ malt: 'any', hops: 'any', yeast: 'any', misc: 'any' }),
});

function updateIngredientFilter(key, event) {
  const value = event?.target?.value || 'any';
  ingredientFilters.value = { ...ingredientFilters.value, [key]: value };
}
</script>

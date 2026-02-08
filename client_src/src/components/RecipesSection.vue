<template>
  <section class="rounded-2xl border border-border bg-card p-6 shadow-soft">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-display font-semibold text-ink">Recipes</h2>
        <p class="text-sm text-ink-soft">Sorted and filtered locally.</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Sort</label>
        <div class="flex items-center gap-2">
          <select
            v-model="sortKey"
            class="rounded-lg border border-border px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
            :disabled="sortedRecipes.length === 0"
          >
            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <button
            class="flex h-10 w-10 items-center justify-center rounded-lg border border-border bg-card shadow-soft hover:border-copper"
            type="button"
            @click="toggleSortOrder"
            :disabled="sortedRecipes.length === 0"
            :aria-label="sortOrderLabel"
          >
            <span class="flex flex-col gap-1">
              <span :class="[sortOrder === 'asc' ? 'w-2' : 'w-5', 'h-0.5 rounded-full bg-ink']"></span>
              <span class="h-0.5 w-4 rounded-full bg-ink"></span>
              <span :class="[sortOrder === 'asc' ? 'w-5' : 'w-2', 'h-0.5 rounded-full bg-ink']"></span>
            </span>
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="sortedRecipes.length === 0"
      class="mt-6 rounded-xl border border-border bg-paper px-4 py-6 text-sm text-ink-soft"
    >
      No recipes match the current filters.
    </div>

    <div v-else class="mt-6 grid gap-4">
      <div
        v-for="(recipe, index) in sortedRecipes"
        :key="`${recipe.name}-${index}`"
        class="rounded-xl border border-border bg-card p-4 shadow-soft"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h3 class="text-lg font-display font-semibold text-ink">{{ recipe.name || 'Untitled Recipe' }}</h3>
            <p class="text-sm text-ink-soft">{{ recipe.style || 'Style not listed' }}</p>
            <div class="mt-2 flex items-center gap-3 text-sm text-ink-soft">
              <span class="text-xs font-semibold uppercase tracking-[0.2em]">Rating</span>
              <span class="text-sm font-semibold text-ink">{{ formatRating(recipe.rating) }}</span>
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-2 text-xs text-ink-soft">
            <span class="rounded-full border border-border bg-paper px-3 py-1">Type: {{ recipe.type || 'N/A' }}</span>
            <span class="rounded-full border border-border bg-paper px-3 py-1"
              >IBU: {{ recipe.bitterness || '-' }}</span
            >
            <span class="rounded-full border border-border bg-paper px-3 py-1">SRM: {{ recipe.color || '-' }}</span>
          </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Malt</p>
            <ul class="mt-2 space-y-1 text-sm text-ink-soft">
              <li v-for="(item, idx) in filterIngredients(recipe.ingredients, 'malt')" :key="`malt-${idx}`">
                {{ item.name }}
              </li>
              <li v-if="filterIngredients(recipe.ingredients, 'malt').length === 0" class="text-ink-soft">-</li>
            </ul>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Hops</p>
            <ul class="mt-2 space-y-1 text-sm text-ink-soft">
              <li v-for="(item, idx) in filterIngredients(recipe.ingredients, 'hops')" :key="`hops-${idx}`">
                {{ item.name }}
              </li>
              <li v-if="filterIngredients(recipe.ingredients, 'hops').length === 0" class="text-ink-soft">-</li>
            </ul>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Yeast</p>
            <ul class="mt-2 space-y-1 text-sm text-ink-soft">
              <li v-for="(item, idx) in filterIngredients(recipe.ingredients, 'yeast')" :key="`yeast-${idx}`">
                {{ item.name }}
              </li>
              <li v-if="filterIngredients(recipe.ingredients, 'yeast').length === 0" class="text-ink-soft">-</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';

defineProps({
  sortedRecipes: {
    type: Array,
    default: () => [],
  },
  sortOptions: {
    type: Array,
    default: () => [],
  },
});

const sortKey = defineModel('sortKey', { type: String, default: 'default' });
const sortOrder = defineModel('sortOrder', { type: String, default: 'asc' });

const sortOrderLabel = computed(() => (sortOrder.value === 'desc' ? 'Sort descending' : 'Sort ascending'));

function toggleSortOrder() {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
}

function parseNumeric(value) {
  if (value === null || value === undefined) return null;
  if (typeof value === 'number') return Number.isFinite(value) ? value : null;
  const match = String(value).match(/-?\d+(?:\.\d+)?/);
  if (!match) return null;
  const parsed = parseFloat(match[0]);
  return Number.isFinite(parsed) ? parsed : null;
}

function formatRating(value) {
  if (value === null || value === undefined || value === '') {
    return 'Unrated';
  }
  const ratingValue = parseNumeric(value) ?? 0;
  if (ratingValue <= 0) {
    return 'Unrated';
  }
  if (ratingValue > 5) {
    return '🍺🍺🍺🍺🍺';
  }
  return '🍺'.repeat(Math.round(ratingValue));
}

function normalizeIngredientType(type) {
  return (type || '').trim().toLowerCase();
}

function ingredientTypeMatches(type, key) {
  const normalized = normalizeIngredientType(type);
  if (!normalized) return false;
  if (key === 'malt') return normalized.includes('grain') || normalized.includes('malt');
  if (key === 'hops') return normalized.includes('hop');
  if (key === 'yeast') return normalized.includes('yeast');
  return false;
}

function filterIngredients(ingredients, key) {
  if (!Array.isArray(ingredients)) return [];
  return ingredients.filter((ingredient) => ingredientTypeMatches(ingredient?.type, key));
}
</script>

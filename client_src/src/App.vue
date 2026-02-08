<template>
  <div class="min-h-screen relative">
    <div v-if="isBootstrapping" class="fixed inset-0 z-50 flex items-center justify-center bg-paper text-ink">
      <div class="flex flex-col items-center text-center">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-ink/20 border-t-ink"></div>
        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.35em] text-ink-soft">Loading...</p>
        <p class="mt-2 text-sm text-ink-soft">{{ bootstrapStatus }}</p>
        <p v-if="bootstrapError" class="mt-4 text-sm text-rose-600">{{ bootstrapError }}</p>
      </div>
    </div>

    <div v-else class="relative z-10">
      <HeaderSection
        :files-count="files.length"
        :theme="theme"
        :app-name="appName"
        :output-dir="outputDir"
        @update:theme="setTheme"
      />

      <main class="mx-auto flex max-w-6xl flex-col gap-8 px-4 py-10">
        <RecipeFilesSection
          :files="files"
          :select-rows="selectRows"
          :loading-files="loadingFiles"
          :show-clear-all-confirm="showClearAllConfirm"
          :recipes-count="recipes.length"
          :has-selections="hasSelections"
          :error-message="errorMessage"
          :loading-recipes="loadingRecipes"
          @refresh-files="fetchFiles"
          @request-clear-all="requestClearAll"
          @confirm-clear-all="confirmClearAll"
          @cancel-clear-all="cancelClearAll"
          @open-crawl-modal="openCrawlModal"
          @update-selection="updateSelection"
          @clear-selection-at="clearSelectionAt"
          @clear-selection="clearSelection"
        />

        <StatisticsSection
          v-if="hasSelections"
          :stats="stats"
          :merged-meta="mergedMeta"
          :per-file-meta="perFileMeta"
          :theme="theme"
          :recipes="recipes"
          :type-labels="recipeTypeLabels"
        />

        <LocalSearchSection
          v-if="hasSelections"
          v-model:searchTerm="searchTerm"
          v-model:ratingFilter="ratingFilter"
          v-model:ratingComparator="ratingComparator"
          v-model:ingredientFilters="ingredientFilters"
          :ingredient-options="ingredientOptions"
          :rating-options="filterRatingOptions"
          :rating-comparator-options="ratingComparatorOptions"
          :disabled="recipes.length === 0"
          @reset="resetFilters"
        />

        <RecipesSection
          v-if="hasSelections"
          v-model:sortKey="sortKey"
          v-model:sortOrder="sortOrder"
          :sorted-recipes="sortedRecipes"
          :sort-options="recipeSortOptions"
        />
      </main>
      <CrawlModal
        :open="isCrawlOpen"
        :app-name="appName"
        :crawl-config="crawlConfig"
        @close="closeCrawlModal"
        @completed="handleCrawlCompleted"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import CrawlModal from './components/CrawlModal.vue';
import HeaderSection from './components/HeaderSection.vue';
import LocalSearchSection from './components/LocalSearchSection.vue';
import RecipeFilesSection from './components/RecipeFilesSection.vue';
import RecipesSection from './components/RecipesSection.vue';
import StatisticsSection from './components/StatisticsSection.vue';

const files = ref([]);
const selectedFiles = ref([]);
const recipes = ref([]);
const loadingFiles = ref(false);
const loadingRecipes = ref(false);
const errorMessage = ref('');
const theme = ref('system');
const appConfig = ref(null);
const isBootstrapping = ref(true);
const bootstrapStage = ref('config');
const bootstrapError = ref('');

const THEME_STORAGE_KEY = 'beer-smith-theme';
const THEME_OPTIONS = new Set(['light', 'system', 'dark']);

const searchTerm = ref('');
const ratingFilter = ref('any');
const ratingComparator = ref('=');
const ingredientFilters = ref({
  malt: 'any',
  hops: 'any',
  yeast: 'any',
  misc: 'any',
});

const sortKey = ref('default');
const sortOrder = ref('asc');

const isCrawlOpen = ref(false);
const showClearAllConfirm = ref(false);
const hasSelections = computed(() => normalizeSelections(selectedFiles.value).length > 0);
const mergedMeta = ref({});
const perFileMeta = ref([]);

const appName = computed(() => appConfig.value?.app?.name || '');
const outputDir = computed(() => appConfig.value?.app?.outputDir || '');
const crawlConfig = computed(() => appConfig.value?.crawl || {});
const filterRatingOptions = computed(() => appConfig.value?.filters?.ratingOptions || []);
const ratingComparatorOptions = computed(() => appConfig.value?.filters?.ratingComparators || []);
const defaultRatingComparator = computed(() => appConfig.value?.filters?.ratingComparatorDefault || '=');
const recipeSortOptions = computed(() => appConfig.value?.recipes?.sortOptions || []);
const recipeTypeLabels = computed(() => appConfig.value?.stats?.recipeTypeLabels || {});

const bootstrapStatus = computed(() => {
  if (bootstrapStage.value === 'files') {
    return 'Getting files list...';
  }
  return 'Getting configuration...';
});

const stats = ref({
  recipes: 0,
  ingredients: 0,
  grains: 0,
  hops: 0,
  yeast: 0,
  timestamp: null,
});

function normalizeTheme(value) {
  if (typeof value !== 'string') return 'system';
  const normalized = value.trim().toLowerCase();
  return THEME_OPTIONS.has(normalized) ? normalized : 'system';
}

function setTheme(value, persist = true) {
  const nextTheme = normalizeTheme(value);
  theme.value = nextTheme;
  if (typeof document !== 'undefined') {
    document.documentElement.setAttribute('data-theme', nextTheme);
  }
  if (persist && typeof localStorage !== 'undefined') {
    try {
      localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
    } catch {
      // Ignore storage failures (private mode or blocked storage).
    }
  }
}

function loadStoredTheme() {
  if (typeof localStorage === 'undefined') return null;
  try {
    return localStorage.getItem(THEME_STORAGE_KEY);
  } catch {
    return null;
  }
}

const selectRows = computed(() => {
  const uniqueSelections = normalizeSelections(selectedFiles.value);
  const maxRows = Math.min(files.value.length, uniqueSelections.length + 1);
  const rows = [];
  for (let i = 0; i < maxRows; i += 1) {
    rows.push({
      index: i,
      value: uniqueSelections[i] || '',
    });
  }
  return rows;
});

const ingredientOptions = computed(() => {
  const options = {
    malt: new Set(),
    hops: new Set(),
    yeast: new Set(),
    misc: new Set(),
  };

  recipes.value.forEach((recipe) => {
    const ingredients = Array.isArray(recipe?.ingredients) ? recipe.ingredients : [];
    ingredients.forEach((ingredient) => {
      const name = (ingredient?.name || '').trim();
      if (!name) return;
      if (ingredientTypeMatches(ingredient?.type, 'malt')) {
        options.malt.add(name);
      } else if (ingredientTypeMatches(ingredient?.type, 'hops')) {
        options.hops.add(name);
      } else if (ingredientTypeMatches(ingredient?.type, 'yeast')) {
        options.yeast.add(name);
      } else if (ingredientTypeMatches(ingredient?.type, 'misc')) {
        options.misc.add(name);
      }
    });
  });

  return {
    malt: Array.from(options.malt).sort(localeSort),
    hops: Array.from(options.hops).sort(localeSort),
    yeast: Array.from(options.yeast).sort(localeSort),
    misc: Array.from(options.misc).sort(localeSort),
  };
});

const filteredRecipes = computed(() => {
  const term = searchTerm.value.trim().toLowerCase();
  const rating = ratingFilter.value;

  return recipes.value.filter((recipe) => {
    if (term && !(recipe?.name || '').toLowerCase().includes(term)) {
      return false;
    }
    if (rating !== 'any') {
      const ratingValue = parseNumeric(recipe?.rating) ?? 0;
      const target = Number(rating);
      if (Number.isNaN(target)) return false;
      if (ratingComparator.value === '>') {
        if (!(ratingValue > target)) return false;
      } else if (ratingComparator.value === '<') {
        if (!(ratingValue < target)) return false;
      } else if (ratingValue !== target) {
        return false;
      }
    }
    if (!matchesIngredientFilter(recipe, 'malt', ingredientFilters.value.malt)) return false;
    if (!matchesIngredientFilter(recipe, 'hops', ingredientFilters.value.hops)) return false;
    if (!matchesIngredientFilter(recipe, 'yeast', ingredientFilters.value.yeast)) return false;
    if (!matchesIngredientFilter(recipe, 'misc', ingredientFilters.value.misc)) return false;

    return true;
  });
});

const sortedRecipes = computed(() => {
  if (sortKey.value === 'default') {
    return filteredRecipes.value.slice();
  }

  const mapped = filteredRecipes.value.map((recipe, index) => ({
    recipe,
    index,
    value: getSortValue(recipe, sortKey.value),
  }));

  mapped.sort((a, b) => {
    const valueA = a.value;
    const valueB = b.value;
    const missingA = valueA === null || valueA === undefined || valueA === '';
    const missingB = valueB === null || valueB === undefined || valueB === '';

    if (missingA && missingB) return a.index - b.index;
    if (missingA) return 1;
    if (missingB) return -1;

    let result = 0;
    if (typeof valueA === 'number' && typeof valueB === 'number') {
      result = valueA - valueB;
    } else {
      result = localeSort(String(valueA), String(valueB));
    }

    if (result === 0) {
      result = a.index - b.index;
    }

    return sortOrder.value === 'desc' ? -result : result;
  });

  return mapped.map((item) => item.recipe);
});

watch(ingredientOptions, (options) => {
  if (ingredientFilters.value.malt !== 'any' && !options.malt.includes(ingredientFilters.value.malt)) {
    ingredientFilters.value.malt = 'any';
  }
  if (ingredientFilters.value.hops !== 'any' && !options.hops.includes(ingredientFilters.value.hops)) {
    ingredientFilters.value.hops = 'any';
  }
  if (ingredientFilters.value.yeast !== 'any' && !options.yeast.includes(ingredientFilters.value.yeast)) {
    ingredientFilters.value.yeast = 'any';
  }
  if (ingredientFilters.value.misc !== 'any' && !options.misc.includes(ingredientFilters.value.misc)) {
    ingredientFilters.value.misc = 'any';
  }
});

watch(
  () => selectedFiles.value.slice(),
  () => {
    loadRecipes();
  }
);

watch(
  () => recipes.value,
  () => {
    updateStats();
  },
  { deep: true }
);

onMounted(() => {
  setTheme(loadStoredTheme() || 'system', false);
  bootstrap();
});

function localeSort(a, b) {
  return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
}

function normalizeSelections(selections) {
  if (!Array.isArray(selections)) return [];
  const uniqueSelections = [];
  const seen = new Set();
  selections.forEach((value) => {
    if (!value) return;
    if (seen.has(value)) return;
    seen.add(value);
    uniqueSelections.push(value);
  });
  return uniqueSelections;
}

function updateSelection(index, event) {
  const value = event?.target?.value || '';
  const current = normalizeSelections(selectedFiles.value);
  if (!value) {
    if (index < current.length) {
      current.splice(index, 1);
    }
    selectedFiles.value = current;
    return;
  }

  const filtered = current.filter((item) => item !== value);
  const insertIndex = Math.min(index, filtered.length);
  filtered.splice(insertIndex, 0, value);
  selectedFiles.value = filtered;
}

function clearSelectionAt(index) {
  const current = normalizeSelections(selectedFiles.value);
  if (index < current.length) {
    current.splice(index, 1);
  }
  selectedFiles.value = current;
}

async function fetchFiles() {
  loadingFiles.value = true;
  errorMessage.value = '';
  showClearAllConfirm.value = false;
  try {
    const response = await fetch('/api/list_recipes.php');
    if (!response.ok) throw new Error('Failed to load recipe files.');
    const data = await response.json();
    applyFiles(data);
  } catch (error) {
    errorMessage.value = error?.message || 'Failed to load recipe files.';
  } finally {
    loadingFiles.value = false;
  }
}

async function bootstrap() {
  isBootstrapping.value = true;
  bootstrapError.value = '';
  bootstrapStage.value = 'config';
  try {
    const response = await fetch('/api/bootstrap.php');
    if (!response.ok) {
      throw new Error('Failed to load configuration.');
    }
    const data = await response.json();
    if (!data || typeof data !== 'object') {
      throw new Error('Invalid bootstrap response.');
    }
    if (!data.config || typeof data.config !== 'object') {
      throw new Error('Invalid configuration response.');
    }
    appConfig.value = data.config;
    ratingComparator.value = defaultRatingComparator.value;
    bootstrapStage.value = 'files';
    await nextTick();
    applyFiles(data.files || []);
    isBootstrapping.value = false;
  } catch (error) {
    bootstrapError.value = error?.message || 'Failed to load configuration.';
  }
}

function applyFiles(list) {
  const sorted = Array.isArray(list) ? [...list].sort() : [];
  files.value = sorted;
  selectedFiles.value = normalizeSelections(selectedFiles.value).filter((file) => sorted.includes(file));
}

async function loadRecipes() {
  errorMessage.value = '';
  if (selectedFiles.value.length === 0) {
    recipes.value = [];
    mergedMeta.value = {};
    perFileMeta.value = [];
    stats.value = {
      recipes: 0,
      ingredients: 0,
      grains: 0,
      hops: 0,
      yeast: 0,
      timestamp: null,
    };
    return;
  }

  loadingRecipes.value = true;
  try {
    const selection = normalizeSelections(selectedFiles.value);
    const payloads = await Promise.all(selection.map(fetchRecipeFile));
    const normalizedPayloads = payloads.map(normalizePayload);
    const merged = mergeNormalizedPayloads(normalizedPayloads);
    recipes.value = merged.recipes;
    mergedMeta.value = merged.meta || {};
    perFileMeta.value = selection.map((file, index) => ({
      file,
      meta: normalizedPayloads[index]?.meta || {},
    }));
  } catch (error) {
    errorMessage.value = error?.message || 'Failed to load recipes.';
    recipes.value = [];
    mergedMeta.value = {};
    perFileMeta.value = [];
  } finally {
    loadingRecipes.value = false;
  }
}

function clearSelection() {
  selectedFiles.value = [];
  recipes.value = [];
  mergedMeta.value = {};
  perFileMeta.value = [];
}

function requestClearAll() {
  if (files.value.length === 0) return;
  showClearAllConfirm.value = true;
}

function cancelClearAll() {
  showClearAllConfirm.value = false;
}

async function confirmClearAll() {
  showClearAllConfirm.value = false;
  errorMessage.value = '';

  try {
    const response = await fetch('/api/delete_all.php', { method: 'DELETE' });
    let data = null;
    try {
      data = await response.json();
    } catch (error) {
      data = null;
    }

    if (!response.ok || !data || data.success !== true) {
      const message = data && data.error ? data.error : 'Failed to clear recipe files.';
      throw new Error(message);
    }

    selectedFiles.value = [];
    recipes.value = [];
    mergedMeta.value = {};
    perFileMeta.value = [];
    stats.value = {
      recipes: 0,
      ingredients: 0,
      grains: 0,
      hops: 0,
      yeast: 0,
      timestamp: null,
    };
    await fetchFiles();
  } catch (error) {
    errorMessage.value = error?.message || 'Failed to clear recipe files.';
  }
}

function openCrawlModal() {
  isCrawlOpen.value = true;
}

function closeCrawlModal() {
  isCrawlOpen.value = false;
}

async function handleCrawlCompleted() {
  await fetchFiles();
  isCrawlOpen.value = false;
}

async function fetchRecipeFile(file) {
  const response = await fetch(`/api/get_recipe.php?recipe=${encodeURIComponent(file)}`);
  let data = null;

  try {
    data = await response.json();
  } catch (error) {
    data = null;
  }

  if (!response.ok) {
    const message = data && data.error ? data.error : 'Failed to load recipe file.';
    throw new Error(message);
  }

  return data;
}

function normalizePayload(payload) {
  if (Array.isArray(payload)) {
    return { meta: {}, recipes: payload };
  }

  if (payload && Array.isArray(payload.recipes)) {
    const meta =
      payload.meta && typeof payload.meta === 'object'
        ? payload.meta
        : {
            term: payload.term ?? null,
            depth: payload.depth ?? null,
            sort: payload.sort ?? null,
            types: payload.types ?? null,
            rated: payload.rated ?? null,
            timestamp: payload.timestamp ?? null,
          };
    return {
      meta,
      recipes: payload.recipes,
    };
  }

  return { meta: {}, recipes: [] };
}

function mergePayloads(payloads) {
  return mergeNormalizedPayloads(payloads.map(normalizePayload));
}

function mergeNormalizedPayloads(normalizedPayloads) {
  const recipes = [];
  const metas = [];

  normalizedPayloads.forEach((normalized) => {
    if (normalized?.meta && typeof normalized.meta === 'object') {
      metas.push(normalized.meta);
    }
    if (Array.isArray(normalized?.recipes)) {
      recipes.push(...normalized.recipes);
    }
  });

  return { meta: mergeMeta(metas), recipes };
}

function resetFilters() {
  searchTerm.value = '';
  ratingFilter.value = 'any';
  ratingComparator.value = defaultRatingComparator.value;
  ingredientFilters.value = {
    malt: 'any',
    hops: 'any',
    yeast: 'any',
    misc: 'any',
  };
}

function mergeMeta(metas) {
  if (!Array.isArray(metas) || metas.length === 0) {
    return {};
  }

  return {
    term: mergeScalar(metas.map((meta) => meta?.term)),
    depth: mergeScalar(metas.map((meta) => meta?.depth)),
    sort: mergeScalar(metas.map((meta) => meta?.sort)),
    types: mergeTypes(metas.map((meta) => meta?.types)),
    rated: mergeScalar(metas.map((meta) => meta?.rated)),
    timestamp: mergeScalar(metas.map((meta) => meta?.timestamp)),
  };
}

function mergeScalar(values) {
  if (!Array.isArray(values) || values.length === 0) {
    return null;
  }

  const normalized = values.map((value) => {
    if (value === undefined || value === '') {
      return null;
    }
    return value;
  });
  const unique = new Set(normalized.map((value) => JSON.stringify(value)));

  if (unique.size === 1) {
    return normalized[0];
  }

  return 'Multiple';
}

function mergeTypes(typesList) {
  if (!Array.isArray(typesList) || typesList.length === 0) {
    return null;
  }

  const merged = {};
  let hasAny = false;

  typesList.forEach((types) => {
    if (!types) return;
    if (Array.isArray(types)) {
      types.forEach((type) => {
        if (!type) return;
        merged[type] = 1;
        hasAny = true;
      });
      return;
    }
    if (typeof types !== 'object') return;
    Object.entries(types).forEach(([key, value]) => {
      if (value === null || value === '' || value === false || value === 0 || value === '0') {
        return;
      }
      merged[key] = value;
      hasAny = true;
    });
  });

  return hasAny ? merged : null;
}

function parseNumeric(value) {
  if (value === null || value === undefined) return null;
  if (typeof value === 'number') return Number.isFinite(value) ? value : null;
  const match = String(value).match(/-?\d+(?:\.\d+)?/);
  if (!match) return null;
  const parsed = parseFloat(match[0]);
  return Number.isFinite(parsed) ? parsed : null;
}

function getSortValue(recipe, key) {
  if (!recipe) return null;
  switch (key) {
    case 'name':
      return (recipe.name || '').trim();
    case 'rating':
      return parseNumeric(recipe.rating);
    case 'bitterness':
      return parseNumeric(recipe.bitterness);
    case 'color':
      return parseNumeric(recipe.color);
    case 'batchSize':
      return parseNumeric(recipe.batchSize);
    case 'estOg':
      return parseNumeric(recipe.estOg);
    case 'estFg':
      return parseNumeric(recipe.estFg);
    case 'style':
      return (recipe.style || '').trim();
    case 'type':
      return (recipe.type || '').trim();
    case 'fermentation':
      return (recipe.fermentation || '').trim();
    default:
      return null;
  }
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
  if (key === 'misc') return normalized.includes('misc');
  return false;
}

function matchesIngredientFilter(recipe, key, selectedValue) {
  if (!selectedValue || selectedValue === 'any') return true;
  const ingredients = Array.isArray(recipe?.ingredients) ? recipe.ingredients : [];
  const target = selectedValue.toLowerCase();
  return ingredients.some((ingredient) => {
    if (!ingredientTypeMatches(ingredient?.type, key)) return false;
    const name = (ingredient?.name || '').trim().toLowerCase();
    return name === target;
  });
}

function updateStats() {
  const totalRecipes = recipes.value.length;
  let totalIngredients = 0;
  let grainCount = 0;
  let hopCount = 0;
  let yeastCount = 0;

  recipes.value.forEach((recipe) => {
    const ingredients = Array.isArray(recipe?.ingredients) ? recipe.ingredients : [];
    totalIngredients += ingredients.length;
    ingredients.forEach((ingredient) => {
      if (ingredientTypeMatches(ingredient?.type, 'malt')) grainCount += 1;
      if (ingredientTypeMatches(ingredient?.type, 'hops')) hopCount += 1;
      if (ingredientTypeMatches(ingredient?.type, 'yeast')) yeastCount += 1;
    });
  });

  stats.value = {
    recipes: totalRecipes,
    ingredients: totalIngredients,
    grains: grainCount,
    hops: hopCount,
    yeast: yeastCount,
    timestamp: mergedMeta.value?.timestamp || null,
  };
}
</script>

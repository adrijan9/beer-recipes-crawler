<template>
  <section class="rounded-2xl border border-border bg-card p-6 shadow-soft">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-display font-semibold text-ink">Statistics</h2>
        <p class="text-sm text-ink-soft">Filters and usage stats for the current selection.</p>
      </div>
      <div class="text-sm text-ink-soft">
        <span class="rounded-full border border-border bg-paper px-3 py-1"
          >Run Time: {{ stats.timestamp || 'N/A' }}</span
        >
      </div>
    </div>

    <div class="mt-4 rounded-xl border border-border bg-paper p-4">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-xs font-semibold uppercase tracking-[0.25em] text-ink-soft">Filters</h3>
        <span class="text-xs text-ink-soft">Per file + merged</span>
      </div>

      <div class="mt-4">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Per file</p>
        <div
          v-if="perFileMeta.length === 0"
          class="mt-3 rounded-lg border border-border bg-card px-3 py-4 text-sm text-ink-soft"
        >
          No filters yet. Select recipe files to see their crawl settings.
        </div>
        <div v-else class="mt-3 space-y-3">
          <div v-for="entry in perFileMeta" :key="entry.file" class="rounded-xl border border-border bg-card p-4">
            <p class="text-sm font-semibold text-ink">{{ formatFileName(entry.file) }}</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
              <div
                v-for="field in filterFields"
                :key="field.key"
                class="rounded-xl border border-border bg-paper px-3 py-2 text-xs text-ink-soft"
              >
                <p class="uppercase tracking-[0.2em]">{{ field.label }}</p>
                <p class="mt-1 text-sm font-semibold text-ink">{{ field.format(entry.meta?.[field.key]) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-ink-soft">Merged</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
          <div
            v-for="field in filterFields"
            :key="field.key"
            class="rounded-xl border border-border bg-card px-3 py-2 text-xs text-ink-soft"
          >
            <p class="uppercase tracking-[0.2em]">{{ field.label }}</p>
            <p class="mt-1 text-sm font-semibold text-ink">{{ field.format(mergedMeta?.[field.key]) }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-5">
      <div class="rounded-xl border border-border bg-paper px-4 py-3 text-sm text-ink-soft">
        <p class="text-xs uppercase tracking-[0.2em]">Recipes</p>
        <p class="text-xl font-semibold text-ink">{{ stats.recipes }}</p>
      </div>
      <div class="rounded-xl border border-border bg-paper px-4 py-3 text-sm text-ink-soft">
        <p class="text-xs uppercase tracking-[0.2em]">Ingredients</p>
        <p class="text-xl font-semibold text-ink">{{ stats.ingredients }}</p>
      </div>
      <div class="rounded-xl border border-border bg-paper px-4 py-3 text-sm text-ink-soft">
        <p class="text-xs uppercase tracking-[0.2em]">Grains</p>
        <p class="text-xl font-semibold text-ink">{{ stats.grains }}</p>
      </div>
      <div class="rounded-xl border border-border bg-paper px-4 py-3 text-sm text-ink-soft">
        <p class="text-xs uppercase tracking-[0.2em]">Hops</p>
        <p class="text-xl font-semibold text-ink">{{ stats.hops }}</p>
      </div>
      <div class="rounded-xl border border-border bg-paper px-4 py-3 text-sm text-ink-soft">
        <p class="text-xs uppercase tracking-[0.2em]">Yeast</p>
        <p class="text-xl font-semibold text-ink">{{ stats.yeast }}</p>
      </div>
    </div>

    <h3 class="mt-6 text-xs font-semibold uppercase tracking-[0.25em] text-ink-soft">Ingredient usage</h3>
    <div class="mt-3 grid gap-4 lg:grid-cols-3">
      <div class="rounded-xl border border-border bg-paper p-4">
        <div class="flex items-center justify-between text-sm text-ink-soft">
          <span class="font-semibold text-ink">Grains</span>
          <span>{{ stats.grains }}</span>
        </div>
        <div class="mt-3">
          <canvas ref="grainChartEl" class="h-40 w-full"></canvas>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-paper p-4">
        <div class="flex items-center justify-between text-sm text-ink-soft">
          <span class="font-semibold text-ink">Hops</span>
          <span>{{ stats.hops }}</span>
        </div>
        <div class="mt-3">
          <canvas ref="hopsChartEl" class="h-40 w-full"></canvas>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-paper p-4">
        <div class="flex items-center justify-between text-sm text-ink-soft">
          <span class="font-semibold text-ink">Yeast</span>
          <span>{{ stats.yeast }}</span>
        </div>
        <div class="mt-3">
          <canvas ref="yeastChartEl" class="h-40 w-full"></canvas>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from 'chart.js';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
  mergedMeta: {
    type: Object,
    default: () => ({}),
  },
  perFileMeta: {
    type: Array,
    default: () => [],
  },
  theme: {
    type: String,
    default: 'system',
  },
  recipes: {
    type: Array,
    default: () => [],
  },
  typeLabels: {
    type: Object,
    default: () => ({}),
  },
});

const grainChartEl = ref(null);
const hopsChartEl = ref(null);
const yeastChartEl = ref(null);
const charts = ref({
  grain: null,
  hops: null,
  yeast: null,
});
let systemThemeQuery = null;
let systemThemeHandler = null;

const chartColors = {
  grain: {
    fill: 'rgba(208, 111, 57, 0.65)',
    border: 'rgba(208, 111, 57, 1)',
  },
  hops: {
    fill: 'rgba(124, 156, 132, 0.6)',
    border: 'rgba(124, 156, 132, 1)',
  },
  yeast: {
    fill: 'rgba(122, 174, 194, 0.6)',
    border: 'rgba(122, 174, 194, 1)',
  },
};

const filterFields = [
  { key: 'term', label: 'Term', format: formatMetaValue },
  { key: 'depth', label: 'Depth', format: formatMetaValue },
  { key: 'sort', label: 'Sort', format: formatMetaValue },
  { key: 'types', label: 'Recipe Type', format: formatRecipeTypes },
  { key: 'rated', label: 'Rating', format: formatRatingFilter },
];

function formatFileName(file) {
  if (!file) return 'Unknown file';
  return file.split('/').pop() || file;
}

function formatMetaValue(value) {
  if (value === null || value === undefined || value === '') {
    return 'N/A';
  }
  return value;
}

function formatRecipeTypes(types) {
  if (!types || (typeof types !== 'object' && !Array.isArray(types))) {
    return 'All';
  }

  const selected = [];
  if (Array.isArray(types)) {
    types.forEach((type) => {
      if (!type) return;
      const label = getTypeLabel(type);
      selected.push(label);
    });
  } else {
    Object.entries(types).forEach(([key, value]) => {
      if (value === null || value === '' || value === false || value === 0 || value === '0') return;
      const label = getTypeLabel(key);
      selected.push(label);
    });
  }

  if (selected.length === 0) {
    return 'All';
  }

  return selected.join(', ');
}

function getTypeLabel(key) {
  if (!props.typeLabels || typeof props.typeLabels !== 'object') {
    return key;
  }
  return props.typeLabels[key] || key;
}

function formatRatingFilter(value) {
  if (value === 'Multiple') {
    return 'Multiple';
  }
  if (value === null || value === undefined || value === '') {
    return 'Any';
  }

  const rating = parseInt(value, 10);
  if (Number.isNaN(rating)) {
    return 'Any';
  }
  if (rating <= 0) {
    return 'Unrated';
  }
  if (rating > 5) {
    return '🍺🍺🍺🍺🍺';
  }

  return '🍺'.repeat(rating);
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

function getUsageData(key, limit = 8) {
  const usage = new Map();

  props.recipes.forEach((recipe) => {
    const ingredients = Array.isArray(recipe?.ingredients) ? recipe.ingredients : [];
    ingredients.forEach((ingredient) => {
      if (!ingredientTypeMatches(ingredient?.type, key)) return;
      const name = (ingredient?.name || 'Unknown').trim();
      if (!name) return;
      usage.set(name, (usage.get(name) || 0) + 1);
    });
  });

  return Array.from(usage.entries())
    .sort((a, b) => b[1] - a[1])
    .slice(0, limit);
}

function getChartThemeColors() {
  if (typeof window === 'undefined') {
    return {
      ink: '#1f1c18',
      inkSoft: 'rgba(31, 28, 24, 0.7)',
      border: 'rgba(31, 28, 24, 0.12)',
    };
  }

  const styles = getComputedStyle(document.documentElement);
  const ink = styles.getPropertyValue('--ink').trim() || '#1f1c18';
  const inkSoft = styles.getPropertyValue('--ink-soft').trim() || 'rgba(31, 28, 24, 0.7)';
  const border = styles.getPropertyValue('--border').trim() || 'rgba(31, 28, 24, 0.12)';
  return { ink, inkSoft, border };
}

function destroyCharts() {
  Object.values(charts.value).forEach((chart) => {
    if (chart) chart.destroy();
  });
  charts.value = { grain: null, hops: null, yeast: null };
}

function renderCharts() {
  if (!grainChartEl.value || !hopsChartEl.value || !yeastChartEl.value) {
    return;
  }

  destroyCharts();

  const { ink, inkSoft, border } = getChartThemeColors();

  const createChart = (element, entries, label, color, outline) => {
    const ctx = element.getContext('2d');
    if (!ctx) return null;
    const labels = entries.map(([name]) => name);
    return new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label,
            data: entries.map(([, count]) => count),
            backgroundColor: color,
            borderColor: outline,
            borderWidth: 1,
            borderRadius: 8,
            barThickness: 'flex',
            maxBarThickness: 28,
          },
        ],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 600 },
        plugins: {
          legend: { display: false },
          tooltip: {
            enabled: true,
            callbacks: {
              title: (items) => items[0]?.label || '',
              label: (item) => `Count: ${item.parsed.x}`,
            },
          },
        },
        scales: {
          x: {
            ticks: { color: inkSoft },
            grid: { color: border },
          },
          y: {
            ticks: {
              color: ink,
              callback: (value) => {
                const label = labels[value] || '';
                return label.length > 22 ? `${label.slice(0, 22)}…` : label;
              },
            },
            grid: { display: false },
          },
        },
      },
    });
  };

  charts.value.grain = createChart(
    grainChartEl.value,
    getUsageData('malt'),
    'Grains usage',
    chartColors.grain.fill,
    chartColors.grain.border
  );
  charts.value.hops = createChart(
    hopsChartEl.value,
    getUsageData('hops'),
    'Hops usage',
    chartColors.hops.fill,
    chartColors.hops.border
  );
  charts.value.yeast = createChart(
    yeastChartEl.value,
    getUsageData('yeast'),
    'Yeast usage',
    chartColors.yeast.fill,
    chartColors.yeast.border
  );
}

watch(
  () => props.recipes,
  () => {
    renderCharts();
  },
  { deep: true }
);

watch(
  () => props.theme,
  () => {
    renderCharts();
  }
);

onMounted(() => {
  renderCharts();

  if (typeof window !== 'undefined') {
    systemThemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    systemThemeHandler = () => {
      if (props.theme === 'system') {
        renderCharts();
      }
    };
    systemThemeQuery.addEventListener('change', systemThemeHandler);
  }
});

onBeforeUnmount(() => {
  destroyCharts();
  if (systemThemeQuery && systemThemeHandler) {
    systemThemeQuery.removeEventListener('change', systemThemeHandler);
  }
});
</script>

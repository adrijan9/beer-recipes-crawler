<template>
  <div class="flex flex-wrap items-center gap-2">
    <select
      :value="value"
      class="flex-1 rounded-lg border border-border bg-card px-3 py-2 text-sm text-ink focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
      @change="emit('update', $event)"
    >
      <option value="">Select a recipe file</option>
      <option v-for="file in files" :key="file" :value="file" :disabled="isDisabled(file)">
        {{ formatFileName(file) }}
      </option>
    </select>
    <button
      v-if="value"
      type="button"
      class="rounded-full border border-border bg-card px-3 py-2 text-xs font-semibold text-ink-soft shadow-soft hover:border-copper hover:shadow-strong"
      @click="emit('clear')"
    >
      Remove
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  value: {
    type: String,
    default: '',
  },
  files: {
    type: Array,
    default: () => [],
  },
  selectedValues: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['update', 'clear']);

function isDisabled(file) {
  if (!file) return false;
  return props.selectedValues.includes(file) && file !== props.value;
}

function formatFileName(file) {
  return file.split('/').pop() || file;
}
</script>

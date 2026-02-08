export default {
  content: ['./client_src/index.html', './client_src/src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        ink: 'var(--ink)',
        'ink-soft': 'var(--ink-soft)',
        paper: 'var(--paper)',
        sand: 'var(--sand)',
        amber: 'var(--amber)',
        copper: 'var(--copper)',
        sage: 'var(--sage)',
        sky: 'var(--sky)',
        card: 'var(--card)',
        border: 'var(--border)',
      },
      boxShadow: {
        soft: 'var(--shadow-soft)',
        strong: 'var(--shadow)',
      },
      fontFamily: {
        display: ['var(--font-display)'],
        sans: ['var(--font-sans)'],
      },
    },
  },
  plugins: [],
};

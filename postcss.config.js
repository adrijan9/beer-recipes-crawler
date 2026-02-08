import path from 'node:path';
import tailwindcss from '@tailwindcss/postcss';
import autoprefixer from 'autoprefixer';

export default {
  plugins: [
    tailwindcss({
      base: path.resolve('client_src'),
    }),
    autoprefixer(),
  ],
};

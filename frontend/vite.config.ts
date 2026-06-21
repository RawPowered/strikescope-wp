import { defineConfig } from 'vite'
import path from 'path'
import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'


function figmaAssetResolver() {
  return {
    name: 'figma-asset-resolver',
    resolveId(id: string) {
      if (id.startsWith('figma:asset/')) {
        const filename = id.replace('figma:asset/', '')
        return path.resolve(__dirname, 'src/assets', filename)
      }
    },
  }
}

export default defineConfig(({ command }) => ({
  base: './',
  mode: command === 'build' ? 'production' : 'development',
  define: {
    'process.env.NODE_ENV': JSON.stringify(command === 'build' ? 'production' : 'development'),
  },
  plugins: [
    figmaAssetResolver(),
    react(),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  assetsInclude: ['**/*.svg', '**/*.csv'],
  build:
    command === 'build'
      ? {
          lib: {
            entry: path.resolve(__dirname, 'src/wp-mount.tsx'),
            name: 'StrikeScopeForm',
            formats: ['iife'],
            fileName: () => 'strikescope-form.js',
          },
          rollupOptions: {
            output: {
              inlineDynamicImports: true,
              assetFileNames: 'strikescope-form.[ext]',
            },
          },
          cssCodeSplit: false,
          outDir: 'dist',
          emptyOutDir: true,
        }
      : undefined,
}))

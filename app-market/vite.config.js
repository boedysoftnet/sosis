import { fileURLToPath, URL } from 'url'
import path from "path";
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      // '@': require('path').resolve(__dirname, 'src'),
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      "devextreme/ui": 'devextreme/esm/ui'
    }
  },
  build: {
    watch: {},
    sourcemap: false,
    minify: false,
    emptyOutDir:true,
    cleanCssOptions: {sourceMap:false},
    rollupOptions: {
        output:{
          entryFileNames:"assets/[name].js",
          chunkFileNames:"assets/[name].js",
          assetFileNames:"assets/[name].[ext]",
        }
    },
    outDir: './dist/',
    chunkSizeWarningLimit: 1200,
  }
})

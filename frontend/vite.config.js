import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  optimizeDeps: {
    include: [
      'vue-filepond',
      'filepond',
      'filepond-plugin-image-preview',
      'filepond-plugin-file-validate-type'
    ]
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      // Force Vite to resolve vue-filepond to its ESM build
      'vue-filepond': fileURLToPath(new URL('./node_modules/vue-filepond/dist/vue-filepond.esm.js', import.meta.url))
    }
  },
  server: {
    host: '0.0.0.0',
    port: 5175,
    watch: {
      usePolling: true
    },
    proxy: {
      '/api': {
        target: 'http://php:80',
        changeOrigin: true,
        secure: false,
        configure: (proxy, options) => {
          proxy.on('error', (err, req, res) => {
            console.log('proxy error', err);
          });
          proxy.on('proxyReq', (proxyReq, req, res) => {
            console.log('Sending Request to the Target:', req.method, req.url);
          });
          proxy.on('proxyRes', (proxyRes, req, res) => {
            console.log('Received Response from the Target:', proxyRes.statusCode, req.url);
          });
        }
      },
      '/secure-image': {
        target: 'http://php:80',
        changeOrigin: true,
        secure: false
      }
    }
  },
  preview: {
    host: '0.0.0.0',
    port: 5176
  },
  optimizeDeps: {
    include: [
      'vue-filepond',
      'filepond',
      'filepond-plugin-image-preview',
      'filepond-plugin-file-validate-type',
      'filepond-plugin-image-crop',
      'filepond-plugin-image-resize',
      'filepond-plugin-image-transform'
    ]
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'vue-router', 'pinia'],
          utils: ['axios']
        }
      }
    }
  }
})

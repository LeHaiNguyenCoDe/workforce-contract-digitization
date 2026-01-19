import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath, URL } from "node:url";
import AutoImport from "unplugin-auto-import/vite";
import Components from "unplugin-vue-components/vite";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), "");

  return {
    plugins: [
      vue(),
      AutoImport({
        // Auto import functions from Vue, Vue Router, Pinia
        dirs: [
          'src/composables/**',
          'src/helpers/**',
          'src/utils/**',
          'src/modules/**/composables/**',
          'src/modules/**/store.ts',
          'src/modules/**/store/store.ts',
          // Only import from module services, not from plugins/api/services
          // to avoid duplicates with the centralized index.ts exports
        ],
        // Explicit imports from centralized services index
        imports: [
          'vue',
          'vue-router',
          'pinia',
          'vue-i18n',
          {
            '@/stores': ['useAuthStore'],
          },
          {
            // Import services from centralized index
            '@/plugins/api/services': [
              'authService',
              'productService',
              'adminProductService',
              'categoryService',
              'adminCategoryService',
              'orderService',
              'adminOrderService',
              'cartService',
              'adminUserService',
              'warehouseService',
              'adminPromotionService',
              'adminReviewService',
              'returnService',
              'membershipService',
              'reportService',
              'settingsService',
            ],
          },
        ],
        // Generate TypeScript declarations
        dts: 'src/auto-imports.d.ts',
        // Auto import inside Vue templates
        vueTemplate: true,
      }),
      Components({
        // Folders to scan for components
        dirs: ['src/components', 'src/shared/components', 'src/modules/landing/components', 'src/modules/admin/components'],
        // Enable auto-import for Vue components
        extensions: ['vue'],
        // Generate TypeScript declarations
        dts: 'src/components.d.ts',
        // Allow subdirectories as namespace prefix
        directoryAsNamespace: false,
        // Don't include subfolders in component name
        collapseSamePrefixes: true,
        // Add Bootstrap-Vue-Next resolver for automatic component imports
        resolvers: [
          // Bootstrap-Vue-Next components resolver
          (componentName) => {
            if (componentName.startsWith('B')) {
              return { name: componentName, from: 'bootstrap-vue-next' }
            }
          }
        ],
      }),

    ],
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url)),
      },
      dedupe: ["dompurify"],
      // Ensure ESM modules are resolved correctly
      conditions: ['import', 'module', 'browser', 'default'],
    },
    optimizeDeps: {
      include: ["dompurify"],
      esbuildOptions: {
        // Ensure dompurify is treated as ESM
        mainFields: ['module', 'browser', 'main'],
      },
    },
    server: {
      port: 3000,
      host: true, // Listen on all network interfaces
      proxy: {
        "/api": {
          target: env.VITE_API_BASE_URL || "http://127.0.0.1:8000",
          changeOrigin: true,
          secure: false,
        },
        // Laravel Sanctum CSRF cookie endpoint
        "/sanctum": {
          target: env.VITE_API_BASE_URL || "http://127.0.0.1:8000",
          changeOrigin: true,
          secure: false,
        },
        // Laravel Broadcasting Auth
        "/broadcasting": {
          target: env.VITE_API_BASE_URL || "http://127.0.0.1:8000",
          changeOrigin: true,
          secure: false,
        },
        // Laravel Public Storage
        "/storage": {
          target: env.VITE_API_BASE_URL || "http://127.0.0.1:8000",
          changeOrigin: true,
          secure: false,
        },
      },
    },
    css: {
      preprocessorOptions: {
        scss: {
          api: 'modern-compiler',
        },
      },
    },
  };
});

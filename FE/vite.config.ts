import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath, URL } from "node:url";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), "");

  return {
    plugins: [vue()],
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url)),
      },
    },
    server: {
      port: 3000,
      host: true, // Listen on all network interfaces
      proxy: {
        "/api": {
          target: env.VITE_API_BASE_URL || "http://192.168.1.10",
          changeOrigin: true,
          secure: false,
          // Forward headers properly for session cookies
          configure: (proxy, _options) => {
            proxy.on('proxyRes', (proxyRes, req, res) => {
              // Fix SameSite cookie issue for cross-origin requests
              const cookies = proxyRes.headers['set-cookie'];
              if (cookies) {
                proxyRes.headers['set-cookie'] = cookies.map(cookie => 
                  cookie
                    .replace(/; SameSite=Lax/gi, '; SameSite=None')
                    .replace(/; SameSite=Strict/gi, '; SameSite=None')
                    .replace(/; Secure/gi, '') // Remove Secure for HTTP
                );
              }
            });
          }
        },
      },
    },
  };
});

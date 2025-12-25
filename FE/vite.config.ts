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
      port: 5173,
      proxy: {
        "/api": {
          target: env.VITE_API_BASE_URL || "http://workforce_contract_digitization.io",
          changeOrigin: true,
          secure: false,
          // Cookie settings for proper session forwarding
          cookieDomainRewrite: {
            "*": "localhost"
          },
          cookiePathRewrite: {
            "*": "/"
          },
          // Forward headers properly
          configure: (proxy, _options) => {
            proxy.on('proxyRes', (proxyRes, req, res) => {
              // Fix SameSite cookie issue
              const cookies = proxyRes.headers['set-cookie'];
              if (cookies) {
                proxyRes.headers['set-cookie'] = cookies.map(cookie => 
                  cookie
                    .replace(/; SameSite=Lax/gi, '; SameSite=None; Secure')
                    .replace(/; SameSite=Strict/gi, '; SameSite=None; Secure')
                );
              }
            });
          }
        },
      },
    },
  };
});

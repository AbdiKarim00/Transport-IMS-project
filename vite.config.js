import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
  plugins: [
    laravel({
      input: [
        "resources/css/app.css",
        "resources/css/login.css",
        "resources/js/app.js",
      ],
      refresh: true,
    }),
  ],
  server: {
    proxy: {
      // Proxy API requests to Laravel server
      "/api": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      // Proxy auth routes to Laravel server
      "/login": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      "/register": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      "/logout": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      "/password": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      "/request-access": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      // Proxy admin routes to Laravel server
      "/admin": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      "/dashboard": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
      // Proxy any PHP files to Laravel server
      "**.php": {
        target: "http://localhost:8000",
        changeOrigin: true,
        secure: false,
      },
    },
  },
});

/** @type {import('vite').UserConfig} */
export default {
  base: "/reporTerra/",
  server: {
    host: true,
    port: 5173,
    proxy: {
      "/api.php": {
        target: "http://localhost:9000",
        changeOrigin: true,
      },
    },
  },
};

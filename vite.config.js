import { defineConfig } from 'vite';

export default defineConfig({
    define: {
        'process.env': process.env,
    },
    server: {
        host: process.env.VITE_HOST,
        port: process.env.VITE_PORT,
        hmr: true,
    },
    build: {
        manifest: true,
        outDir: './test_theme/build',
        rollupOptions: {
            input: [
                "assets/scss/style.scss",
                "assets/ts/script.ts",
                "assets/ts/head.ts",
                "assets/ts/footer.ts",
            ],
        },
    },
});

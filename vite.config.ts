import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import fs from 'node:fs'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: ['resources/views/**'],
        }),
        vue({
            script: {
                fs: {
                    fileExists: fs.existsSync,
                    readFile: (file) => fs.readFileSync(file, 'utf-8'),
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': `${import.meta.dirname}/resources/js`,
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
})

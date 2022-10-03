import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            publicDirectory: './craft',
            input: [
                'src/fields/slot.ts',
            ],
            refresh: true,
        }),
    ],
});

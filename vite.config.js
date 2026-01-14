import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // server: {
    //     host: "0.0.0.0",
    //     port: 5173, //vite
    //     hmr: {
    //         host: "192.168.1.108",  // PC's IP
    //        // host: "172.20.10.4",  // PC's IP
    //     },
    // },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
